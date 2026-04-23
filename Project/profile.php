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
if (!file_exists($config_file)) {
    die('Configuration error');
}
require_once $config_file;

// Функция экранирования
function e($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

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
    error_log('Profile page error: ' . $e->getMessage());
    die('Ошибка базы данных');
}

$gender_text = [
    'male' => 'Мужской',
    'female' => 'Женский',
    'other' => 'Другой'
][$user['gender']] ?? 'Не указан';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль пользователя</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 600px; width: 100%; padding: 40px; margin: 20px; }
        h1 { color: #333; margin-bottom: 30px; text-align: center; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        .profile-field { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .profile-label { font-weight: bold; color: #667eea; font-size: 14px; margin-bottom: 5px; }
        .profile-value { font-size: 16px; color: #333; }
        .button-group { display: flex; gap: 15px; justify-content: center; margin-top: 30px; }
        .btn-edit { background: #4a90e2; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; display: inline-block; }
        .btn-logout { background: #dc3545; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; display: inline-block; }
        .btn-edit:hover, .btn-logout:hover { transform: translateY(-2px); }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Профиль пользователя</h1>
        
        <div class="profile-field">
            <div class="profile-label">Логин</div>
            <div class="profile-value"><?php echo e($user['login']); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">ФИО</div>
            <div class="profile-value"><?php echo e($user['full_name']); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Телефон</div>
            <div class="profile-value"><?php echo e($user['phone']); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Email</div>
            <div class="profile-value"><?php echo e($user['email']); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Дата рождения</div>
            <div class="profile-value"><?php echo e($user['birth_date']); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Пол</div>
            <div class="profile-value"><?php echo e($gender_text); ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Любимые языки программирования</div>
            <div class="profile-value"><?php echo !empty($user_languages) ? implode(', ', array_map('e', $user_languages)) : 'Не указаны'; ?></div>
        </div>
        
        <div class="profile-field">
            <div class="profile-label">Биография</div>
            <div class="profile-value"><?php echo nl2br(e($user['biography'] ?? 'Не указана')); ?></div>
        </div>
        
        <div class="button-group">
            <a href="edit.php" class="btn-edit">✏️ Редактировать</a>
            <a href="logout.php" class="btn-logout">🚪 Выйти</a>
        </div>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php">← Вернуться на главную</a>
        </p>
    </div>
</body>
</html>
