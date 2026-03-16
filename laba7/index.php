<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Отключаем вывод ошибок
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Безопасное подключение конфига
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Ошибка конфигурации сервера');
}
require_once $config_file;

// Функция экранирования для вывода
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Загружаем данные из Cookies
if (!isset($_SESSION['form_errors']) && !isset($_GET['errors'])) {
    $full_name = $_COOKIE['full_name'] ?? '';
    $phone = $_COOKIE['phone'] ?? '';
    $email = $_COOKIE['email'] ?? '';
    $birth_date = $_COOKIE['birth_date'] ?? '';
    $gender = $_COOKIE['gender'] ?? '';
    $languages = isset($_COOKIE['languages']) ? explode(',', $_COOKIE['languages']) : [];
    $biography = $_COOKIE['biography'] ?? '';
    $contract = $_COOKIE['contract'] ?? '';
}

// Получаем ошибки из Cookies
$errors = [];
if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    setcookie('form_errors', '', time() - 3600, '/', '', true, true);
}

// Получаем старые данные
$old = [];
if (isset($_COOKIE['form_old'])) {
    $old = json_decode($_COOKIE['form_old'], true);
    setcookie('form_old', '', time() - 3600, '/', '', true, true);
}

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета пользователя</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .header-buttons { text-align: right; margin-bottom: 20px; }
        .header-buttons a {
            display: inline-block; padding: 8px 16px; margin-left: 10px;
            text-decoration: none; border-radius: 5px; font-size: 14px; color: white;
        }
        .btn-login { background: #667eea; }
        .btn-admin { background: #28a745; }
        .login-info {
            background: #e3f2fd; color: #0d47a1; padding: 15px;
            border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0d47a1;
        }
        .login-info code {
            background: #f5f5f5; padding: 2px 5px; border-radius: 3px; font-size: 1.1em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-buttons">
            <a href="login.php" class="btn-login">🔐 Войти</a>
            <a href="admin.php" class="btn-admin">👨‍💼 Админ-панель</a>
        </div>
        
        <h1>📝 Анкета пользователя</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo e($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['new_user_login']) && isset($_SESSION['new_user_password'])): ?>
            <div class="login-info">
                <strong>🎉 Регистрация успешна!</strong><br><br>
                🔐 <strong>Ваши данные для входа:</strong><br>
                Логин: <code><?php echo e($_SESSION['new_user_login']); ?></code><br>
                Пароль: <code><?php echo e($_SESSION['new_user_password']); ?></code><br><br>
                <small>⚠️ Сохраните эти данные!</small>
            </div>
            <?php 
            unset($_SESSION['new_user_login']);
            unset($_SESSION['new_user_password']);
            ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Пожалуйста, исправьте ошибки:</strong>
            </div>
        <?php endif; ?>

        <form action="validate.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <!-- ФИО -->
            <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                <label for="full_name">👤 ФИО:</label>
                <input type="text" id="full_name" name="full_name" 
                       placeholder="Иванов Иван Иванович"
                       value="<?php echo e($old['full_name'] ?? $full_name ?? ''); ?>"
                       maxlength="150" required>
                <?php if (isset($errors['full_name'])): ?>
                    <small class="error-text"><?php echo e($errors['full_name']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Телефон -->
            <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                <label for="phone">📞 Телефон:</label>
                <input type="text" id="phone" name="phone" 
                       placeholder="+7 (999) 123-45-67"
                       value="<?php echo e($old['phone'] ?? $phone ?? ''); ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <small class="error-text"><?php echo e($errors['phone']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <label for="email">✉️ Email:</label>
                <input type="email" id="email" name="email" 
                       placeholder="example@mail.com"
                       value="<?php echo e($old['email'] ?? $email ?? ''); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <small class="error-text"><?php echo e($errors['email']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Дата рождения -->
            <div class="form-group <?php echo isset($errors['birth_date']) ? 'has-error' : ''; ?>">
                <label for="birth_date">🎂 Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo e($old['birth_date'] ?? $birth_date ?? ''); ?>" required>
                <?php if (isset($errors['birth_date'])): ?>
                    <small class="error-text"><?php echo e($errors['birth_date']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Пол -->
            <div class="form-group <?php echo isset($errors['gender']) ? 'has-error' : ''; ?>">
                <label>⚥ Пол:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="male" name="gender" value="male" 
                            <?php echo (($old['gender'] ?? $gender ?? '') == 'male') ? 'checked' : ''; ?> required>
                        <label for="male">Мужской</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="female" name="gender" value="female"
                            <?php echo (($old['gender'] ?? $gender ?? '') == 'female') ? 'checked' : ''; ?>>
                        <label for="female">Женский</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="other" name="gender" value="other"
                            <?php echo (($old['gender'] ?? $gender ?? '') == 'other') ? 'checked' : ''; ?>>
                        <label for="other">Другой</label>
                    </div>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <small class="error-text"><?php echo e($errors['gender']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Языки программирования -->
            <div class="form-group <?php echo isset($errors['languages']) ? 'has-error' : ''; ?>">
                <label for="languages">💻 Любимые языки программирования:</label>
                <select id="languages" name="languages[]" multiple required>
                    <?php
                    $all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    $selected_langs = $old['languages'] ?? $languages ?? [];
                    
                    foreach ($all_languages as $lang): 
                        $selected = in_array($lang, $selected_langs) ? 'selected' : '';
                    ?>
                        <option value="<?php echo e($lang); ?>" <?php echo $selected; ?>><?php echo e($lang); ?></option>
                    <?php endforeach; ?>
                </select>
                <small>Удерживайте Ctrl для выбора нескольких языков</small>
                <?php if (isset($errors['languages'])): ?>
                    <small class="error-text"><?php echo e($errors['languages']); ?></small>
                <?php endif; ?>
            </div>

            <!-- Биография -->
            <div class="form-group">
                <label for="biography">📖 Биография:</label>
                <textarea id="biography" name="biography" placeholder="Расскажите о себе..."><?php echo e($old['biography'] ?? $biography ?? ''); ?></textarea>
            </div>

            <!-- Чекбокс -->
            <div class="form-group <?php echo isset($errors['contract']) ? 'has-error' : ''; ?>">
                <div class="checkbox-group">
                    <input type="checkbox" id="contract" name="contract" value="1" 
                        <?php echo (($old['contract'] ?? $contract ?? '') == '1') ? 'checked' : ''; ?> required>
                    <label for="contract">Я ознакомлен(а) с условиями контракта</label>
                </div>
                <?php if (isset($errors['contract'])): ?>
                    <small class="error-text"><?php echo e($errors['contract']); ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">💾 Сохранить</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #888;">
            <small>После регистрации вы получите логин и пароль</small>
        </p>
    </div>
</body>
</html>
