<?php
session_start();

// Подключаем конфигурацию БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (file_exists($config_file)) {
    require_once $config_file;
}

// Функция для генерации логина (БЕЗ mbstring)
function generateLogin($full_name) {
    // Транслитерация
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

// Функция для генерации пароля
function generatePassword($length = 8) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Массив для ошибок
$errors = [];

// Регулярные выражения для валидации
$patterns = [
    'full_name' => '/^[А-Яа-яЁёA-Za-z\s\-]+$/u',
    'phone' => '/^[\+\d\s\(\)\-]{10,20}$/',
    'email' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
    'birth_date' => '/^\d{4}-\d{2}-\d{2}$/',
    'gender' => '/^(male|female|other)$/'
];

// Сообщения об ошибках
$messages = [
    'full_name' => 'ФИО должно содержать только буквы, пробелы и дефисы',
    'phone' => 'Телефон может содержать только цифры, пробелы, +, -, (, )',
    'email' => 'Введите корректный email (например: name@domain.com)',
    'birth_date' => 'Дата должна быть в формате ГГГГ-ММ-ДД',
    'gender' => 'Выберите допустимое значение пола',
    'languages' => 'Выберите хотя бы один язык программирования из списка',
    'contract' => 'Необходимо подтвердить ознакомление с контрактом'
];

// Валидация ФИО
if (empty($_POST['full_name'])) {
    $errors['full_name'] = 'Поле ФИО обязательно для заполнения';
} elseif (!preg_match($patterns['full_name'], $_POST['full_name'])) {
    $errors['full_name'] = $messages['full_name'];
} elseif (strlen($_POST['full_name']) > 150) {
    $errors['full_name'] = 'ФИО не должно превышать 150 символов';
}

// Валидация телефона
if (empty($_POST['phone'])) {
    $errors['phone'] = 'Поле Телефон обязательно для заполнения';
} elseif (!preg_match($patterns['phone'], $_POST['phone'])) {
    $errors['phone'] = $messages['phone'];
}

// Валидация email
if (empty($_POST['email'])) {
    $errors['email'] = 'Поле Email обязательно для заполнения';
} elseif (!preg_match($patterns['email'], $_POST['email'])) {
    $errors['email'] = $messages['email'];
}

// Валидация даты рождения
if (empty($_POST['birth_date'])) {
    $errors['birth_date'] = 'Поле Дата рождения обязательно для заполнения';
} elseif (!preg_match($patterns['birth_date'], $_POST['birth_date'])) {
    $errors['birth_date'] = $messages['birth_date'];
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birth_date']) {
        $errors['birth_date'] = 'Некорректная дата';
    } elseif ($date > new DateTime()) {
        $errors['birth_date'] = 'Дата рождения не может быть в будущем';
    }
}

// Валидация пола
if (empty($_POST['gender'])) {
    $errors['gender'] = 'Выберите пол';
} elseif (!preg_match($patterns['gender'], $_POST['gender'])) {
    $errors['gender'] = $messages['gender'];
}

// Валидация языков
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $errors['languages'] = $messages['languages'];
} else {
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_languages)) {
            $errors['languages'] = 'Выбран недопустимый язык программирования';
            break;
        }
    }
}

// Валидация чекбокса
if (empty($_POST['contract'])) {
    $errors['contract'] = $messages['contract'];
}

// Если есть ошибки
if (!empty($errors)) {
    setcookie('form_errors', json_encode($errors), 0, '/');
    setcookie('form_old', json_encode($_POST), 0, '/');
    header('Location: index.php');
    exit();
}

// Если ошибок нет - сохраняем в БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->beginTransaction();
    
    // Генерируем логин и пароль
    $login = generateLogin($_POST['full_name']);
    $password = generatePassword();
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Проверяем уникальность логина
    $check_login = $pdo->prepare("SELECT id FROM users WHERE login = ?");
    $check_login->execute([$login]);
    
    $counter = 1;
    while ($check_login->fetch()) {
        $login = generateLogin($_POST['full_name']) . $counter;
        $check_login->execute([$login]);
        $counter++;
    }
    
    // Сохраняем пользователя
    $stmt = $pdo->prepare("
        INSERT INTO users (login, password_hash, full_name, phone, email, birth_date, gender, biography, contract_accepted) 
        VALUES (:login, :password_hash, :full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)
    ");
    
    $stmt->execute([
        ':login' => $login,
        ':password_hash' => $password_hash,
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['birth_date'],
        ':gender' => $_POST['gender'],
        ':biography' => $_POST['biography'] ?? null,
        ':contract_accepted' => 1
    ]);
    
    $user_id = $pdo->lastInsertId();
    
    // Сохраняем языки
    $lang_stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language_id) 
        SELECT :user_id, id FROM programming_languages WHERE name = :lang_name
    ");
    
    foreach ($_POST['languages'] as $language) {
        $lang_stmt->execute([
            ':user_id' => $user_id,
            ':lang_name' => $language
        ]);
    }
    
    $pdo->commit();
    
    // Сохраняем логин и пароль в сессии для отображения
    $_SESSION['new_user_login'] = $login;
    $_SESSION['new_user_password'] = $password;
    $_SESSION['success'] = 'Данные успешно сохранены!';
    
    // Сохраняем данные в Cookies на 1 год
    setcookie('full_name', $_POST['full_name'], time() + 31536000, '/');
    setcookie('phone', $_POST['phone'], time() + 31536000, '/');
    setcookie('email', $_POST['email'], time() + 31536000, '/');
    setcookie('birth_date', $_POST['birth_date'], time() + 31536000, '/');
    setcookie('gender', $_POST['gender'], time() + 31536000, '/');
    setcookie('languages', implode(',', $_POST['languages']), time() + 31536000, '/');
    setcookie('biography', $_POST['biography'] ?? '', time() + 31536000, '/');
    setcookie('contract', '1', time() + 31536000, '/');
    
    header('Location: index.php');
    exit();
    
} catch (PDOException $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    setcookie('form_errors', json_encode(['database' => 'Ошибка базы данных']), 0, '/');
    setcookie('form_old', json_encode($_POST), 0, '/');
    header('Location: index.php');
    exit();
}
?>
