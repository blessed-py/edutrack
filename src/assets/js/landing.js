(function () {
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function animateCount(el, target, duration) {
    var startTime = null;
    var done = false;
    function step(ts) {
      if (done) return;
      if (!startTime) startTime = ts;
      var progress = Math.min((ts - startTime) / duration, 1);
      el.textContent = Math.round(target * progress);
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        done = true;
      }
    }
    requestAnimationFrame(step);

    // Safety net: rAF can stall (throttled/backgrounded tab) partway through
    // — never leave the counter frozen on a wrong intermediate number.
    setTimeout(function () {
      if (!done) {
        done = true;
        el.textContent = target;
      }
    }, duration + 400);
  }

  var mockup = document.getElementById('mockup');
  if (mockup && !reduceMotion) {
    var counted = false;
    var countObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting && !counted) {
          counted = true;
          mockup.querySelectorAll('.l-stat-value[data-count]').forEach(function (el) {
            animateCount(el, parseInt(el.dataset.count, 10), 700);
          });
          countObserver.disconnect();
        }
      });
    }, { threshold: 0.4 });
    countObserver.observe(mockup);
  }

  var revealEls = document.querySelectorAll('.reveal');
  if (revealEls.length) {
    var revealObserver = new IntersectionObserver(function (entries, obs) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('in-view');
          obs.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(function (el) { revealObserver.observe(el); });

    // Safety net: content must never stay invisible if the observer can't
    // fire for some reason (blocked by an extension, a backgrounded tab
    // that's never focused, etc.) — force it visible after a short delay.
    setTimeout(function () {
      revealEls.forEach(function (el) { el.classList.add('in-view'); });
      revealObserver.disconnect();
    }, 2000);
  }
})();
