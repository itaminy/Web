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
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 800px; width: 100%; padding: 40px; margin: 20px; }
        h1 { color: #333; margin-bottom: 30px; text-align: center; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .header-buttons { text-align: right; margin-bottom: 20px; }
        .header-buttons a { display: inline-block; padding: 8px 16px; margin-left: 10px; text-decoration: none; border-radius: 5px; font-size: 14px; color: white; }
        .btn-login { background: #667eea; }
        .btn-admin { background: #28a745; }
        .login-info { background: #e3f2fd; color: #0d47a1; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #0d47a1; }
        .login-info code { background: #f5f5f5; padding: 2px 5px; border-radius: 3px; font-size: 1.1em; }
        .error-message { background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33; }
        .success-message { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2e7d32; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input, select, textarea { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; font-family: inherit; transition: all 0.3s ease; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
        .radio-group { display: flex; gap: 20px; flex-wrap: wrap; }
        .radio-option { display: flex; align-items: center; gap: 5px; }
        .checkbox-group { display: flex; align-items: center; gap: 10px; }
        select[multiple] { height: 150px; }
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
                <?php foreach ($errors as $error): ?>
                    <div style="font-size: 14px; margin-top: 5px;">• <?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="validate.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="full_name">👤 ФИО:</label>
                <input type="text" id="full_name" name="full_name" 
                       placeholder="Иванов Иван Иванович"
                       value="<?php echo e($old['full_name'] ?? $full_name ?? ''); ?>"
                       maxlength="150" required>
            </div>

            <div class="form-group">
                <label for="phone">📞 Телефон:</label>
                <input type="text" id="phone" name="phone" 
                       placeholder="+7 (999) 123-45-67"
                       value="<?php echo e($old['phone'] ?? $phone ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">✉️ Email:</label>
                <input type="email" id="email" name="email" 
                       placeholder="example@mail.com"
                       value="<?php echo e($old['email'] ?? $email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="birth_date">🎂 Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo e($old['birth_date'] ?? $birth_date ?? ''); ?>" required>
            </div>

            <div class="form-group">
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
            </div>

            <div class="form-group">
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
            </div>

            <div class="form-group">
                <label for="biography">📖 Биография:</label>
                <textarea id="biography" name="biography" rows="5" placeholder="Расскажите о себе..."><?php echo htmlspecialchars($old['biography'] ?? $biography ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="contract" name="contract" value="1" 
                        <?php echo (($old['contract'] ?? $contract ?? '') == '1') ? 'checked' : ''; ?> required>
                    <label for="contract">Я ознакомлен(а) с условиями контракта</label>
                </div>
            </div>

            <button type="submit" class="btn-submit">💾 Сохранить</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #888;">
            <small>После регистрации вы получите логин и пароль для редактирования анкеты</small>
        </p>
    </div>
</body>
</html>
