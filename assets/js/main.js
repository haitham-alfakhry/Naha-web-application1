/**
 * ======================================================
 *  NAHA Web Application
 *  File: main.js
 *  Author: Haitham
 *  Description:
 *  Global UI interactions:
 *  - Smooth scroll
 *  - Topbar effects
 *  - Scroll progress bar
 *  - Back-to-top button
 *  - Parallax hero
 *  - Intersection animations
 *  - Lazy Chart.js loading
 *  - Animated counters
 *  - Newsletter AJAX
 * ======================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  /* ======================================================
     Smooth Scroll
  ====================================================== */

  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', e => {
      const id = anchor.getAttribute('href');
      if (id.length > 1) {
        const target = document.querySelector(id);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });


  /* ======================================================
     Topbar Shadow + Scroll Progress + Back To Top
  ====================================================== */

  const topbar = document.querySelector('.topbar');
  const progress = document.querySelector('.scroll-progress');

  const toTop = document.createElement('button');
  toTop.className = 'to-top';
  toTop.setAttribute('aria-label', 'Remonter');
  toTop.textContent = '↑';
  document.body.appendChild(toTop);

  const handleScroll = () => {
    const y = window.scrollY || document.documentElement.scrollTop;

    if (topbar) topbar.classList.toggle('scrolled', y > 8);
    toTop.classList.toggle('show', y > 400);

    if (progress) {
      const doc = document.documentElement;
      const max = doc.scrollHeight - doc.clientHeight;
      const ratio = Math.max(0, Math.min(1, y / (max || 1)));
      progress.style.width = `${(ratio * 100).toFixed(2)}%`;
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  toTop.addEventListener('click', () =>
    window.scrollTo({ top: 0, behavior: 'smooth' })
  );


  /* ======================================================
     Parallax Hero
  ====================================================== */

  const heroInner = document.querySelector('.hero[data-parallax] .hero__inner');

  if (heroInner) {
    window.addEventListener('scroll', () => {
      const speed = 0.25;
      heroInner.style.transform =
        `translate3d(0, ${(window.scrollY || 0) * speed}px, 0)`;
    }, { passive: true });
  }


  /* ======================================================
     Intersection Animations
  ====================================================== */

  const animationObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-inview');
        animationObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.18 });

  document.querySelectorAll('[data-animate]')
    .forEach(el => animationObserver.observe(el));


  /* ======================================================
     Lazy Chart Initialization (Chart.js)
  ====================================================== */

  const lazyCharts = document.querySelectorAll('[data-lazy-chart]');

  if (lazyCharts.length && window.Chart) {

    const chartObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          initChart(entry.target);
          chartObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    lazyCharts.forEach(chart => chartObserver.observe(chart));
  }

  function initChart(canvas) {

    const ctx = canvas.getContext('2d');
    const type = canvas.dataset.lazyChart;

    const blue = '#4c5bf9';
    const green = '#45c778';

    if (type === 'bar') {
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'],
          datasets: [{
            data: [32,45,41,50,62,48,54],
            backgroundColor: blue,
            borderRadius: 8
          }]
        },
        options: { responsive: true }
      });
    }

    if (type === 'area') {
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: Array.from({length: 12}, (_,i)=>i+1),
          datasets: [{
            data: [12,14,13,16,18,17,21,22,20,23,25,27],
            borderColor: green,
            backgroundColor: 'rgba(69,199,120,.15)',
            fill: true,
            tension: .35,
            pointRadius: 0
          }]
        },
        options: { responsive: true }
      });
    }
  }


  /* ======================================================
     Animated Counters
  ====================================================== */

  const counters = document.querySelectorAll('[data-counter]');

  if (counters.length) {
    const counterObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.6 });

    counters.forEach(counter => counterObserver.observe(counter));
  }

  function animateCounter(el) {
    const target = parseInt(el.dataset.counter, 10) || 0;
    const duration = 900;
    const startTime = performance.now();

    const step = now => {
      const progress = Math.min(1, (now - startTime) / duration);
      const value = Math.floor(target * progress);
      el.textContent = value.toLocaleString('fr-FR');
      if (progress < 1) requestAnimationFrame(step);
    };

    requestAnimationFrame(step);
  }


  /* ======================================================
     Newsletter AJAX
  ====================================================== */

  const newsForm = document.getElementById('newsletter-form');
  const newsMsg  = document.getElementById('newsletter-msg');

  if (newsForm) {
    newsForm.addEventListener('submit', async e => {
      e.preventDefault();
      newsMsg.textContent = 'Envoi…';

      try {
        const response = await fetch(
          newsForm.action || 'api/newsletter.php',
          { method: 'POST', body: new FormData(newsForm) }
        );

        const data = await response.json().catch(() => ({}));

        if (response.ok) {
          newsMsg.textContent = data.message || 'Merci ! Inscription confirmée.';
          newsForm.reset();
        } else {
          newsMsg.textContent = data.error || 'Erreur serveur.';
        }

      } catch {
        newsMsg.textContent = 'Connexion impossible.';
      }
    });
  }

});