
<?php
session_start();

// Заголовки безопасности
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Отключаем вывод ошибок
ini_set('display_errors', 0);

// Подключаем конфиг
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Configuration error');
}
require_once $config_file;

// Проверка CSRF токена
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    error_log('CSRF token mismatch');
    setcookie('form_errors', json_encode(['csrf' => 'Ошибка безопасности']), 0, '/', '', true, true);
    header('Location: index.php');
    exit();
}

// Функция для генерации логина
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

// Функция для генерации пароля
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Массив для ошибок
$errors = [];

// Регулярные выражения
$patterns = [
    'full_name' => '/^[А-Яа-яЁёA-Za-z\s\-]+$/u',
    'phone' => '/^[\+\d\s\(\)\-]{10,20}$/'
];

// Валидация ФИО
if (empty($_POST['full_name'])) {
    $errors['full_name'] = 'Поле ФИО обязательно';
} elseif (!preg_match($patterns['full_name'], $_POST['full_name'])) {
    $errors['full_name'] = 'ФИО должно содержать только буквы, пробелы и дефисы';
} elseif (strlen($_POST['full_name']) > 150) {
    $errors['full_name'] = 'ФИО не должно превышать 150 символов';
} else {
    $_POST['full_name'] = trim($_POST['full_name']);
}

// Валидация телефона
if (empty($_POST['phone'])) {
    $errors['phone'] = 'Поле Телефон обязательно';
} elseif (!preg_match($patterns['phone'], $_POST['phone'])) {
    $errors['phone'] = 'Неверный формат телефона';
} else {
    $_POST['phone'] = trim($_POST['phone']);
}

// Валидация email
if (empty($_POST['email'])) {
    $errors['email'] = 'Поле Email обязательно';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Неверный формат email';
} else {
    $_POST['email'] = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
}

// Валидация даты
if (empty($_POST['birth_date'])) {
    $errors['birth_date'] = 'Поле Дата рождения обязательно';
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birth_date']) {
        $errors['birth_date'] = 'Неверный формат даты';
    } elseif ($date > new DateTime()) {
        $errors['birth_date'] = 'Дата не может быть в будущем';
    }
}

// Валидация пола
$allowed_genders = ['male', 'female', 'other'];
if (empty($_POST['gender'])) {
    $errors['gender'] = 'Выберите пол';
} elseif (!in_array($_POST['gender'], $allowed_genders, true)) {
    $errors['gender'] = 'Неверное значение пола';
}

// Валидация языков
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = 'Выберите хотя бы один язык';
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_languages, true)) {
            $errors['languages'] = 'Недопустимый язык';
            break;
        }
    }
}

// Валидация чекбокса
if (empty($_POST['contract'])) {
    $errors['contract'] = 'Необходимо подтверждение';
}

// Если есть ошибки
if (!empty($errors)) {
    setcookie('form_errors', json_encode($errors), 0, '/', '', true, true);
    setcookie('form_old', json_encode($_POST), 0, '/', '', true, true);
    header('Location: index.php');
    exit();
}

// Сохранение в БД
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $pdo->beginTransaction();
    
    // Генерация логина и пароля
    $login = generateLogin($_POST['full_name']);
    $password = generatePassword();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Проверка уникальности логина
    $check = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $check->execute([$login]);
    
    $counter = 1;
    while ($check->fetch()) {
        $login = generateLogin($_POST['full_name']) . $counter;
        $check->execute([$login]);
        $counter++;
    }
    
    // Вставка пользователя
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
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['birth_date'],
        ':gender' => $_POST['gender'],
        ':biography' => $_POST['biography'] ?? null
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Вставка языков
    $lang_stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language_id) 
        SELECT ?, id FROM programming_languages WHERE name = ?
    ");
    
    foreach ($_POST['languages'] as $language) {
        $lang_stmt->execute([$user_id, $language]);
    }
    
    $pdo->commit();
    
    // Сохраняем в сессию
    $_SESSION['new_user_login'] = $login;
    $_SESSION['new_user_password'] = $password;
    $_SESSION['success'] = 'Данные сохранены!';
    
    // Cookies на год
    $cookie_params = [
        'expires' => time() + 31536000,
        'path' => '/',
        'domain' => '',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ];
    
    setcookie('full_name', $_POST['full_name'], $cookie_params);
    setcookie('phone', $_POST['phone'], $cookie_params);
    setcookie('email', $_POST['email'], $cookie_params);
    setcookie('birth_date', $_POST['birth_date'], $cookie_params);
    setcookie('gender', $_POST['gender'], $cookie_params);
    setcookie('languages', implode(',', $_POST['languages']), $cookie_params);
    setcookie('biography', $_POST['biography'] ?? '', $cookie_params);
    setcookie('contract', '1', $cookie_params);
    
    // Удаляем старый CSRF токен
    unset($_SESSION['csrf_token']);
    
    header('Location: index.php');
    exit();
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    error_log('Database error in validate.php: ' . $e->getMessage());
    
    setcookie('form_errors', json_encode(['database' => 'Ошибка базы данных']), 0, '/', '', true, true);
    setcookie('form_old', json_encode($_POST), 0, '/', '', true, true);
    header('Location: index.php');
    exit();
}
?>
