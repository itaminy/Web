<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Проверяем авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Подключаем конфиг
$config_file = '/home/u82382/config/laba3/db_config.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

// Функция экранирования
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

$message = '';
$errors = [];

// Получаем данные пользователя
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    $lang_stmt = $pdo->prepare("
        SELECT pl.name FROM programming_languages pl
        JOIN user_languages ul ON pl.id = ul.language_id
        WHERE ul.user_id = ?
    ");
    $lang_stmt->execute([$_SESSION['user_id']]);
    $user_languages = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log('Edit page error: ' . $e->getMessage());
    die('Ошибка базы данных');
}

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors['csrf'] = 'Ошибка безопасности';
    } else {
        try {
            $pdo->beginTransaction();
            
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
            
            $delete = $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?");
            $delete->execute([$_SESSION['user_id']]);
            
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
            
            // Обновляем данные
            $user = $_POST;
            $user_languages = $_POST['languages'] ?? [];
            
            // Регенерируем CSRF токен
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Edit update error: ' . $e->getMessage());
            $errors['database'] = 'Ошибка при обновлении данных';
        }
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
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 40px 20px; }
        .btn-submit { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-size: 16px; width: 100%; }
        .radio-group { display: flex; gap: 20px; margin-top: 8px; }
        .radio-option { display: flex; align-items: center; gap: 5px; }
        select[multiple] { height: 150px; }
        .success-message { background: #e8f5e9; color: #2e7d32; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2e7d32; }
        .error-message { background: #fee; color: #c33; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c33; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        input, select, textarea { width: 100%; padding: 12px 15px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 16px; font-family: inherit; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✏️ Редактирование данных</h1>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <p>Вы вошли как: <strong><?php echo e($_SESSION['user_login']); ?></strong></p>
            <a href="logout.php" style="background: #dc3545; color: white; padding: 8px 16px; border-radius: 5px; text-decoration: none;">Выйти</a>
        </div>
        
        <?php if ($message): ?>
            <div class="success-message"><?php echo e($message); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <strong>Ошибки:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="edit.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="full_name">👤 ФИО:</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo e($user['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">📞 Телефон:</label>
                <input type="text" id="phone" name="phone" 
                       value="<?php echo e($user['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">✉️ Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo e($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="birth_date">🎂 Дата рождения:</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo e($user['birth_date']); ?>" required>
            </div>

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

            <div class="form-group">
                <label for="languages">💻 Любимые языки программирования:</label>
                <select id="languages" name="languages[]" multiple required>
                    <?php
                    $all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    
                    foreach ($all_languages as $lang) {
                        $selected = in_array($lang, $user_languages) ? 'selected' : '';
                        echo "<option value=\"" . e($lang) . "\" $selected>" . e($lang) . "</option>";
                    }
                    ?>
                </select>
                <small>Удерживайте Ctrl для выбора нескольких языков</small>
            </div>

            <div class="form-group">
                <label for="biography">📖 Биография:</label>
                <textarea id="biography" name="biography" rows="6"><?php echo e($user['biography'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-submit">💾 Обновить данные</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php">← Вернуться на главную</a>
        </p>
    </div>
</body>
</html>
