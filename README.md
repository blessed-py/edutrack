# EduTrack – Student Study Planner

A centralized web platform for students to manage courses, exam dates, assignment deadlines, weak topics, and previous scores — and get a personalized, priority-scored study plan.

## Stack

- **Backend**: Plain PHP 8.2 (PDO + MySQL), no framework
- **Database**: MySQL 8.0
- **Frontend**: Bootstrap 5 (server-rendered PHP views)
- **Deployment**: Docker Compose (PHP+Apache container, MySQL container) — same pattern used for VPS deployment

## Running locally (Docker)

```
docker compose up -d --build
```

Then open http://localhost:8080

First-time setup: visit http://localhost:8080/create_admin.php once to create the admin account (this page disables itself after the first admin is created). Students register normally at `/register.php`.

## Priority algorithm

The Smart Study Planner (`src/includes/priority.php`) scores each open topic:

```
priority_score = 0.4 × urgency_score + 0.4 × weakness_score + 0.2 × deadline_score

urgency_score   = 1 / (days_to_nearest_exam + 1)
weakness_score  = (100 - previous_score) / 100   [0 if topic not marked weak]
deadline_score  = 1 / (days_to_nearest_assignment_deadline + 1)
```

Topics are sorted by score descending, and the student's daily available study hours are allocated proportionally across them.

## Modules

1. Student Registration & Login
2. Course/Subject Management
3. Examination Schedule
4. Assignment Deadlines
5. Weak Topics & Previous Scores
6. Smart Study Planner (priority algorithm)
7. Progress Tracking
8. Admin (view/manage student accounts)

## Deploying to a VPS

```
git clone <repo> && cd edutrack
docker compose up -d --build
```

Put a reverse proxy (Nginx/Caddy) in front of port 8080 for HTTPS, and change the DB passwords in `docker-compose.yml` before deploying anywhere public.
