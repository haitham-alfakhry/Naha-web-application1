/**
 * ======================================================
 *  NAHA Web Application
 *  File: tableau.js
 *  Description:
 *  Dashboard interactions:
 *  - Dynamic charts (Chart.js)
 *  - Weekly calories
 *  - Macros donut
 *  - Cardio evolution
 *  - Animated counters
 * ======================================================
 */

document.addEventListener('DOMContentLoaded', () => {

  console.log('Dashboard JS loaded');

  const dash = window.NAHA_DASH || {};

  const weekLabels = dash.weekLabels || ['L','M','M','J','V','S','D'];
  const weekIn     = dash.weekIn     || [0,0,0,0,0,0,0];
  const weekOut    = dash.weekOut    || [0,0,0,0,0,0,0];
  const weekCardio = dash.weekCardio || [0,0,0,0,0,0,0];
  const macros     = dash.macros     || { prot: 0, glu: 0, lip: 0 };


  /* ======================================================
     Animated Counters
  ====================================================== */

  const counters = document.querySelectorAll('[data-counter]');

  const counterObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      animateCounter(entry.target);
      counterObserver.unobserve(entry.target);
    });
  }, { threshold: 0.7 });

  counters.forEach(el => counterObserver.observe(el));

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
     Charts (Lazy Loading)
  ====================================================== */

  if (!window.Chart) return;

  const chartObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;

      const canvas = entry.target;
      const type   = canvas.dataset.chart;

      if (type === 'bars-week')   initBars(canvas);
      if (type === 'donut-macros') initDonut(canvas);
      if (type === 'line-cardio')  initLine(canvas);

      chartObserver.unobserve(canvas);
    });
  }, { threshold: 0.2 });

  document.querySelectorAll('canvas[data-chart]')
    .forEach(canvas => chartObserver.observe(canvas));


  /* ======================================================
     Chart Configuration Helpers
  ====================================================== */

  const primary = getCSS('--primary') || '#4c5bf9';
  const green   = getCSS('--green')   || '#45c778';
  const prot    = getCSS('--prot')    || '#6e8efb';
  const glu     = getCSS('--glu')     || '#60a5fa';
  const lip     = getCSS('--lip')     || '#faca63';

  function baseOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    };
  }


  /* ======================================================
     Weekly Calories Bar Chart
  ====================================================== */

  function initBars(canvas) {

    const maxY = Math.max(...weekIn, ...weekOut, 2000) + 300;

    new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: weekLabels,
        datasets: [
          {
            label: 'Ingérées',
            data: weekIn,
            backgroundColor: primary,
            borderRadius: 8
          },
          {
            label: 'Dépensées',
            data: weekOut,
            backgroundColor: green,
            borderRadius: 8
          }
        ]
      },
      options: {
        ...baseOptions(),
        scales: {
          y: { beginAtZero: true, suggestedMax: maxY }
        }
      }
    });
  }


  /* ======================================================
     Macros Donut Chart
  ====================================================== */

  function initDonut(canvas) {

    const p = macros.prot || 0;
    const g = macros.glu  || 0;
    const l = macros.lip  || 0;

    if ((p + g + l) === 0) {
      canvas.parentElement.innerHTML =
        `<div style="text-align:center;padding:50px;font-weight:600">
          Ton tableau est vide.<br>
          Ajoute un aliment pour voir tes macros.
        </div>`;
      return;
    }

    new Chart(canvas.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Protéines','Glucides','Lipides'],
        datasets: [{
          data: [p, g, l],
          backgroundColor: [prot, glu, lip],
          borderWidth: 0,
          cutout: '60%'
        }]
      },
      options: {
        plugins: {
          tooltip: {
            callbacks: {
              label: ctx => {
                const total = p + g + l || 1;
                const pct = (ctx.raw * 100 / total).toFixed(1);
                return `${ctx.label} : ${ctx.raw} g (${pct}%)`;
              }
            }
          }
        }
      }
    });
  }


  /* ======================================================
     Cardio Evolution Line Chart
  ====================================================== */

  function initLine(canvas) {

    const maxY = Math.max(...weekCardio, 30) + 10;

    new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: weekLabels,
        datasets: [{
          data: weekCardio,
          borderColor: primary,
          backgroundColor: 'rgba(76,91,249,.12)',
          fill: true,
          tension: .35,
          borderWidth: 3
        }]
      },
      options: {
        ...baseOptions(),
        scales: { y: { beginAtZero: true, suggestedMax: maxY } }
      }
    });
  }


  function getCSS(varName) {
    return getComputedStyle(document.documentElement)
      .getPropertyValue(varName)
      .trim();
  }

});