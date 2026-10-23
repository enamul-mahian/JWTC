(() => {
  'use strict';
  document.querySelectorAll('[data-hero-slides]').forEach(hero => {
    const slides = [...hero.querySelectorAll('.jg-hero-slide')];
    if (slides.length < 2) return;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    const pause = hero.querySelector('.jg-hero-pause');
    const counter = hero.querySelector('.jg-hero-counter');
    let index = 0, timer = null, stopped = false, hovering = false;
    const autoplay = hero.dataset.autoplay === 'true';
    const interval = Math.max(5,Math.min(30,Number(hero.dataset.interval) || 8))*1000;
    function select(next) {
      index = (next + slides.length) % slides.length;
      slides.forEach((slide,i) => slide.classList.toggle('is-active',i === index));
      counter.textContent = `${index + 1} / ${slides.length}`;
    }
    function sync() {
      clearInterval(timer);
      if (autoplay && !stopped && !reduced.matches && !document.hidden && !hovering) timer = setInterval(() => select(index+1),interval);
      pause.textContent = stopped ? 'Play' : 'Pause';
      pause.setAttribute('aria-label',stopped ? 'Play background slideshow' : 'Pause background slideshow');
      pause.hidden = !autoplay || reduced.matches;
    }
    hero.querySelector('.jg-hero-prev').addEventListener('click',() => {select(index-1);sync();});
    hero.querySelector('.jg-hero-next').addEventListener('click',() => {select(index+1);sync();});
    pause.addEventListener('click',() => {stopped = !stopped;sync();});
    hero.addEventListener('mouseenter',() => {hovering=true;sync();});
    hero.addEventListener('mouseleave',() => {hovering=false;sync();});
    hero.addEventListener('focusin',() => {hovering=true;sync();});
    hero.addEventListener('focusout',event => {if (!hero.contains(event.relatedTarget)) {hovering=false;sync();}});
    document.addEventListener('visibilitychange',sync); reduced.addEventListener('change',sync); sync();
  });
})();
