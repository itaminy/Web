<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Очищаем сессию
$_SESSION = array();

// Удаляем cookie сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Уничтожаем сессию
session_destroy();

// Перенаправляем на главную
header('Location: index.php');
exit();
?>
