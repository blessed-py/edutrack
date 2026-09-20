<?php
/**
 * priority_score = (W1 * urgency_score) + (W2 * weakness_score) + (W3 * deadline_score)
 *
 * urgency_score   = 1 / (days_to_exam + 1)
 * weakness_score  = (100 - previous_score) / 100   [0 if topic not marked weak]
 * deadline_score  = 1 / (days_to_deadline + 1)
 */

const PRIORITY_W1_URGENCY = 0.4;
const PRIORITY_W2_WEAKNESS = 0.4;
const PRIORITY_W3_DEADLINE = 0.2;

function days_until(?string $date): ?int
{
    if (!$date) {
        return null;
    }
    $today = new DateTime('today');
    $target = new DateTime($date);
    $diff = $today->diff($target)->days;
    return $target < $today ? -$diff : $diff;
}

function urgency_score(?int $daysToExam): float
{
    if ($daysToExam === null || $daysToExam < 0) {
        return 0.0;
    }
    return 1 / ($daysToExam + 1);
}

function weakness_score(bool $isWeak, ?int $previousScore): float
{
    if (!$isWeak) {
        return 0.0;
    }
    $score = $previousScore ?? 50;
    return (100 - $score) / 100;
}

function deadline_score(?int $daysToDeadline): float
{
    if ($daysToDeadline === null || $daysToDeadline < 0) {
        return 0.0;
    }
    return 1 / ($daysToDeadline + 1);
}

function compute_priority_score(?int $daysToExam, bool $isWeak, ?int $previousScore, ?int $daysToDeadline): float
{
    return (PRIORITY_W1_URGENCY * urgency_score($daysToExam))
        + (PRIORITY_W2_WEAKNESS * weakness_score($isWeak, $previousScore))
        + (PRIORITY_W3_DEADLINE * deadline_score($daysToDeadline));
}

/**
 * Builds the prioritized study plan for a user.
 * Returns topics sorted by priority_score desc, with allocated_hours proportional
 * to score, distributed across the user's available_study_hours.
 */
function build_study_plan(PDO $pdo, int $userId, float $availableHours): array
{
    $stmt = $pdo->prepare(
        'SELECT t.id, t.name, t.previous_score, t.is_weak, t.is_completed,
                c.name AS course_name,
                (SELECT MIN(e.exam_date) FROM exams e WHERE e.course_id = c.id AND e.exam_date >= CURDATE()) AS next_exam_date,
                (SELECT MIN(a.due_date) FROM assignments a WHERE a.course_id = c.id AND a.status = "pending" AND a.due_date >= CURDATE()) AS next_deadline
         FROM topics t
         JOIN courses c ON c.id = t.course_id
         WHERE c.user_id = ? AND t.is_completed = 0'
    );
    $stmt->execute([$userId]);
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($topics as &$topic) {
        $daysToExam = days_until($topic['next_exam_date']);
        $daysToDeadline = days_until($topic['next_deadline']);
        $topic['days_to_exam'] = $daysToExam;
        $topic['days_to_deadline'] = $daysToDeadline;
        $topic['priority_score'] = compute_priority_score(
            $daysToExam,
            (bool) $topic['is_weak'],
            $topic['previous_score'] !== null ? (int) $topic['previous_score'] : null,
            $daysToDeadline
        );
    }
    unset($topic);

    usort($topics, fn($a, $b) => $b['priority_score'] <=> $a['priority_score']);

    $scoreSum = array_sum(array_column($topics, 'priority_score'));
    foreach ($topics as &$topic) {
        $topic['allocated_hours'] = $scoreSum > 0
            ? round(($topic['priority_score'] / $scoreSum) * $availableHours, 1)
            : 0.0;
    }
    unset($topic);

    return $topics;
}
