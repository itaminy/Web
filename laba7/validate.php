<?php
// Безопасные настройки
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

// Проверка CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log("CSRF attack detected");
    die('Ошибка безопасности');
}

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    error_log("Config file not found");
    die('Ошибка конфигурации');
}
require_once $config_file;

// Функция генерации логина
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
        $first = strtolower($first);
        $login .= $first;
    }
    if (isset($name_parts[1])) {
        $last = substr($name_parts[1], 0, 2);
        $last = strtr($last, $converter);
        $last = strtolower($last);
        $login .= $last;
    }
    
    $login .= rand(100, 999);
    $login = preg_replace('/[^a-z0-9]/', '', $login);
    
    return $login;
}

// Функция генерации пароля
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Валидация
$errors = [];

// ФИО
if (empty($_POST['full_name'])) {
    $errors['full_name'] = 'Поле обязательно';
} elseif (!preg_match('/^[А-Яа-яЁёA-Za-z\s\-]+$/u', $_POST['full_name'])) {
    $errors['full_name'] = 'Только буквы, пробелы и дефисы';
} elseif (strlen($_POST['full_name']) > 150) {
    $errors['full_name'] = 'Не больше 150 символов';
}

// Телефон
if (empty($_POST['phone'])) {
    $errors['phone'] = 'Поле обязательно';
} elseif (!preg_match('/^[\+\d\s\(\)\-]{10,20}$/', $_POST['phone'])) {
    $errors['phone'] = 'Недопустимые символы';
}

// Email
if (empty($_POST['email'])) {
    $errors['email'] = 'Поле обязательно';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Некорректный email';
}

// Дата рождения
if (empty($_POST['birth_date'])) {
    $errors['birth_date'] = 'Поле обязательно';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['birth_date'])) {
    $errors['birth_date'] = 'Формат ГГГГ-ММ-ДД';
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (!$date || $date > new DateTime()) {
        $errors['birth_date'] = 'Некорректная дата';
    }
}

// Пол
$allowed_genders = ['male', 'female', 'other'];
if (empty($_POST['gender'])) {
    $errors['gender'] = 'Выберите пол';
} elseif (!in_array($_POST['gender'], $allowed_genders)) {
    $errors['gender'] = 'Недопустимое значение';
}

// Языки
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = 'Выберите языки';
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_languages)) {
            $errors['languages'] = 'Недопустимый язык';
            break;
        }
    }
}

// Чекбокс
if (empty($_POST['contract'])) {
    $errors['contract'] = 'Примите условия';
}

// Если есть ошибки
if (!empty($errors)) {
    setcookie('form_errors', json_encode($errors), 0, '/');
    setcookie('form_old', json_encode($_POST), 0, '/');
    header('Location: index.php');
    exit();
}

// Сохранение в БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    $pdo->beginTransaction();
    
    // Генерация логина и пароля
    $login = generateLogin($_POST['full_name']);
    $password = generatePassword();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Проверка уникальности логина
    $check = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $check->execute([$login]);
    while ($check->fetch()) {
        $login = generateLogin($_POST['full_name']) . rand(10, 99);
        $check->execute([$login]);
    }
    
    // Сохранение пользователя
    $stmt = $pdo->prepare("
        INSERT INTO users (login, password_hash, full_name, phone, email, birth_date, gender, biography, contract_accepted) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $login,
        $password_hash,
        $_POST['full_name'],
        $_POST['phone'],
        $_POST['email'],
        $_POST['birth_date'],
        $_POST['gender'],
        $_POST['biography'] ?? null,
        1
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Сохранение языков
    $lang_stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language_id) 
        SELECT ?, id FROM programming_languages WHERE name = ?
    ");
    
    foreach ($_POST['languages'] as $language) {
        $lang_stmt->execute([$user_id, $language]);
    }
    
    $pdo->commit();
    
    // Сохранение в сессию
    $_SESSION['new_user_login'] = $login;
    $_SESSION['new_user_password'] = $password;
    $_SESSION['success'] = 'Регистрация успешна!';
    
    // Сохранение в Cookies
    setcookie('full_name', $_POST['full_name'], time() + 31536000, '/', '', false, true);
    setcookie('phone', $_POST['phone'], time() + 31536000, '/', '', false, true);
    setcookie('email', $_POST['email'], time() + 31536000, '/', '', false, true);
    setcookie('birth_date', $_POST['birth_date'], time() + 31536000, '/', '', false, true);
    setcookie('gender', $_POST['gender'], time() + 31536000, '/', '', false, true);
    setcookie('languages', implode(',', $_POST['languages']), time() + 31536000, '/', '', false, true);
    setcookie('biography', $_POST['biography'] ?? '', time() + 31536000, '/', '', false, true);
    
    header('Location: index.php');
    exit();
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    
    setcookie('form_errors', json_encode(['database' => 'Ошибка сохранения']), 0, '/');
    setcookie('form_old', json_encode($_POST), 0, '/');
    header('Location: index.php');
    exit();
}
?>
