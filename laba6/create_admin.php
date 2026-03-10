<?php
// Временный файл для создания администратора
// ЗАПУСТИТЕ 1 РАЗ И СРАЗУ УДАЛИТЕ!

session_start();

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Файл конфигурации не найден!');
}
require_once $config_file;

// Данные администратора - ИЗМЕНИТЕ НА СВОИ!
$admin_username = 'admin';
$admin_password = 'admin123'; // Смените на сложный пароль!

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Создаем таблицу админов, если ее нет
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Генерируем хеш пароля
    $password_hash = password_hash($admin_password, PASSWORD_DEFAULT);
    
    // Проверяем, есть ли уже такой админ
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$admin_username]);
    
    if ($stmt->fetch()) {
        // Обновляем пароль существующего админа
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
        $stmt->execute([$password_hash, $admin_username]);
        echo "Пароль администратора обновлен!<br>";
    } else {
        // Создаем нового админа
        $stmt = $pdo->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
        $stmt->execute([$admin_username, $password_hash]);
        echo "Администратор создан!<br>";
    }
    
    echo "<br><strong>Данные для входа:</strong><br>";
    echo "Логин: " . htmlspecialchars($admin_username) . "<br>";
    echo "Пароль: " . htmlspecialchars($admin_password) . "<br>";
    echo "Хеш пароля: " . $password_hash . "<br>";
    echo "<br><span style='color:red; font-weight:bold'>⚠️ УДАЛИТЕ ЭТОТ ФАЙЛ СРАЗУ ПОСЛЕ ИСПОЛЬЗОВАНИЯ!</span>";
    
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>
