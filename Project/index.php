<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drupal-coder - профессиональная поддержка сайтов на Drupal</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Стили для карусели клиентов */
        .clients-carousel {
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }
        .clients-track {
            display: flex;
            animation: scrollClients 30s linear infinite;
            width: fit-content;
        }
        .clients-track:hover {
            animation-play-state: paused;
        }
        .client-logo {
            flex: 0 0 auto;
            margin: 0 30px;
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            min-width: 180px;
        }
        .client-logo:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }
        .client-logo img {
            max-width: 100%;
            height: 60px;
            object-fit: contain;
            filter: grayscale(100%);
            transition: var(--transition);
        }
        .client-logo:hover img {
            filter: grayscale(0%);
        }
        @keyframes scrollClients {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        
        /* Более человечный стиль отзыва */
        .review-card {
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            max-width: 900px;
            margin: 0 auto;
            position: relative;
        }
        .review-card:before {
            content: "“";
            font-size: 100px;
            color: var(--primary-color);
            opacity: 0.3;
            position: absolute;
            top: 20px;
            left: 30px;
            font-family: Georgia, serif;
        }
        .review-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-dark);
            font-style: normal;
            text-align: justify;
        }
        .review-author {
            border-top: 2px solid var(--border-color);
            padding-top: 25px;
            margin-top: 20px;
        }
        .review-author strong {
            display: block;
            font-size: 1.1rem;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        /* Адаптивность карусели */
        @media (max-width: 768px) {
            .client-logo { min-width: 140px; margin: 0 15px; }
            .client-logo img { height: 45px; }
            .review-card { padding: 30px 25px; }
            .review-card:before { font-size: 60px; top: 10px; left: 20px; }
            .review-content p { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="video-container">
            <video autoplay muted loop playsinline class="video-bg">
                <source src="video.mp4" type="video/mp4">
            </video>
            <div class="video-overlay"></div>
        </div>

        <nav class="navbar">
            <div class="nav-container">
                <div class="logo">
                    <img src="images/drupal-coder.svg" alt="Drupal-coder" class="logo-img">
                </div>
                <ul class="nav-menu">
                    <li><a href="#support">Поддержка сайтов</a></li>
                    <li class="dropdown"><a href="#admin">Администрирование ▼</a>
                        <ul class="dropdown-menu">
                            <li><a href="#migration">Миграция</a></li>
                            <li><a href="#backups">Бэкапы</a></li>
                            <li><a href="#security">Аудит безопасности</a></li>
                            <li><a href="#optimization">Оптимизация скорости</a></li>
                            <li><a href="#https">Переезд на HTTPS</a></li>
                        </ul>
                    </li>
                    <li><a href="#seo">Продвижение</a></li>
                    <li><a href="#ads">Реклама</a></li>
                    <li class="dropdown"><a href="#about">О нас ▼</a>
                        <ul class="dropdown-menu">
                            <li><a href="#team">Команда</a></li>
                            <li><a href="#drupalcive">Drupalcive</a></li>
                            <li><a href="#blog">Блог</a></li>
                            <li><a href="#courses">Курсы Drupal</a></li>
                            <li><a href="#projects">Проекты</a></li>
                        </ul>
                    </li>
                    <li><a href="#contacts">Контакты</a></li>
                </ul>
                <div class="mobile-menu-toggle">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </nav>

        <div class="mobile-menu">
            <div class="mobile-menu-header">
                <div class="mobile-logo-container">
                    <img src="images/drupal-coder.svg" alt="Drupal-coder" class="mobile-logo">
                </div>
                <div class="mobile-close">×</div>
            </div>
            <div class="mobile-menu-content">
                <ul class="mobile-nav">
                    <li><a href="#support">Поддержка сайтов</a></li>
                    <li class="mobile-dropdown">
                        <div class="mobile-dropdown-header">
                            <a href="#admin" class="mobile-dropdown-link">Администрирование</a>
                            <span class="mobile-dropdown-arrow">▼</span>
                        </div>
                        <ul class="mobile-submenu">
                            <li><a href="#migration">Миграция</a></li>
                            <li><a href="#backups">Бэкапы</a></li>
                            <li><a href="#security">Аудит безопасности</a></li>
                            <li><a href="#optimization">Оптимизация скорости</a></li>
                            <li><a href="#https">Переезд на HTTPS</a></li>
                        </ul>
                    </li>
                    <li><a href="#seo">Продвижение</a></li>
                    <li><a href="#ads">Реклама</a></li>
                    <li class="mobile-dropdown">
                        <div class="mobile-dropdown-header">
                            <a href="#about" class="mobile-dropdown-link">О нас</a>
                            <span class="mobile-dropdown-arrow">▼</span>
                        </div>
                        <ul class="mobile-submenu">
                            <li><a href="#team">Команда</a></li>
                            <li><a href="#drupalcive">Drupalcive</a></li>
                            <li><a href="#blog">Блог</a></li>
                            <li><a href="#courses">Курсы Drupal</a></li>
                            <li><a href="#projects">Проекты</a></li>
                        </ul>
                    </li>
                    <li><a href="#contacts">Контакты</a></li>
                </ul>
                <div class="mobile-contacts">
                    <a href="tel:88002222673" class="mobile-phone">8 800 222-26-73</a>
                    <a href="mailto:info@drupal-coder.ru" class="mobile-email">info@drupal-coder.ru</a>
                </div>
            </div>
        </div>

        <div class="hero">
            <h1 class="hero-title">Поддержка сайтов на Drupal</h1>
            <p class="hero-subtitle">Сопровождение и поддержка сайтов на CMS Drupal любых версий и запущенности</p>
            <button class="btn-primary hero-btn" onclick="document.querySelector('.contact-form').scrollIntoView({behavior: 'smooth'})">ПОДДЕРЖКА DRUPAL</button>
        </div>
    </header>

    <main>
        <!-- Компетенции -->
        <section class="competencies">
            <div class="container">
                <h2 class="section-title">13 лет совершенствуем компетенции в Drupal поддержке!</h2>
                <p class="section-subtitle">Разрабатываем и оптимизируем модули, расширяем функциональность сайтов, обновляем дизайн</p>
                <div class="services-grid">
                    <div class="service-card"><div class="service-number">1</div><img src="images/competency-1.svg" class="service-icon"><h3>Добавление информации на сайт, создание новых разделов</h3></div>
                    <div class="service-card"><div class="service-number">2</div><img src="images/competency-2.svg" class="service-icon"><h3>Разработка и оптимизация модулей сайта</h3></div>
                    <div class="service-card"><div class="service-number">3</div><img src="images/competency-3.svg" class="service-icon"><h3>Интеграция с CRM, 1C, платежными системами и любыми веб-сервисами</h3></div>
                    <div class="service-card"><div class="service-number">4</div><img src="images/competency-4.svg" class="service-icon"><h3>Любые доработки функционала и дизайна</h3></div>
                    <div class="service-card"><div class="service-number">5</div><img src="images/competency-5.svg" class="service-icon"><h3>Аудит и мониторинг безопасности Drupal сайтов</h3></div>
                    <div class="service-card"><div class="service-number">6</div><img src="images/competency-6.svg" class="service-icon"><h3>Миграция, импорт контента и апгрейд Drupal</h3></div>
                    <div class="service-card"><div class="service-number">7</div><img src="images/competency-7.svg" class="service-icon"><h3>Оптимизация и ускорение Drupal-сайтов</h3></div>
                    <div class="service-card"><div class="service-number">8</div><img src="images/competency-8.svg" class="service-icon"><h3>Веб-маркетинг, консультации и работы по SEO</h3></div>
                </div>
            </div>
        </section>

        <!-- Поддержка -->
        <section class="support-features">
            <div class="container">
                <h2 class="section-title">Поддержка от Drupal-coder</h2>
                <div class="features-grid">
                    <div class="feature-card"><div class="feature-number">01</div><img src="images/support1.svg" class="feature-icon"><h3>Постановка задач по Email</h3><p>Удобная и привычная модель постановки задач, при которой задачи фиксируются и никогда не теряются</p></div>
                    <div class="feature-card"><div class="feature-number">02</div><img src="images/support2.svg" class="feature-icon"><h3>Система Helpdesk - отчетность, прозрачность</h3><p>Возможность посмотреть все заявки в работе и отработанные часы в личном кабинете через браузер</p></div>
                    <div class="feature-card"><div class="feature-number">03</div><img src="images/support3.svg" class="feature-icon"><h3>Расширенная техническая поддержка</h3><p>Возможность организации расширенной техподдержки с 6:00 до 22:00 без выходных</p></div>
                    <div class="feature-card"><div class="feature-number">04</div><img src="images/support4.svg" class="feature-icon"><h3>Персональный менеджер проекта</h3><p>Ваш менеджер проекта всегда в курсе текущего состояния проекта и в любой момент готов ответить на любые вопросы</p></div>
                    <div class="feature-card"><div class="feature-number">05</div><img src="images/support1.svg" class="feature-icon"><h3>Удобные способы оплаты</h3><p>Безналичный расчет по договору или электронные деньги: WebMoney, Яндекс.Деньги, Paypal</p></div>
                    <div class="feature-card"><div class="feature-number">06</div><img src="images/support2.svg" class="feature-icon"><h3>Работаем с SLA и NDA</h3><p>Работа в рамках соглашений о конфиденциальности и об уровне качества работ</p></div>
                    <div class="feature-card"><div class="feature-number">07</div><img src="images/support3.svg" class="feature-icon"><h3>Штатные специалисты</h3><p>Надежные штатные специалисты, никаких фрилансеров</p></div>
                    <div class="feature-card"><div class="feature-number">08</div><img src="images/support4.svg" class="feature-icon"><h3>Удобные каналы связи</h3><p>Консультации по телефону, скайпу, в мессенджерах</p></div>
                </div>
                <div class="expertise"><h3>Экспертиза в Drupal, опыт 14 лет!</h3><p>Поддержка сайтов на других CMS!</p></div>
            </div>
        </section>

        <!-- Тарифы -->
        <section class="pricing">
            <div class="container">
                <h2 class="section-title">Тарифы</h2>
                <div class="pricing-grid">
                    <div class="pricing-card"><div class="pricing-header"><h3>Стартовый</h3><div class="price">от 6 000 ₽/мес</div></div><ul class="pricing-features"><li>Консультации и работы по SEO</li><li>Услуги дизайнера</li><li>Невоспользованные оплаченные часы переносятся на следующий месяц</li></ul><button class="btn-secondary" onclick="document.querySelector('.contact-form').scrollIntoView({behavior:'smooth'})">Свяжитесь с нами!</button></div>
                    <div class="pricing-card featured"><div class="pricing-badge">Бизнес</div><div class="pricing-header"><h3>Бизнес</h3><div class="price">от 30 000 ₽/мес</div></div><ul class="pricing-features"><li>Консультации и работы по SEO</li><li>Услуги дизайнера</li><li>Высокое время реакции - до 2 рабочих дней</li><li>Неиспользованные оплаченные часы не переносятся</li></ul><button class="btn-primary" onclick="document.querySelector('.contact-form').scrollIntoView({behavior:'smooth'})">Свяжитесь с нами!</button></div>
                    <div class="pricing-card"><div class="pricing-header"><h3>VIP</h3><div class="price">от 270 000 ₽/мес</div></div><ul class="pricing-features"><li>Консультации и работы по SEO</li><li>Услуги дизайнера</li><li>Максимальное время реакции - в день обращения</li><li>Невоспользованные оплаченные часы не переносятся</li></ul><button class="btn-secondary" onclick="document.querySelector('.contact-form').scrollIntoView({behavior:'smooth'})">Выбрать тариф</button></div>
                </div>
                <div class="pricing-cta"><p>Вам не подходят наши тарифы? Оставьте заявку и мы предложим вам индивидуальный тариф!</p><button class="btn-outline" onclick="document.querySelector('.contact-form').scrollIntoView({behavior:'smooth'})">Получить индивидуальный тариф</button></div>
            </div>
        </section>

        <!-- Команда (кратко) -->
        <section class="team-preview">
            <div class="container">
                <h2 class="section-title">Наши профессиональные разработчики выполняют быстро любые задачи</h2>
                <div class="tasks-grid">
                    <div class="task-card"><div class="task-price">от 1ч</div><h3>Настройка события GA в интернет-магазине</h3></div>
                    <div class="task-card"><div class="task-price">от 20ч</div><h3>Разработка мобильной версии сайта</h3></div>
                    <div class="task-card"><div class="task-price">от 8ч</div><h3>Интеграция модуля оплаты</h3></div>
                </div>
            </div>
        </section>

        <!-- Команда -->
        <section class="team">
            <div class="container">
                <h2 class="section-title">Команда</h2>
                <div class="team-grid">
                    <div class="team-member"><img src="images/IMG_2472_0.jpg" class="member-photo"><h3>Сергей Синица</h3><p>Руководитель отдела веб-разработки, канд. техн. наук заместитель директора</p></div>
                    <div class="team-member"><img src="images/IMG_2474_1.jpg" class="member-photo"><h3>Роман Агабеков</h3><p>Руководитель отдела DevOPS, директор</p></div>
                    <div class="team-member"><img src="images/IMG_2539_0.jpg" class="member-photo"><h3>Алексей Синица</h3><p>Руководитель отдела поддержки сайтов</p></div>
                    <div class="team-member"><img src="images/IMG_9971_16.jpg" class="member-photo"><h3>Дарья Бочкарёва</h3><p>Руководитель отдела продвижения, контекстной рекламы и контент-поддержки сайтов</p></div>
                    <div class="team-member"><img src="images/IMG_2522_0.jpg" class="member-photo"><h3>Ирина Торкунова</h3><p>Менеджер по работе с клиентами</p></div>
                </div>
                <div class="team-stats"><div class="stat"><div class="stat-number">15</div><div class="stat-label">человек в команде</div></div><div class="stat"><div class="stat-number">4</div><div class="stat-label">среднее время выполнения задачи (в часах)</div></div></div>
            </div>
        </section>

        <!-- Кейсы -->
        <section class="cases">
            <div class="container">
                <h2 class="section-title">Последние кейсы</h2>
                <div class="cases-grid">
                    <div class="case-card"><h3>Настройка кэширования данных. Апгрейд сервера. Ускорение работы сайта в 30 раз!</h3><p>Влияние скорости загрузки страницы на отказы и конверсию. Кейс ускорения...</p><a href="#" class="case-link">Читать полностью</a></div>
                    <div class="case-card"><h3>Drupal 7: ускорение времени генерации страницы интернет-магазина на 32%</h3><p>Форма заявки с применением тестирования. Опубликован релиз модуля.</p><a href="#" class="case-link">Читать полностью</a></div>
                    <div class="case-card"><h3>Обмен товарами и заказами интернет-магазина на Drupal 7 с 1С: Предприятие, МойСклад, Класс365</h3><p>Модельная задача повышения конверсии страницы с формой...</p><a href="#" class="case-link">Читать полностью</a></div>
                </div>
            </div>
        </section>

        <!-- Отзывы (человечный текст) -->
        <section class="reviews">
            <div class="container">
                <h2 class="section-title">Отзывы</h2>
                <div class="review-card">
                    <div class="review-content">
                        <p>«Ребята из Drupal-coder — настоящие профессионалы своего дела! Когда наш сайт www.cielparfum.com начал постоянно "вылетать" и тормозить, мы отчаялись найти толкового специалиста. Но команда Drupal-coder не просто починила всё — они полностью преобразили сайт! Сделали редизайн, настроили поиск, меню, провели конкурсы и тесты. Любая мелочь — и они уже на связи. Особенно хочу поблагодарить Алексея за оперативность, Сергея за креативные идеи, Надежду и Романа за то, что всегда на страже порядка. Спасибо за вашу работу!»</p>
                    </div>
                    <div class="review-author">
                        <strong>Наталья </strong>
                        <span>Руководитель отдела веб-проектов, ГК «СИ ЭЛЬ ПАРФЮМ»</span>
                        <a href="http://www.cielparfum.com/">www.cielparfum.com</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Клиенты (карусель с автопрокруткой) -->
        <section class="clients">
            <div class="container">
                <h2 class="section-title">С нами работают</h2>
                <p class="section-subtitle">Десятки компаний доверяют нам самое ценное, что у них есть в интернете - свои сайты. Мы делаем всё, чтобы наше сотрудничество было долгим и приятным.</p>
                
                <div class="clients-carousel">
                    <div class="clients-track">
                        <!-- Первая копия логотипов -->
                        <div class="client-logo"><img src="images/farbors_ru.jpg" alt="Фарборс"></div>
                        <div class="client-logo"><img src="images/cableman_ru.png" alt="Cableman"></div>
                        <div class="client-logo"><img src="images/logo-estee.png" alt="Estee"></div>
                        <div class="client-logo"><img src="images/lpcma_rus_v4.jpg" alt="Библиотека"></div>
                        <div class="client-logo"><img src="images/logo_2.png" alt="Sexclos.Ag"></div>
                        <div class="client-logo"><img src="images/nashagazeta_ch.png" alt="Наша газета"></div>
                        <!-- Дубликат для бесшовной анимации -->
                        <div class="client-logo"><img src="images/farbors_ru.jpg" alt="Фарборс"></div>
                        <div class="client-logo"><img src="images/cableman_ru.png" alt="Cableman"></div>
                        <div class="client-logo"><img src="images/logo-estee.png" alt="Estee"></div>
                        <div class="client-logo"><img src="images/lpcma_rus_v4.jpg" alt="Библиотека"></div>
                        <div class="client-logo"><img src="images/logo_2.png" alt="Sexclos.Ag"></div>
                        <div class="client-logo"><img src="images/nashagazeta_ch.png" alt="Наша газета"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="faq">
            <div class="container">
                <h2 class="section-title">FAQ</h2>
                <div class="faq-grid">
                    <div class="faq-item"><h3>1. Кто непосредственно занимается поддержкой?</h3><p>Сайты поддерживают штатные сотрудники ООО «Иннитлаб» (Краснодар), прошедшие специальное обучение и имеющие опыт работы с Drupal от 4 до 15 лет: 8 web-разработчиков, 2 специалиста по SEO, 4 системных администратора.</p></div>
                    <div class="faq-item"><h3>2. Как организована работа поддержки?</h3><p>Работа организована через систему Helpdesk с постановкой задач по email, отслеживанием времени и прозрачной отчетностью.</p></div>
                    <div class="faq-item"><h3>3. Что происходит, когда отработаны все часы за месяц?</h3><p>При исчерпании лимита часов вы можете докупить дополнительные часы или дождаться следующего расчетного периода.</p></div>
                    <div class="faq-item"><h3>4. Что происходит, когда не отработаны все часы за месяц?</h3><p>На некоторых тарифах неиспользованные часы переносятся на следующий месяц, на других — сгорают. Уточняйте условия вашего тарифа.</p></div>
                    <div class="faq-item"><h3>5. Как происходит оценка и согласование времени на выполнение заявок?</h3><p>Менеджер проекта оценивает задачу и согласовывает с вами время на её выполнение перед началом работ.</p></div>
                    <div class="faq-item"><h3>6. Сколько программистов выделяется на проект?</h3><p>Количество программистов зависит от сложности и объёма задач. Обычно от 1 до 3 специалистов.</p></div>
                </div>
            </div>
        </section>

        <!-- Форма связи -->
        <section class="contact-form">
            <div class="container">
                <h2 class="section-title">Оставить заявку на поддержку сайта</h2>
                <form id="supportForm" class="support-form">
                    <div class="form-row"><div class="form-group"><input type="text" id="name" name="name" placeholder="Ваше имя" required></div><div class="form-group"><input type="tel" id="phone" name="phone" placeholder="Телефон" required></div></div>
                    <div class="form-group"><input type="email" id="email" name="email" placeholder="E-mail" required></div>
                    <div class="form-group"><textarea id="message" name="message" placeholder="Комментарий" rows="4"></textarea></div>
                    <button type="submit" class="btn-primary form-submit">Отправить</button>
                </form>
                <div class="form-contacts"><div class="contact-item"><strong>8 800 222-26-73</strong></div><div class="contact-item"><strong>info@drupal-coder.ru</strong></div></div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo"><img src="images/drupal-coder.svg" alt="Drupal-coder" class="footer-logo-img"></div>
                <div class="footer-info"><p>Проект ООО «Иннитлаб», Краснодар, Россия.</p><p>Drupal является зарегистрированной торговой маркой Dries Buytaert.</p></div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
