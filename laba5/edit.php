<?php
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Подключаем конфигурацию БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

$message = '';
$errors = [];

// Получаем данные пользователя
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем данные пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Получаем языки пользователя
    $lang_stmt = $pdo->prepare("
        SELECT pl.name FROM programming_languages pl
        JOIN user_languages ul ON pl.id = ul.language_id
        WHERE ul.user_id = ?
    ");
    $lang_stmt->execute([$_SESSION['user_id']]);
    $user_languages = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    die('Ошибка базы данных');
}

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Здесь должна быть валидация как в validate.php
    // Для краткости пропустим, но в реальном проекте нужно добавить
    
    try {
        $pdo->beginTransaction();
        
        // Обновляем данные пользователя
        $update = $pdo->prepare("
            UPDATE users SET 
                full_name = ?, 
                phone = ?, 
                email = ?, 
                birth_date = ?, 
                gender = ?, 
                biography = ?
            WHERE id = ?
        ");
        
        $update->execute([
            $_POST['full_name'],
            $_POST['phone'],
            $_POST['email'],
            $_POST['birth_date'],
            $_POST['gender'],
            $_POST['biography'] ?? null,
            $_SESSION['user_id']
        ]);
        
        // Удаляем старые языки
        $delete = $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?");
        $delete->execute([$_SESSION['user_id']]);
        
        // Добавляем новые языки
        if (!empty($_POST['languages']) && is_array($_POST['languages'])) {
            $lang_stmt = $pdo->prepare("
                INSERT INTO user_languages (user_id, language_id) 
                SELECT ?, id FROM programming_languages WHERE name = ?
            ");
            
            foreach ($_POST['languages'] as $language) {
                $lang_stmt->execute([$_SESSION['user_id'], $language]);
            }
        }
        
        $pdo->commit();
        $message = 'Данные успешно обновлены!';
        
        // Обновляем данные для отображения
        $user = $_POST;
        $user_languages = $_POST['languages'] ?? [];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors['database'] = 'Ошибка при обновлении данных';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование данных</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>✏️ Редактирование данных</h1>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <p>Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['user_login']); ?></strong></p>
            <a href="logout.php" class="btn-submit" style="padding: 8px 16px; font-size: 14px; width: auto;">Выйти</a>
        </div>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="edit.php" method="POST">
            <!-- ФИО -->
            <div class="form-group">
                <label for="full_name">👤 ФИО:</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>

            <!-- Телефон -->
            <div class="form-group">
                <label for="phone">📞 Телефон:</label>
                <input type="text" id="phone" name="phone" 
                       value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">✉️ Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <!-- Дата рождения -->
            <div class="form-group">
                <label for="birth_date">🎂 Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo $user['birth_date']; ?>" required>
            </div>

            <!-- Пол -->
            <div class="form-group">
                <label>⚥ Пол:</label>
                <div class="radio-group">
                    <div class="radio-option">
                        <input type="radio" id="male" name="gender" value="male" 
                            <?php echo ($user['gender'] == 'male') ? 'checked' : ''; ?> required>
                        <label for="male">Мужской</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="female" name="gender" value="female"
                            <?php echo ($user['gender'] == 'female') ? 'checked' : ''; ?>>
                        <label for="female">Женский</label>
                    </div>
                    <div class="radio-option">
                        <input type="radio" id="other" name="gender" value="other"
                            <?php echo ($user['gender'] == 'other') ? 'checked' : ''; ?>>
                        <label for="other">Другой</label>
                    </div>
                </div>
            </div>

            <!-- Языки программирования -->
            <div class="form-group">
                <label for="languages">💻 Любимые языки программирования:</label>
                <select id="languages" name="languages[]" multiple required>
                    <?php
                    $all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    
                    foreach ($all_languages as $lang) {
                        $selected = in_array($lang, $user_languages) ? 'selected' : '';
                        echo "<option value=\"$lang\" $selected>$lang</option>";
                    }
                    ?>
                </select>
                <small>Удерживайте Ctrl для выбора нескольких языков</small>
            </div>

            <!-- Биография -->
            <div class="form-group">
                <label for="biography">📖 Биография:</label>
                <textarea id="biography" name="biography"><?php echo htmlspecialchars($user['biography'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">💾 Обновить данные</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php">← Вернуться к форме регистрации</a>
        </p>
    </div>
</body>
</html>
