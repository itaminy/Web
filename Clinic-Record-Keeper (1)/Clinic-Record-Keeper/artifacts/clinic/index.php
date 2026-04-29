<?php
require __DIR__ . '/includes/sample-data.php';
$page_title = 'Поликлиника «Здоровье» — современная медицина с заботой о вас';
include __DIR__ . '/includes/header.php';
?>

<!-- ============== HERO ============== -->
<section class="hero">
    <div class="hero-media">
        <div class="hero-poster" style="background-image: url('images/reception.png');"></div>
        <video class="hero-video" autoplay muted loop playsinline poster="images/reception.png">
            <source src="videos/hero-bg.mp4" type="video/mp4">
        </video>
        <div class="hero-overlay"></div>
    </div>

    <div class="container hero-content">
        <span class="hero-eyebrow">Запись по телефону и онлайн</span>
        <h1>Современная медицина<br><span class="accent">с заботой о вас</span></h1>
        <p class="lead">
            Высококвалифицированные врачи, точная диагностика и индивидуальный подход
            к каждому пациенту в уютной атмосфере нашей клиники.
        </p>
        <div class="hero-actions">
            <a href="apply.php" class="btn btn-primary btn-lg">
                Подать заявку на лечение
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="#services" class="btn btn-ghost btn-lg">Наши услуги</a>
        </div>

        <div class="hero-stats">
            <div class="hero-stat"><strong data-count="14" data-suffix="+">0</strong><span>лет опыта</span></div>
            <div class="hero-stat"><strong data-count="38" data-suffix="">0</strong><span>врачей-специалистов</span></div>
            <div class="hero-stat"><strong data-count="12500" data-suffix="+">0</strong><span>довольных пациентов</span></div>
        </div>
    </div>
</section>

<!-- ============== УСЛУГИ ============== -->
<section id="services" class="block">
    <div class="container">
        <div class="section-head reveal">
            <span class="section-eyebrow">Что мы делаем</span>
            <h2>Полный спектр медицинских услуг</h2>
            <p>От профилактических осмотров до сложной диагностики — все направления медицины под одной крышей.</p>
        </div>

        <div class="services-grid reveal-stagger">
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.29 1.51 4.04 3 5.5l7 7Z"/></svg></div>
                <h3>Терапия</h3>
                <p>Комплексный подход к диагностике и лечению заболеваний внутренних органов и систем.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>
                <h3>Кардиология</h3>
                <p>Современные методы лечения сердечно-сосудистых заболеваний, ЭКГ, УЗИ сердца.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div>
                <h3>Неврология</h3>
                <p>Точная диагностика и лечение заболеваний центральной и периферической нервной системы.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
                <h3>Педиатрия</h3>
                <p>Забота о здоровье самых маленьких пациентов с первых дней жизни и до 18 лет.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/></svg></div>
                <h3>Офтальмология</h3>
                <p>Диагностика, лечение и профилактика заболеваний органов зрения у взрослых и детей.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg></div>
                <h3>Хирургия</h3>
                <p>Малоинвазивные оперативные вмешательства и амбулаторная хирургия высокого уровня.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></div>
                <h3>Стоматология</h3>
                <p>Терапевтическая, хирургическая и эстетическая стоматология с современным оборудованием.</p>
            </div>
            <div class="service-card">
                <div class="service-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><polyline points="16 21 16 3 8 3 8 21"/></svg></div>
                <h3>Лабораторная диагностика</h3>
                <p>Более 1500 видов анализов с быстрым получением результатов в личном кабинете.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============== ВРАЧИ ============== -->
<section id="doctors" class="block" style="background: var(--surface);">
    <div class="container">
        <div class="section-head reveal">
            <span class="section-eyebrow">Наши специалисты</span>
            <h2>Команда опытных врачей</h2>
            <p>Все наши специалисты имеют профильное образование, регулярно проходят повышение квалификации и используют современные методы лечения.</p>
        </div>

        <div class="doctors-grid reveal-stagger">
            <?php foreach ($DOCTORS as $d): ?>
            <article class="doctor-card">
                <div class="doctor-photo">
                    <img src="<?= htmlspecialchars($d['photo']) ?>" alt="<?= htmlspecialchars($d['name']) ?>" loading="lazy">
                </div>
                <div class="doctor-info">
                    <h3><?= htmlspecialchars($d['name']) ?></h3>
                    <div class="doctor-spec"><?= htmlspecialchars($d['specialty']) ?></div>
                    <div class="doctor-meta">
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            Опыт <?= (int)$d['experience'] ?> лет
                        </span>
                        <span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Каб. <?= htmlspecialchars($d['room']) ?>
                        </span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============== О КЛИНИКЕ ============== -->
<section id="about" class="block">
    <div class="container">
        <div class="about-grid">
            <div class="about-image-wrap reveal">
                <div class="about-image">
                    <img src="images/home-about.png" alt="О клинике">
                </div>
                <div class="about-image-deco" aria-hidden="true"></div>
            </div>
            <div class="about-content reveal">
                <span class="section-eyebrow">О клинике</span>
                <h2>Ваше здоровье в надёжных руках</h2>
                <p>
                    Поликлиника «Здоровье» — это современный многопрофильный медицинский центр,
                    объединяющий опыт ведущих специалистов и передовые технологии диагностики.
                    Мы работаем для того, чтобы каждый пациент получал внимательное и качественное лечение.
                </p>

                <div class="about-features">
                    <div class="about-feature">
                        <div class="check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                        <div><strong>Лицензия и сертификаты</strong><span>Все направления лицензированы Росздравнадзором</span></div>
                    </div>
                    <div class="about-feature">
                        <div class="check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                        <div><strong>Современное оборудование</strong><span>МРТ, УЗИ, лаборатория экспертного класса</span></div>
                    </div>
                    <div class="about-feature">
                        <div class="check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                        <div><strong>Электронная карта</strong><span>Все результаты и назначения в одном месте</span></div>
                    </div>
                    <div class="about-feature">
                        <div class="check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>
                        <div><strong>Удобное расположение</strong><span>5 минут пешком от метро, бесплатная парковка</span></div>
                    </div>
                </div>

                <a href="apply.php" class="btn btn-primary">Записаться на приём</a>
            </div>
        </div>
    </div>
</section>

<!-- ============== ОТЗЫВЫ ============== -->
<section class="block" style="background: var(--surface);">
    <div class="container">
        <div class="section-head reveal">
            <span class="section-eyebrow">Отзывы</span>
            <h2>Нам доверяют тысячи пациентов</h2>
        </div>

        <div class="testimonials-grid reveal-stagger">
            <div class="testimonial">
                <p>Очень внимательный персонал, всё разъяснили, провели обследование быстро и без очередей. Рекомендую клинику от всего сердца!</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">МК</div>
                    <div><strong>Мария К.</strong><span>пациент</span></div>
                </div>
            </div>
            <div class="testimonial">
                <p>Записалась через сайт — заявку обработали в течение часа. Доктор Смирнова — настоящий профессионал, спасибо за заботу!</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">ОН</div>
                    <div><strong>Ольга Н.</strong><span>пациент</span></div>
                </div>
            </div>
            <div class="testimonial">
                <p>Привожу ребёнка к Марии Игоревне уже три года. Отношение замечательное, всегда найдёт подход и к малышу, и к родителям.</p>
                <div class="testimonial-author">
                    <div class="testimonial-avatar">АД</div>
                    <div><strong>Анна Д.</strong><span>пациент</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============== CTA ============== -->
<section class="block">
    <div class="cta-band reveal">
        <h2>Запишитесь на приём прямо сейчас</h2>
        <p>Заполните короткую заявку — мы перезвоним в течение 30 минут и подберём удобное время.</p>
        <a href="apply.php" class="btn btn-lg">Подать заявку на лечение</a>
    </div>
</section>

<!-- ============== КОНТАКТЫ ============== -->
<section id="contact" class="block">
    <div class="container">
        <div class="section-head reveal">
            <span class="section-eyebrow">Контакты</span>
            <h2>Как нас найти</h2>
        </div>

        <div class="contact-grid reveal-stagger">
            <div class="contact-card">
                <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
                <h4>Адрес</h4>
                <p>г. Москва, ул. Центральная, д. 12<br>5 минут от м. Парк Культуры</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
                <h4>Телефон</h4>
                <p>+7 (495) 123-45-67<br>круглосуточная справочная</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                <h4>Email</h4>
                <p>info@clinic-zdorovie.ru<br>отвечаем в течение часа</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <h4>Часы работы</h4>
                <p>Пн–Пт 08:00–20:00<br>Сб 09:00–17:00, Вс — выходной</p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
