<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Подключение к БД
$config_file = '/home/u82382/www/Web/db_config.php';
if (!file_exists($config_file)) {
    echo json_encode(['error' => 'Configuration error: db_config.php not found']);
    exit();
}
require_once $config_file;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ========== GET /api.php?action=doctors (список врачей) ==========
if ($method === 'GET' && $action === 'doctors') {
    $stmt = $pdo->query("SELECT id, name, specialty, experience, room, phone, email, photo FROM doctors");
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($doctors, JSON_UNESCAPED_UNICODE);
    exit();
}

// ========== GET /api.php?action=diseases (список болезней) ==========
if ($method === 'GET' && $action === 'diseases') {
    $stmt = $pdo->query("SELECT id, name, icd10, severity, description FROM diseases");
    $diseases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($diseases, JSON_UNESCAPED_UNICODE);
    exit();
}

// ========== POST /api.php?action=application (отправка заявки) ==========
if ($method === 'POST' && $action === 'application') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $errors = [];
    if (empty($data['name'])) $errors['name'] = 'Введите ФИО';
    if (empty($data['phone'])) $errors['phone'] = 'Введите телефон';
    if (empty($data['symptoms'])) $errors['symptoms'] = 'Опишите симптомы';
    
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    $stmt = $pdo->prepare("INSERT INTO applications (name, phone, email, desired_date, doctor_id, symptoms, status) VALUES (?, ?, ?, ?, ?, ?, 'new')");
    $stmt->execute([
        $data['name'],
        $data['phone'],
        $data['email'] ?? null,
        $data['date'] ?? null,
        $data['doctor_id'] ?? null,
        $data['symptoms']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Заявка отправлена! Мы свяжемся с вами.']);
    exit();
}

// ========== POST /api.php?action=update_application_status (изменение статуса) ==========
if ($method === 'POST' && $action === 'update_application_status') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$data['status'], $data['id']]);
    
    echo json_encode(['success' => true]);
    exit();
}

// ========== POST /api.php?action=register_doctor (регистрация врача) ==========
if ($method === 'POST' && $action === 'register_doctor') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $login = strtolower(trim($data['name']));
    $login = preg_replace('/[^a-z0-9]/', '', $login) . rand(100, 999);
    
    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("UPDATE doctors SET login = ?, password_hash = ? WHERE id = ?");
    $stmt->execute([$login, $password_hash, $data['id']]);
    
    echo json_encode(['success' => true, 'login' => $login, 'password' => $password]);
    exit();
}

// ========== POST /api.php?action=login (вход врача) ==========
if ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE login = ?");
    $stmt->execute([$data['login']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($doctor && password_verify($data['password'], $doctor['password_hash'])) {
        $_SESSION['doctor_id'] = $doctor['id'];
        $_SESSION['doctor_name'] = $doctor['name'];
        echo json_encode(['success' => true, 'doctor' => $doctor]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Неверный логин или пароль']);
    }
    exit();
}

echo json_encode(['error' => 'Неверный запрос']);
?>
