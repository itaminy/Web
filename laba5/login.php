<?php
session_start();

// Подключаем конфигурацию БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($login) || empty($password)) {
        $error = 'Введите логин и пароль';
    } else {
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Ищем пользователя по логину
            $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Успешный вход - создаем сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_login'] = $user['login'];
                $_SESSION['user_name'] = $user['full_name'];
                
                // Перенаправляем на страницу редактирования
                header('Location: edit.php');
                exit();
            } else {
                $error = 'Неверный логин или пароль';
            }
            
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных';
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
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .login-links {
            text-align: center;
            margin-top: 20px;
        }
        .login-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }
        .login-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <h1>🔐 Вход в систему</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="login">👤 Логин:</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
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
