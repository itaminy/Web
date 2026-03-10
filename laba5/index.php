<?php
session_start();

// Загружаем данные из Cookies при первом посещении
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
    // Удаляем Cookies после использования
    setcookie('form_errors', '', time() - 3600, '/');
}

// Получаем старые данные из Cookies при ошибке
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
        <!-- Кнопка входа -->
        <div style="text-align: right; margin-bottom: 20px;">
            <a href="login.php" class="btn-submit" style="padding: 8px 16px; font-size: 14px; width: auto; display: inline-block;">🔐 Войти для редактирования</a>
        </div>
        
        <h1>📝 Анкета пользователя</h1>
        
        <?php
        // Отображаем сообщение об успехе, если оно есть в сессии
        if (isset($_SESSION['success'])) {
            echo '<div class="success-message">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        
        // Отображаем логин и пароль для нового пользователя
        if (isset($_SESSION['new_user_login']) && isset($_SESSION['new_user_password'])) {
            echo '<div class="success-message" style="background-color: #e3f2fd; color: #0d47a1; border-left-color: #0d47a1;">';
            echo '<strong>🎉 Регистрация успешна!</strong><br><br>';
            echo '🔐 <strong>Ваши данные для входа:</strong><br>';
            echo 'Логин: <code style="background: #f5f5f5; padding: 2px 5px; border-radius: 3px;">' . htmlspecialchars($_SESSION['new_user_login']) . '</code><br>';
            echo 'Пароль: <code style="background: #f5f5f5; padding: 2px 5px; border-radius: 3px;">' . htmlspecialchars($_SESSION['new_user_password']) . '</code><br><br>';
            echo '<small>⚠️ Сохраните эти данные! Они понадобятся для редактирования анкеты.</small>';
            echo '</div>';
            
            unset($_SESSION['new_user_login']);
            unset($_SESSION['new_user_password']);
        }
        ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Пожалуйста, исправьте ошибки:</strong>
            </div>
        <?php endif; ?>

        <form action="validate.php" method="POST">
            <!-- ФИО -->
            <div class="form-group <?php echo isset($errors['full_name']) ? 'has-error' : ''; ?>">
                <label for="full_name">👤 ФИО:</label>
                <input type="text" id="full_name" name="full_name" 
                       placeholder="Иванов Иван Иванович"
                       value="<?php echo htmlspecialchars($old['full_name'] ?? $full_name ?? ''); ?>"
                       maxlength="150" required>
                <?php if (isset($errors['full_name'])): ?>
                    <small class="error-text"><?php echo $errors['full_name']; ?></small>
                <?php endif; ?>
            </div>

            <!-- Телефон -->
            <div class="form-group <?php echo isset($errors['phone']) ? 'has-error' : ''; ?>">
                <label for="phone">📞 Телефон:</label>
                <input type="text" id="phone" name="phone" 
                       placeholder="+7 (999) 123-45-67"
                       value="<?php echo htmlspecialchars($old['phone'] ?? $phone ?? ''); ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <small class="error-text"><?php echo $errors['phone']; ?></small>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                <label for="email">✉️ Email:</label>
                <input type="email" id="email" name="email" 
                       placeholder="example@mail.com"
                       value="<?php echo htmlspecialchars($old['email'] ?? $email ?? ''); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <small class="error-text"><?php echo $errors['email']; ?></small>
                <?php endif; ?>
            </div>

            <!-- Дата рождения -->
            <div class="form-group <?php echo isset($errors['birth_date']) ? 'has-error' : ''; ?>">
                <label for="birth_date">🎂 Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo htmlspecialchars($old['birth_date'] ?? $birth_date ?? ''); ?>" required>
                <?php if (isset($errors['birth_date'])): ?>
                    <small class="error-text"><?php echo $errors['birth_date']; ?></small>
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
                    <small class="error-text"><?php echo $errors['gender']; ?></small>
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
                    
                    foreach ($all_languages as $lang) {
                        $selected = in_array($lang, $selected_langs) ? 'selected' : '';
                        echo "<option value=\"$lang\" $selected>$lang</option>";
                    }
                    ?>
                </select>
                <small>Удерживайте Ctrl для выбора нескольких языков</small>
                <?php if (isset($errors['languages'])): ?>
                    <small class="error-text"><?php echo $errors['languages']; ?></small>
                <?php endif; ?>
            </div>

            <!-- Биография -->
            <div class="form-group">
                <label for="biography">📖 Биография:</label>
                <textarea id="biography" name="biography" placeholder="Расскажите о себе..."><?php echo htmlspecialchars($old['biography'] ?? $biography ?? ''); ?></textarea>
            </div>

            <!-- Чекбокс с контрактом -->
            <div class="form-group <?php echo isset($errors['contract']) ? 'has-error' : ''; ?>">
                <div class="checkbox-group">
                    <input type="checkbox" id="contract" name="contract" value="1" 
                        <?php echo (($old['contract'] ?? $contract ?? '') == '1') ? 'checked' : ''; ?> required>
                    <label for="contract">Я ознакомлен(а) с условиями контракта</label>
                </div>
                <?php if (isset($errors['contract'])): ?>
                    <small class="error-text"><?php echo $errors['contract']; ?></small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">💾 Сохранить</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #888;">
            <small>После регистрации вы получите логин и пароль для редактирования анкеты</small>
        </p>
    </div>
</body>
</html>
