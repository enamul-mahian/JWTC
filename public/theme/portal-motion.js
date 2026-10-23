(() => {
  'use strict';
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
  let observer = null;
  function reveal() {
    observer?.disconnect();
    document.querySelectorAll('.jg-motion-enter').forEach(el => el.classList.remove('jg-motion-enter'));
    if (reduced.matches || !('IntersectionObserver' in window)) return;
    const elements = [...document.querySelectorAll('main .jg-stats>div, main .jg-job-card, main .jg-section-heading, main .jg-track-panel, main .jg-track-top, main .jg-track-aside, main .jg-job-copy, main .jw-kpi, main .jw-workspace-intro, #jw-admin-content>.section>.card, #jw-admin-content .row>.col-lg-6>.card')].slice(0,120);
    observer = new IntersectionObserver(entries => {
      let stagger = 0;
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.style.setProperty('--jg-motion-delay', Math.min(stagger++ * 55, 165) + 'ms');
        entry.target.addEventListener('animationend', () => {entry.target.classList.remove('jg-motion-enter'); entry.target.style.removeProperty('--jg-motion-delay');}, {once:true});
        entry.target.classList.add('jg-motion-enter');
        observer.unobserve(entry.target);
      });
    }, {threshold:0.08});
    elements.forEach(el => observer.observe(el));
  }
  reveal();
  reduced.addEventListener('change', reveal);

  document.querySelectorAll('[data-news-ticker]').forEach(ticker => {
    const windowEl = ticker.querySelector('.jg-ticker-window');
    const belt = ticker.querySelector('.jg-ticker-belt');
    const items = ticker.querySelector('.jg-ticker-items');
    const button = ticker.querySelector('.jg-ticker-pause');
    if (!windowEl || !belt || !items || !button) return;
    let animation = null, clone = null, userPaused = false, hovering = false, focused = false, touching = false;
    const speed = Math.max(15, Math.min(160, Number(ticker.dataset.speed) || 60));
    function sync() {
      const paused = userPaused || hovering || focused || touching || document.hidden || reduced.matches;
      if (animation) paused ? animation.pause() : animation.play();
      button.textContent = userPaused ? 'Play' : 'Pause';
      button.setAttribute('aria-label', userPaused ? 'Resume news ticker' : 'Pause news ticker');
      button.setAttribute('aria-pressed', String(userPaused));
    }
    function build() {
      const oldDuration = animation?.effect?.getTiming().duration || 1;
      const progress = animation ? (Number(animation.currentTime || 0) % oldDuration) / oldDuration : 0;
      animation?.cancel(); animation = null; clone?.remove(); clone = null;
      ticker.removeAttribute('data-running'); items.style.minWidth = '';
      if (reduced.matches || typeof belt.animate !== 'function') return;
      ticker.setAttribute('data-running', 'true');
      items.style.minWidth = Math.ceil(windowEl.clientWidth) + 'px';
      const width = items.getBoundingClientRect().width;
      if (!width) {ticker.removeAttribute('data-running'); return;}
      clone = items.cloneNode(true); clone.setAttribute('aria-hidden','true');
      clone.querySelectorAll('a').forEach(a => a.setAttribute('tabindex','-1'));
      belt.append(clone);
      const duration = width / speed * 1000;
      animation = belt.animate([{transform:'translateX(0)'},{transform:`translateX(-${width}px)`}],{duration,iterations:Infinity,easing:'linear'});
      animation.currentTime = progress * duration;
      sync();
    }
    button.addEventListener('click', () => {userPaused = !userPaused; sync();});
    ticker.addEventListener('mouseenter', () => {hovering = true; sync();});
    ticker.addEventListener('mouseleave', () => {hovering = false; sync();});
    ticker.addEventListener('focusin', () => {focused = true; sync();});
    ticker.addEventListener('focusout', event => {focused = ticker.contains(event.relatedTarget); sync();});
    ticker.addEventListener('touchstart', () => {touching = true; sync();}, {passive:true});
    ticker.addEventListener('touchend', () => {touching = false; sync();}, {passive:true});
    ticker.addEventListener('touchcancel', () => {touching = false; sync();}, {passive:true});
    document.addEventListener('visibilitychange', sync);
    reduced.addEventListener('change', build);
    build();
    if ('ResizeObserver' in window) new ResizeObserver(build).observe(windowEl);
    else window.addEventListener('resize', build, {passive:true});
    document.fonts?.ready.then(build);
  });
})();
