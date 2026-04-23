<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drupal-coder - поддержка сайтов на Drupal</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        /* Дополнительные стили для форм и уведомлений */
        .form-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }
        .form-modal.active {
            display: flex;
        }
        .form-modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        .form-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            cursor: pointer;
            color: #999;
        }
        .form-modal-close:hover {
            color: #f14d34;
        }
        .login-credentials {
            background: #f0f9ff;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
        }
        .login-credentials code {
            background: #e0e0e0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 1.1em;
        }
        .auth-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .login-btn, .logout-btn, .edit-profile-btn {
            background: #4a90e2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        .logout-btn {
            background: #dc3545;
        }
        .edit-profile-btn {
            background: #28a745;
        }
        .login-btn:hover, .logout-btn:hover, .edit-profile-btn:hover {
            transform: translateY(-2px);
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 30px;
        }
        .user-info span {
            color: white;
            font-weight: 500;
        }
        .edit-form-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            z-index: 10001;
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }
        .edit-form-container.active {
            display: flex;
        }
        .edit-form-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group select[multiple] {
            height: 120px;
        }
        .error-text {
            color: #f14d34;
            font-size: 12px;
            margin-top: 5px;
            display: block;
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            z-index: 10002;
            animation: slideInRight 0.3s ease;
            max-width: 400px;
        }
        .notification-success {
            background: #4CAF50;
        }
        .notification-error {
            background: #f14d34;
        }
        .notification-info {
            background: #2196F3;
        }
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
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
                <div class="auth-buttons" id="authButtons">
                    <button class="login-btn" onclick="showLoginModal()">Войти</button>
                </div>
                <div class="mobile-menu-toggle">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </nav>

        <div class="hero">
            <h1 class="hero-title">Поддержка сайтов на Drupal</h1>
            <p class="hero-subtitle">Сопровождение и поддержка сайтов на CMS Drupal любых версий и запущенности</p>
            <button class="btn-primary hero-btn" onclick="document.querySelector('.contact-form').scrollIntoView({behavior: 'smooth'})">ПОДДЕРЖКА DRUPAL</button>
        </div>
    </header>

    <main>
        <!-- Форма анкеты -->
        <section class="contact-form" id="registerForm">
            <div class="container">
                <h2 class="section-title">Регистрация / Заполнение анкеты</h2>
                <div id="formErrors" class="error-message" style="display:none;"></div>
                
                <form id="mainForm" class="support-form" method="POST" action="validate.php">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" id="full_name" name="full_name" placeholder="Ваше ФИО" required>
                        </div>
                        <div class="form-group">
                            <input type="tel" id="phone" name="phone" placeholder="Телефон" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="E-mail" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <input type="date" id="birth_date" name="birth_date" required>
                        </div>
                        <div class="form-group">
                            <select id="gender" name="gender" required>
                                <option value="">Выберите пол</option>
                                <option value="male">Мужской</option>
                                <option value="female">Женский</option>
                                <option value="other">Другой</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <select id="languages" name="languages[]" multiple required>
                            <option value="Pascal">Pascal</option>
                            <option value="C">C</option>
                            <option value="C++">C++</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="PHP">PHP</option>
                            <option value="Python">Python</option>
                            <option value="Java">Java</option>
                            <option value="Haskell">Haskell</option>
                            <option value="Clojure">Clojure</option>
                            <option value="Prolog">Prolog</option>
                            <option value="Scala">Scala</option>
                            <option value="Go">Go</option>
                        </select>
                        <small>Удерживайте Ctrl для выбора нескольких языков</small>
                    </div>
                    <div class="form-group">
                        <textarea id="biography" name="biography" placeholder="Биография (расскажите о себе)" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" id="contract" name="contract" value="1" required>
                            <span>Я ознакомлен(а) с условиями контракта</span>
                        </label>
                    </div>
                    <button type="submit" class="btn-primary form-submit">Отправить</button>
                </form>
            </div>
        </section>

        <!-- Модальное окно с логином/паролем -->
        <div id="credentialsModal" class="form-modal">
            <div class="form-modal-content">
                <span class="form-modal-close" onclick="closeCredentialsModal()">&times;</span>
                <h3>🎉 Регистрация успешна!</h3>
                <div id="credentialsContent"></div>
                <button class="btn-primary" onclick="closeCredentialsModal()" style="width:100%;">Закрыть</button>
            </div>
        </div>

        <!-- Форма входа -->
        <div id="loginModal" class="form-modal">
            <div class="form-modal-content">
                <span class="form-modal-close" onclick="closeLoginModal()">&times;</span>
                <h3>🔐 Вход в систему</h3>
                <div id="loginErrors" class="error-message" style="display:none;"></div>
                <form id="loginFormElement">
                    <div class="form-group">
                        <label>Логин</label>
                        <input type="text" id="login_username" required>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" id="login_password" required>
                    </div>
                    <button type="submit" class="btn-primary" style="width:100%;">Войти</button>
                </form>
            </div>
        </div>

        <!-- Форма редактирования -->
        <div id="editModal" class="edit-form-container">
            <div class="edit-form-content">
                <h3>✏️ Редактирование профиля</h3>
                <div id="editErrors" class="error-message" style="display:none;"></div>
                <form id="editFormElement">
                    <input type="hidden" id="edit_user_id">
                    <div class="form-group">
                        <label>ФИО</label>
                        <input type="text" id="edit_full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input type="text" id="edit_phone" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label>Дата рождения</label>
                        <input type="date" id="edit_birth_date" required>
                    </div>
                    <div class="form-group">
                        <label>Пол</label>
                        <select id="edit_gender">
                            <option value="male">Мужской</option>
                            <option value="female">Женский</option>
                            <option value="other">Другой</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Языки программирования</label>
                        <select id="edit_languages" multiple>
                            <option value="Pascal">Pascal</option>
                            <option value="C">C</option>
                            <option value="C++">C++</option>
                            <option value="JavaScript">JavaScript</option>
                            <option value="PHP">PHP</option>
                            <option value="Python">Python</option>
                            <option value="Java">Java</option>
                            <option value="Haskell">Haskell</option>
                            <option value="Clojure">Clojure</option>
                            <option value="Prolog">Prolog</option>
                            <option value="Scala">Scala</option>
                            <option value="Go">Go</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Биография</label>
                        <textarea id="edit_biography" rows="4"></textarea>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn-primary">Сохранить</button>
                        <button type="button" class="btn-secondary" onclick="closeEditModal()">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
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
