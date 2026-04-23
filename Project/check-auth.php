<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && isset($_SESSION['user_login'])) {
    echo json_encode([
        'logged_in' => true,
        'user_id' => $_SESSION['user_id'],
        'login' => $_SESSION['user_login']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
