<?php
require 'db.php';

$fio = trim($_POST['fio']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$birthdate = $_POST['birthdate'];
$gender = $_POST['gender'] ?? '';
$languages = $_POST['languages'] ?? [];
$bio = trim($_POST['bio']);
$contract = isset($_POST['contract']);

$errors = [];

// Валидация
if (!preg_match('/^[a-zA-Zа-яА-Я\s]{1,150}$/u', $fio))
    $errors[] = "ФИО некорректно";

if (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $errors[] = "Email некорректен";

if (!in_array($gender, ['male','female']))
    $errors[] = "Пол некорректен";

if (count($languages) < 1)
    $errors[] = "Выберите хотя бы один язык";

if (!$contract)
    $errors[] = "Не принят контракт";

if ($errors) {
    echo "<h3>Ошибка:</h3><ul>";
    foreach ($errors as $e) echo "<li>$e</li>";
    echo "</ul>";
    exit;
}

// Сохранение пользователя
$stmt = $pdo->prepare("
INSERT INTO users (fio, phone, email, birthdate, gender, bio, contract_agreed)
VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $fio, $phone, $email,
    $birthdate, $gender,
    $bio, $contract
]);

$userId = $pdo->lastInsertId();

// Языки
$langStmt = $pdo->prepare("SELECT id FROM languages WHERE name = ?");
$linkStmt = $pdo->prepare("INSERT INTO user_languages VALUES (?, ?)");

foreach ($languages as $lang) {
    $langStmt->execute([$lang]);
    $langId = $langStmt->fetchColumn();
    $linkStmt->execute([$userId, $langId]);
}

echo "<h2>Данные успешно сохранены</h2>";
