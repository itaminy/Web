<?php
session_start();

$config_file = '/home/u82382/config/laba3/db_config.php';

if (!file_exists($config_file)) {
    error_log("Config file not found: $config_file");
    die('Ошибка конфигурации сервера');
}

require_once $config_file;

// Используем константы из конфига
$host = DB_HOST;
$dbname = DB_NAME;
$username = DB_USER;
$password = DB_PASS;

// Массив для ошибок
$errors = [];
$form_data = $_POST;

// Список допустимых языков
$allowed_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];

// Список допустимых значений пола
$allowed_genders = ['male', 'female', 'other'];

try {
    // Подключение к БД
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ВАЛИДАЦИЯ ПОЛЕЙ
    
    // ФИО
    if (empty($_POST['full_name'])) {
        $errors[] = 'Поле ФИО обязательно для заполнения';
    } elseif (strlen($_POST['full_name']) > 150) {
        $errors[] = 'ФИО не должно превышать 150 символов';
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]+$/u', $_POST['full_name'])) {
        $errors[] = 'ФИО должно содержать только буквы, пробелы и дефисы';
    }
    
    // Телефон
    if (empty($_POST['phone'])) {
        $errors[] = 'Поле Телефон обязательно для заполнения';
    } elseif (!preg_match('/^[0-9\-\+\(\)\s]+$/', $_POST['phone'])) {
        $errors[] = 'Телефон содержит недопустимые символы';
    } elseif (strlen($_POST['phone']) > 20) {
        $errors[] = 'Телефон не должен превышать 20 символов';
    }
    
    // Email
    if (empty($_POST['email'])) {
        $errors[] = 'Поле Email обязательно для заполнения';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный формат email';
    } elseif (strlen($_POST['email']) > 100) {
        $errors[] = 'Email не должен превышать 100 символов';
    }
    
    // Дата рождения
    if (empty($_POST['birth_date'])) {
        $errors[] = 'Поле Дата рождения обязательно для заполнения';
    } else {
        $date = DateTime::createFromFormat('Y-m-d', $_POST['birth_date']);
        if (!$date || $date->format('Y-m-d') !== $_POST['birth_date']) {
            $errors[] = 'Некорректный формат даты';
        } elseif ($date > new DateTime()) {
            $errors[] = 'Дата рождения не может быть в будущем';
        } elseif ($date < new DateTime('1900-01-01')) {
            $errors[] = 'Дата рождения не может быть раньше 1900 года';
        }
    }
    
    // Пол
    if (empty($_POST['gender'])) {
        $errors[] = 'Поле Пол обязательно для выбора';
    } elseif (!in_array($_POST['gender'], $allowed_genders)) {
        $errors[] = 'Выбрано недопустимое значение пола';
    }
    
    // Языки программирования
    if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
        $errors[] = 'Выберите хотя бы один язык программирования';
    } else {
        foreach ($_POST['languages'] as $lang) {
            if (!in_array($lang, $allowed_languages)) {
                $errors[] = 'Выбран недопустимый язык программирования: ' . htmlspecialchars($lang);
            }
        }
    }
    
    // Чекбокс контракта
    if (empty($_POST['contract_accepted'])) {
        $errors[] = 'Необходимо подтвердить ознакомление с контрактом';
    }
    
    // Если есть ошибки, сохраняем их и возвращаемся к форме
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $form_data;
        header('Location: index.html');
        exit();
    }
    
    // НАЧИНАЕМ ТРАНЗАКЦИЮ
    $pdo->beginTransaction();
    
    // Подготовленный запрос для вставки пользователя
    $stmt = $pdo->prepare("
        INSERT INTO users (full_name, phone, email, birth_date, gender, biography, contract_accepted) 
        VALUES (:full_name, :phone, :email, :birth_date, :gender, :biography, :contract_accepted)
    ");
    
    // Выполняем запрос
    $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['birth_date'],
        ':gender' => $_POST['gender'],
        ':biography' => !empty($_POST['biography']) ? $_POST['biography'] : null,
        ':contract_accepted' => 1
    ]);
    
    // Получаем ID нового пользователя
    $user_id = $pdo->lastInsertId();
    
    // Подготовленный запрос для вставки языков
    $lang_stmt = $pdo->prepare("
        INSERT INTO user_languages (user_id, language_id) 
        SELECT :user_id, id FROM programming_languages WHERE name = :lang_name
    ");
    
    // Вставляем каждый выбранный язык
    foreach ($_POST['languages'] as $language) {
        $lang_stmt->execute([
            ':user_id' => $user_id,
            ':lang_name' => $language
        ]);
    }
    
    // Подтверждаем транзакцию
    $pdo->commit();
    
    // Сохраняем сообщение об успехе
    $_SESSION['form_success'] = 'Данные успешно сохранены! ID записи: ' . $user_id;
    
    // Перенаправляем обратно к форме
    //header('Location: index.html');
    //exit();
    
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Сохраняем сообщение об ошибке
    $_SESSION['form_errors'] = ['Произошла ошибка при сохранении данных: ' . $e->getMessage()];
    $_SESSION['form_data'] = $form_data;
    
    header('Location: index.html');
    exit();
}
?>
