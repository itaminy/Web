<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Ошибка конфигурации');
}
require_once $config_file;

$message = '';
$message_type = '';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Получение данных пользователя
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit();
    }
    
    // Получение языков
    $lang_stmt = $pdo->prepare("
        SELECT pl.name FROM programming_languages pl
        JOIN user_languages ul ON pl.id = ul.language_id
        WHERE ul.user_id = ?
    ");
    $lang_stmt->execute([$_SESSION['user_id']]);
    $user_languages = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    error_log("Edit error: " . $e->getMessage());
    die('Ошибка базы данных');
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Ошибка безопасности');
    }
    
    try {
        $pdo->beginTransaction();
        
        // Обновление данных
        $update = $pdo->prepare("
            UPDATE users SET 
                full_name = ?, phone = ?, email = ?, 
                birth_date = ?, gender = ?, biography = ?
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
        
        // Обновление языков
        $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        
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
        $message = 'Данные обновлены';
        $message_type = 'success';
        
        // Обновление данных для отображения
        $user = $_POST;
        $user_languages = $_POST['languages'] ?? [];
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Update error: " . $e->getMessage());
        $message = 'Ошибка обновления';
        $message_type = 'error';
    }
}

$all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Редактирование</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <h1>Редактирование профиля</h1>
            <div>
                <span>Вы вошли как: <?php echo htmlspecialchars($_SESSION['user_login']); ?></span>
                <a href="logout.php" style="margin-left: 10px; color: #4a90e2;">Выйти</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>ФИО</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Телефон</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            
