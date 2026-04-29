// =====================================================
// Поликлиника «Здоровье» — анимации и интерактив
// =====================================================

(function () {
    'use strict';

    // ---------- Шапка: тень при скролле ----------
    const header = document.getElementById('siteHeader');
    if (header) {
        const onScroll = () => header.classList.toggle('scrolled', window.scrollY > 16);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    // ---------- Мобильное меню ----------
    const burger = document.getElementById('burgerBtn');
    const nav = document.getElementById('mainNav');
    if (burger && nav) {
        burger.addEventListener('click', () => {
            const open = nav.classList.toggle('is-open');
            burger.classList.toggle('is-open', open);
        });
        nav.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
            nav.classList.remove('is-open');
            burger.classList.remove('is-open');
        }));
    }

    // ---------- Админ-сайдбар: мобильное меню ----------
    const adminBurger = document.getElementById('adminBurger');
    const adminSidebar = document.getElementById('adminSidebar');
    if (adminBurger && adminSidebar) {
        adminBurger.addEventListener('click', () => {
            adminSidebar.classList.toggle('is-open');
        });
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 900 && adminSidebar.classList.contains('is-open')) {
                if (!adminSidebar.contains(e.target) && !adminBurger.contains(e.target)) {
                    adminSidebar.classList.remove('is-open');
                }
            }
        });
    }

    // ---------- Reveal на скролле ----------
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => observer.observe(el));

    // ---------- Анимация чисел ----------
    const animateCount = (el) => {
        const target = parseFloat(el.dataset.count) || 0;
        const duration = 1600;
        const start = performance.now();
        const decimals = (el.dataset.decimals | 0);
        const suffix = el.dataset.suffix || '';
        const tick = (now) => {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            const v = target * eased;
            el.textContent = (decimals ? v.toFixed(decimals) : Math.round(v).toLocaleString('ru-RU')) + suffix;
            if (p < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };
    const numObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCount(entry.target);
                numObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    document.querySelectorAll('[data-count]').forEach(el => numObserver.observe(el));

    // ---------- Анимация прогресс-баров ----------
    const barObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll('.fill').forEach(fill => {
                    const target = fill.dataset.width || '0%';
                    setTimeout(() => { fill.style.width = target; }, 60);
                });
                barObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.3 });
    document.querySelectorAll('.bars').forEach(el => barObserver.observe(el));

    // ---------- Donut диаграмма (рисуем сегменты) ----------
    document.querySelectorAll('.donut').forEach(svg => {
        const data = JSON.parse(svg.dataset.values || '[]');
        const total = data.reduce((s, d) => s + d.value, 0) || 1;
        const cx = 100, cy = 100, r = 70, sw = 24;
        let offset = 0;
        const ns = 'http://www.w3.org/2000/svg';
        // Фоновое кольцо
        const bg = document.createElementNS(ns, 'circle');
        bg.setAttribute('cx', cx); bg.setAttribute('cy', cy); bg.setAttribute('r', r);
        bg.setAttribute('fill', 'none'); bg.setAttribute('stroke', '#f1f5f9'); bg.setAttribute('stroke-width', sw);
        svg.appendChild(bg);
        const circumference = 2 * Math.PI * r;
        data.forEach((d, i) => {
            const len = (d.value / total) * circumference;
            const c = document.createElementNS(ns, 'circle');
            c.setAttribute('cx', cx); c.setAttribute('cy', cy); c.setAttribute('r', r);
            c.setAttribute('fill', 'none');
            c.setAttribute('stroke', d.color);
            c.setAttribute('stroke-width', sw);
            c.setAttribute('stroke-dasharray', `0 ${circumference}`);
            c.setAttribute('stroke-dashoffset', -offset);
            c.setAttribute('stroke-linecap', 'butt');
            c.style.transition = `stroke-dasharray 1.4s cubic-bezier(.22,1,.36,1) ${i * 0.12}s`;
            svg.appendChild(c);
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    c.setAttribute('stroke-dasharray', `${len} ${circumference}`);
                });
            });
            offset += len;
        });
    });

    // ---------- Плавающая кнопка появляется при скролле ----------
    const cta = document.getElementById('floatingCta');
    if (cta) {
        const onScrollCta = () => cta.classList.toggle('is-visible', window.scrollY > 600);
        onScrollCta();
        window.addEventListener('scroll', onScrollCta, { passive: true });
    }

    // ---------- Поиск в админ-таблицах ----------
    document.querySelectorAll('[data-search-target]').forEach(input => {
        const targetSel = input.dataset.searchTarget;
        const table = document.querySelector(targetSel);
        if (!table) return;
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach(tr => {
                tr.style.display = !q || tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    });

    // ---------- Фильтр по статусу ----------
    document.querySelectorAll('[data-filter-target]').forEach(sel => {
        const targetSel = sel.dataset.filterTarget;
        const colName = sel.dataset.filterColumn;
        const table = document.querySelector(targetSel);
        if (!table) return;
        sel.addEventListener('change', () => {
            const v = sel.value;
            table.querySelectorAll('tbody tr').forEach(tr => {
                if (!v) { tr.style.display = ''; return; }
                const cell = tr.querySelector(`[data-col="${colName}"]`);
                tr.style.display = (cell && cell.dataset.value === v) ? '' : 'none';
            });
        });
    });

    // ---------- Форма заявки: «отправка» (фронтэнд only) ----------
    const applyForm = document.getElementById('applyForm');
    const successCard = document.getElementById('applySuccess');
    if (applyForm && successCard) {
        applyForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = applyForm.querySelector('button[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Отправляем...'; }
            setTimeout(() => {
                applyForm.style.transition = 'opacity .3s';
                applyForm.style.opacity = '0';
                setTimeout(() => {
                    applyForm.style.display = 'none';
                    successCard.style.display = 'block';
                }, 300);
            }, 700);
        });
    }

    // ---------- «Магнитный» эффект на основные кнопки hero ----------
    document.querySelectorAll('.hero .btn').forEach(btn => {
        btn.addEventListener('mousemove', (e) => {
            const rect = btn.getBoundingClientRect();
            const x = e.clientX - rect.left - rect.width / 2;
            const y = e.clientY - rect.top - rect.height / 2;
            btn.style.transform = `translate(${x * 0.12}px, ${y * 0.12}px)`;
        });
        btn.addEventListener('mouseleave', () => { btn.style.transform = ''; });
    });

    // ---------- Подтверждение удаления ----------
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (!confirm(el.dataset.confirm || 'Вы уверены?')) e.preventDefault();
        });
    });

})();
