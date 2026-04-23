<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Отключаем вывод ошибок
ini_set('display_errors', 0);

// Определяем формат ответа (JSON или XML)
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';
$format = 'json';
if (strpos($accept, 'application/xml') !== false || strpos($accept, 'text/xml') !== false) {
    $format = 'xml';
}

// Функция для отправки ответа
function sendResponse($data, $status = 200, $format = 'json') {
    http_response_code($status);
    
    if ($format === 'xml') {
        header('Content-Type: application/xml; charset=utf-8');
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><response></response>');
        array_to_xml($data, $xml);
        echo $xml->asXML();
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    exit();
}

function array_to_xml($data, &$xml) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (is_numeric($key)) {
                $subnode = $xml->addChild('item');
                array_to_xml($value, $subnode);
            } else {
                $subnode = $xml->addChild($key);
                array_to_xml($value, $subnode);
            }
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    sendResponse(['error' => 'Configuration error'], 500, $format);
}
require_once $config_file;

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
} catch (PDOException $e) {
    sendResponse(['error' => 'Database connection failed'], 500, $format);
}

// Функции валидации
function validateFullName($name) {
    if (empty($name)) return 'Поле ФИО обязательно';
    if (!preg_match('/^[А-Яа-яЁёA-Za-z\s\-]+$/u', $name)) return 'ФИО должно содержать только буквы, пробелы и дефисы';
    if (strlen($name) > 150) return 'ФИО не должно превышать 150 символов';
    return null;
}

function validatePhone($phone) {
    if (empty($phone)) return 'Поле Телефон обязательно';
    if (!preg_match('/^[\+\d\s\(\)\-]{10,20}$/', $phone)) return 'Неверный формат телефона';
    return null;
}

function validateEmail($email) {
    if (empty($email)) return 'Поле Email обязательно';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return 'Неверный формат email';
    return null;
}

function validateBirthDate($date) {
    if (empty($date)) return 'Поле Дата рождения обязательно';
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) return 'Неверный формат даты';
    if ($dateObj > new DateTime()) return 'Дата не может быть в будущем';
    return null;
}

function validateGender($gender) {
    $allowed = ['male', 'female', 'other'];
    if (empty($gender)) return 'Выберите пол';
    if (!in_array($gender, $allowed, true)) return 'Неверное значение пола';
    return null;
}

function validateLanguages($languages) {
    $allowed = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    if (empty($languages) || !is_array($languages)) return 'Выберите хотя бы один язык';
    foreach ($languages as $lang) {
        if (!in_array($lang, $allowed, true)) return 'Недопустимый язык';
    }
    return null;
}

function validateContract($contract) {
    if (empty($contract)) return 'Необходимо подтверждение';
    return null;
}

// Генерация логина
function generateLogin($full_name) {
    $converter = [
        'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
        'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
        'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
        'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N',
        'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
        'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch',
        'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '',
        'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya'
    ];
    
    $name_parts = explode(' ', $full_name);
    $login = '';
    
    if (isset($name_parts[0])) {
        $first = strtr($name_parts[0], $converter);
        $first = preg_replace('/[^a-zA-Z]/', '', $first);
        $login .= strtolower($first);
    }
    if (isset($name_parts[1])) {
        $last = substr($name_parts[1], 0, 2);
        $last = strtr($last, $converter);
        $last = preg_replace('/[^a-zA-Z]/', '', $last);
        $login .= strtolower($last);
    }
    
    $login .= rand(100, 999);
    $login = preg_replace('/[^a-z0-9]/', '', $login);
    
    return $login;
}

function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Получаем метод и путь
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? $_SERVER['REQUEST_URI'] ?? '';
$path = strtok($path, '?');
preg_match('/\/api\/users(?:\/(\d+))?/', $path, $matches);
$user_id = $matches[1] ?? null;

// Разбираем входные данные
$input_data = [];
$content_type = $_SERVER['CONTENT_TYPE'] ?? '';

if (strpos($content_type, 'application/json') !== false) {
    $input_data = json_decode(file_get_contents('php://input'), true) ?? [];
} elseif (strpos($content_type, 'application/xml') !== false || strpos($content_type, 'text/xml') !== false) {
    $xml = simplexml_load_string(file_get_contents('php://input'));
    if ($xml) {
        $input_data = json_decode(json_encode($xml), true);
    }
} else {
    $input_data = $_POST;
}

// POST /api/users (регистрация)
if ($method === 'POST' && !$user_id) {
    $errors = [];
    
    $errors['full_name'] = validateFullName($input_data['full_name'] ?? null);
    $errors['phone'] = validatePhone($input_data['phone'] ?? null);
    $errors['email'] = validateEmail($input_data['email'] ?? null);
    $errors['birth_date'] = validateBirthDate($input_data['birth_date'] ?? null);
    $errors['gender'] = validateGender($input_data['gender'] ?? null);
    $errors['languages'] = validateLanguages($input_data['languages'] ?? null);
    $errors['contract'] = validateContract($input_data['contract'] ?? null);
    
    $errors = array_filter($errors);
    
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400, $format);
    }
    
    try {
        $pdo->beginTransaction();
        
        $login = generateLogin($input_data['full_name']);
        $password = generatePassword();
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $check = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $check->execute([$login]);
        
        $counter = 1;
        while ($check->fetch()) {
            $login = generateLogin($input_data['full_name']) . $counter;
            $check->execute([$login]);
            $counter++;
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO users (
                login, password_hash, full_name, phone, email, 
                birth_date, gender, biography, contract_accepted
            ) VALUES (
                :login, :password_hash, :full_name, :phone, :email,
                :birth_date, :gender, :biography, 1
            )
        ");
        
        $stmt->execute([
            ':login' => $login,
            ':password_hash' => $password_hash,
            ':full_name' => $input_data['full_name'],
            ':phone' => $input_data['phone'],
            ':email' => $input_data['email'],
            ':birth_date' => $input_data['birth_date'],
            ':gender' => $input_data['gender'],
            ':biography' => $input_data['biography'] ?? null
        ]);
        
        $new_user_id = $pdo->lastInsertId();
        
        $lang_stmt = $pdo->prepare("
            INSERT INTO user_languages (user_id, language_id) 
            SELECT ?, id FROM programming_languages WHERE name = ?
        ");
        
        foreach ($input_data['languages'] as $language) {
            $lang_stmt->execute([$new_user_id, $language]);
        }
        
        $pdo->commit();
        
        sendResponse([
            'success' => true,
            'login' => $login,
            'password' => $password,
            'profile_url' => "/edit.php?id=$new_user_id"
        ], 201, $format);
        
    } catch (PDOException $e) {
        if (isset($pdo)) $pdo->rollBack();
        sendResponse(['error' => 'Database error'], 500, $format);
    }
}

// PUT /api/users/{id}
if ($method === 'PUT' && $user_id) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
        sendResponse(['error' => 'Unauthorized'], 401, $format);
    }
    
    $errors = [];
    
    $errors['full_name'] = validateFullName($input_data['full_name'] ?? null);
    $errors['phone'] = validatePhone($input_data['phone'] ?? null);
    $errors['email'] = validateEmail($input_data['email'] ?? null);
    $errors['birth_date'] = validateBirthDate($input_data['birth_date'] ?? null);
    $errors['gender'] = validateGender($input_data['gender'] ?? null);
    $errors['languages'] = validateLanguages($input_data['languages'] ?? null);
    
    $errors = array_filter($errors);
    
    if (!empty($errors)) {
        sendResponse(['errors' => $errors], 400, $format);
    }
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            UPDATE users SET 
                full_name = ?,
                phone = ?,
                email = ?,
                birth_date = ?,
                gender = ?,
                biography = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $input_data['full_name'],
            $input_data['phone'],
            $input_data['email'],
            $input_data['birth_date'],
            $input_data['gender'],
            $input_data['biography'] ?? null,
            $user_id
        ]);
        
        $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$user_id]);
        
        $lang_stmt = $pdo->prepare("
            INSERT INTO user_languages (user_id, language_id) 
            SELECT ?, id FROM programming_languages WHERE name = ?
        ");
        
        foreach ($input_data['languages'] as $language) {
            $lang_stmt->execute([$user_id, $language]);
        }
        
        $pdo->commit();
        
        sendResponse(['success' => true, 'message' => 'Data updated successfully'], 200, $format);
        
    } catch (PDOException $e) {
        if (isset($pdo)) $pdo->rollBack();
        sendResponse(['error' => 'Database error'], 500, $format);
    }
}

// GET /api/users/{id}
if ($method === 'GET' && $user_id) {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != $user_id) {
        sendResponse(['error' => 'Unauthorized'], 401, $format);
    }
    
    try {
        $stmt = $pdo->prepare("
            SELECT id, login, full_name, phone, email, birth_date, gender, biography 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            sendResponse(['error' => 'User not found'], 404, $format);
        }
        
        $lang_stmt = $pdo->prepare("
            SELECT pl.name FROM programming_languages pl
            JOIN user_languages ul ON pl.id = ul.language_id
            WHERE ul.user_id = ?
        ");
        $lang_stmt->execute([$user_id]);
        $user['languages'] = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
        
        sendResponse($user, 200, $format);
        
    } catch (PDOException $e) {
        sendResponse(['error' => 'Database error'], 500, $format);
    }
}

sendResponse(['error' => 'Method not allowed'], 405, $format);
?>
