<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Подключаем конфиг
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Configuration error');
}
require_once $config_file;

// Функция экранирования
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

// Генерация CSRF токена
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверка CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Ошибка безопасности';
    } else {
        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($login) || empty($password)) {
            $error = 'Введите логин и пароль';
        } else {
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
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
                $stmt->execute([$login]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && password_verify($password, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['user_login'] = $user['login'];
                    $_SESSION['user_name'] = $user['full_name'];
                    
                    unset($_SESSION['csrf_token']);
                    
                    header('Location: edit.php');
                    exit();
                } else {
                    $error = 'Неверный логин или пароль';
                }
                
            } catch (PDOException $e) {
                error_log('Login error: ' . $e->getMessage());
                $error = 'Ошибка базы данных';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .login-container { max-width: 400px; margin: 0 auto; }
        .login-links { text-align: center; margin-top: 20px; }
        .login-links a { color: #667eea; text-decoration: none; margin: 0 10px; }
        .login-links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container login-container">
        <h1>🔐 Вход в систему</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo e($error); ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label for="login">👤 Логин:</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo e($_POST['login'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">🔑 Пароль:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn-submit">Войти</button>
        </form>
        
        <div class="login-links">
            <a href="index.php">← Вернуться к форме регистрации</a>
        </div>
    </div>
</body>
</html>
