<?php
// Безопасные настройки
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

// CSRF защита
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Заголовки безопасности
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    error_log("Config file not found");
    die('Ошибка конфигурации сервера');
}
require_once $config_file;

// Загружаем данные из Cookies
$full_name = $_COOKIE['full_name'] ?? '';
$phone = $_COOKIE['phone'] ?? '';
$email = $_COOKIE['email'] ?? '';
$birth_date = $_COOKIE['birth_date'] ?? '';
$gender = $_COOKIE['gender'] ?? '';
$languages = isset($_COOKIE['languages']) ? explode(',', $_COOKIE['languages']) : [];
$biography = $_COOKIE['biography'] ?? '';

// Получаем ошибки из Cookies
$errors = [];
if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    setcookie('form_errors', '', time() - 3600, '/');
}

// Получаем старые данные
$old = [];
if (isset($_COOKIE['form_old'])) {
    $old = json_decode($_COOKIE['form_old'], true);
    setcookie('form_old', '', time() - 3600, '/');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Анкета пользователя</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="login.php" style="background: #4a90e2; color: white; padding: 8px 16px; text-decoration: none; margin-right: 10px;">Войти</a>
            <a href="admin.php" style="background: #6c757d; color: white; padding: 8px 16px; text-decoration: none;">Админ</a>
        </div>
        
        <h1>Анкета пользователя</h1>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['new_user_login']) && isset($_SESSION['new_user_password'])): ?>
            <div class="success-message">
                <strong>Регистрация успешна!</strong><br>
                Логин: <?php echo htmlspecialchars($_SESSION['new_user_login']); ?><br>
                Пароль: <?php echo htmlspecialchars($_SESSION['new_user_password']); ?><br>
                <small>Сохраните эти данные!</small>
            </div>
            <?php 
            unset($_SESSION['new_user_login']);
            unset($_SESSION['new_user_password']);
            ?>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="validate.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                <label>ФИО:</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($old['full_name'] ?? $full_name ?? ''); ?>" maxlength="150" required>
                <?php if (isset($errors['full_name'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['full_name']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                <label>Телефон:</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($old['phone'] ?? $phone ?? ''); ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['phone']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? $email ?? ''); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['email']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['birth_date']) ? 'has-error' : ''; ?>">
                <label>Дата рождения:</label>
                <input type="date" name="birth_date" value="<?php echo htmlspecialchars($old['birth_date'] ?? $birth_date ?? ''); ?>" required>
                <?php if (isset($errors['birth_date'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['birth_date']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['gender']) ? 'has-error' : ''; ?>">
                <label>Пол:</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male" <?php echo (($old['gender'] ?? $gender ?? '') == 'male') ? 'checked' : ''; ?> required> Мужской</label>
                    <label><input type="radio" name="gender" value="female" <?php echo (($old['gender'] ?? $gender ?? '') == 'female') ? 'checked' : ''; ?>> Женский</label>
                    <label><input type="radio" name="gender" value="other" <?php echo (($old['gender'] ?? $gender ?? '') == 'other') ? 'checked' : ''; ?>> Другой</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['gender']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group <?php echo isset($errors['languages']) ? 'has-error' : ''; ?>">
                <label>Языки программирования:</label>
                <select name="languages[]" multiple required>
                    <?php
                    $all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    $selected_langs = $old['languages'] ?? $languages ?? [];
                    
                    foreach ($all_languages as $lang):
                        $selected = in_array($lang, $selected_langs) ? 'selected' : '';
                    ?>
                        <option value="<?php echo htmlspecialchars($lang); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($lang); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small>Удерживайте Ctrl для выбора нескольких</small>
                <?php if (isset($errors['languages'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['languages']); ?></small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Биография:</label>
                <textarea name="biography"><?php echo htmlspecialchars($old['biography'] ?? $biography ?? ''); ?></textarea>
            </div>

            <div class="form-group <?php echo isset($errors['contract']) ? 'has-error' : ''; ?>">
                <label>
                    <input type="checkbox" name="contract" value="1" <?php echo (($old['contract'] ?? '') == '1') ? 'checked' : ''; ?> required>
                    Я ознакомлен с условиями
                </label>
                <?php if (isset($errors['contract'])): ?>
                    <small class="error-text"><?php echo htmlspecialchars($errors['contract']); ?></small>
                <?php endif; ?>
            </div>

            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>
