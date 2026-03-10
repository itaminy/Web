<?php
session_start();

// Подключение к БД
$config_file = '/home/u82382/config/laba3/db_config.php';
if (!file_exists($config_file)) {
    die('Ошибка конфигурации сервера');
}
require_once $config_file;

// HTTP-авторизация
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Требуется авторизация';
    exit;
}

// Проверка авторизации через БД
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ищем администратора в БД
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Проверяем пароль
    if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])) {
        header('WWW-Authenticate: Basic realm="Admin Panel"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Неверный логин или пароль';
        exit;
    }
    
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных');
}

// Обработка действий
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            // Удаление пользователя
            if ($_POST['action'] === 'delete' && isset($_POST['user_id'])) {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$_POST['user_id']]);
                $message = "Пользователь успешно удален";
                $message_type = 'success';
            }
            
            // Обновление пользователя
            if ($_POST['action'] === 'update' && isset($_POST['user_id'])) {
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
                    $_POST['full_name'],
                    $_POST['phone'],
                    $_POST['email'],
                    $_POST['birth_date'],
                    $_POST['gender'],
                    $_POST['biography'],
                    $_POST['user_id']
                ]);
                
                // Обновление языков
                if (isset($_POST['languages']) && is_array($_POST['languages'])) {
                    // Удалить старые языки
                    $pdo->prepare("DELETE FROM user_languages WHERE user_id = ?")->execute([$_POST['user_id']]);
                    
                    // Добавить новые
                    $lang_stmt = $pdo->prepare("
                        INSERT INTO user_languages (user_id, language_id) 
                        SELECT ?, id FROM programming_languages WHERE name = ?
                    ");
                    
                    foreach ($_POST['languages'] as $lang) {
                        $lang_stmt->execute([$_POST['user_id'], $lang]);
                    }
                }
                
                $pdo->commit();
                $message = "Данные пользователя обновлены";
                $message_type = 'success';
            }
            
        } catch (PDOException $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            $message = "Ошибка: " . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Получение статистики
try {
    // Статистика по языкам
    $stats = $pdo->query("
        SELECT 
            pl.name,
            COUNT(ul.user_id) as user_count
        FROM programming_languages pl
        LEFT JOIN user_languages ul ON pl.id = ul.language_id
        GROUP BY pl.id
        ORDER BY user_count DESC, pl.name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Общая статистика
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_with_languages = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM user_languages")->fetchColumn();
    
    // Все пользователи с их языками
    $users = $pdo->query("
        SELECT 
            u.*,
            GROUP_CONCAT(pl.name ORDER BY pl.name SEPARATOR ', ') as languages
        FROM users u
        LEFT JOIN user_languages ul ON u.id = ul.user_id
        LEFT JOIN programming_languages pl ON ul.language_id = pl.id
        GROUP BY u.id
        ORDER BY u.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die('Ошибка получения данных');
}

// Получение данных для редактирования
$edit_user = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($edit_user) {
        $lang_stmt = $pdo->prepare("
            SELECT pl.name FROM programming_languages pl
            JOIN user_languages ul ON pl.id = ul.language_id
            WHERE ul.user_id = ?
        ");
        $lang_stmt->execute([$_GET['edit']]);
        $edit_user['languages'] = $lang_stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Список всех языков
$all_languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 30px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .admin-header h1 {
            color: #333;
            margin: 0;
        }
        .admin-info {
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.1em;
        }
        .stat-number {
            font-size: 2.5em;
            color: #667eea;
            font-weight: bold;
            display: block;
            margin: 10px 0;
        }
        .language-stats {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .language-stats li {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .language-stats li:last-child {
            border-bottom: none;
        }
        .badge {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85em;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #155724;
        }
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #721c24;
        }
        .edit-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .edit-form h3 {
            margin-top: 0;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons a,
        .action-buttons button {
            display: inline-block;
            padding: 5px 10px;
            margin: 0 2px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85em;
            border: none;
            cursor: pointer;
        }
        .btn-edit {
            background: #28a745;
            color: white;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .btn-save {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-cancel {
            background: #6c757d;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1>👨‍💼 Панель администратора</h1>
            <div class="admin-info">
                Вы вошли как: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Статистика -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>📊 Общая статистика</h3>
                <span class="stat-number"><?php echo $total_users; ?></span>
                <p>Всего пользователей</p>
                <p style="margin-top: 10px; color: #666;">
                    Из них выбрали языки: <strong><?php echo $total_with_languages; ?></strong>
                </p>
            </div>
            
            <div class="stat-card">
                <h3>💻 Популярность языков</h3>
                <ul class="language-stats">
                    <?php foreach ($stats as $stat): ?>
                        <li>
                            <span><?php echo htmlspecialchars($stat['name']); ?></span>
                            <span class="badge"><?php echo $stat['user_count']; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <!-- Форма редактирования -->
        <?php if ($edit_user): ?>
            <div class="edit-form">
                <h3>✏️ Редактирование пользователя #<?php echo $edit_user['id']; ?></h3>
                <form action="admin.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    
                    <div class="form-group">
                        <label>ФИО:</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Телефон:</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($edit_user['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Дата рождения:</label>
                        <input type="date" name="birth_date" value="<?php echo $edit_user['birth_date']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пол:</label>
                        <select name="gender">
                            <option value="male" <?php echo $edit_user['gender'] == 'male' ? 'selected' : ''; ?>>Мужской</option>
                            <option value="female" <?php echo $edit_user['gender'] == 'female' ? 'selected' : ''; ?>>Женский</option>
                            <option value="other" <?php echo $edit_user['gender'] == 'other' ? 'selected' : ''; ?>>Другой</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Языки программирования:</label>
                        <select name="languages[]" multiple>
                            <?php foreach ($all_languages as $lang): ?>
                                <?php $selected = in_array($lang, $edit_user['languages'] ?? []) ? 'selected' : ''; ?>
                                <option value="<?php echo $lang; ?>" <?php echo $selected; ?>><?php echo $lang; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small>Удерживайте Ctrl для выбора нескольких</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Биография:</label>
                        <textarea name="biography"><?php echo htmlspecialchars($edit_user['biography'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-save">💾 Сохранить изменения</button>
                    <a href="admin.php" class="btn-cancel">❌ Отмена</a>
                </form>
            </div>
        <?php endif; ?>
        
        <!-- Таблица пользователей -->
        <h3>📋 Список пользователей (<?php echo count($users); ?>)</h3>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Логин</th>
                        <th>ФИО</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Дата рождения</th>
                        <th>Пол</th>
                        <th>Языки</th>
                        <th>Дата регистрации</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['login'] ?? '-'); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                            <td><?php echo $user['birth_date']; ?></td>
                            <td>
                                <?php
                                $gender_labels = [
                                    'male' => 'Мужской',
                                    'female' => 'Женский',
                                    'other' => 'Другой'
                                ];
                                echo $gender_labels[$user['gender']] ?? $user['gender'];
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['languages'] ?? '-'); ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                            <td class="action-buttons">
                                <a href="?edit=<?php echo $user['id']; ?>" class="btn-edit">✏️ Ред.</a>
                                <form action="admin.php" method="POST" style="display: inline;" onsubmit="return confirm('Удалить пользователя <?php echo htmlspecialchars($user['full_name']); ?>?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn-delete">🗑️ Удал.</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (empty($users)): ?>
            <p style="text-align: center; padding: 40px; color: #666;">Нет зарегистрированных пользователей</p>
        <?php endif; ?>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="index.php" style="color: #667eea;">← Вернуться на главную</a>
        </p>
    </div>
</body>
</html>
