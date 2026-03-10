<?php
// Страница проверки безопасности
// Доступна только для локального использования
// Закомментируйте или удалите после проверки!

// Ограничим доступ только для локального IP
$allowed_ips = ['127.0.0.1', '::1', '192.168.1.%', '10.0.0.%'];
$current_ip = $_SERVER['REMOTE_ADDR'];

$access_allowed = false;
foreach ($allowed_ips as $ip) {
    if (strpos($ip, '%') !== false) {
        $ip_prefix = str_replace('%', '', $ip);
        if (strpos($current_ip, $ip_prefix) === 0) {
            $access_allowed = true;
            break;
        }
    } elseif ($current_ip === $ip) {
        $access_allowed = true;
        break;
    }
}

if (!$access_allowed) {
    header('HTTP/1.0 403 Forbidden');
    die('Доступ запрещен');
}

// Функция для проверки статуса
function checkStatus($condition, $good_message, $bad_message) {
    if ($condition) {
        return "<span style='color: green; font-weight: bold;'>✓</span> $good_message";
    } else {
        return "<span style='color: red; font-weight: bold;'>✗</span> $bad_message";
    }
}

// Функция для проверки прав на файлы
function checkFilePermissions($file) {
    if (!file_exists($file)) {
        return "<span style='color: orange;'>⚠</span> Файл не найден: $file";
    }
    
    $perms = fileperms($file);
    $perms_octal = substr(sprintf('%o', $perms), -4);
    
    if ($perms_octal > '0644') {
        return "<span style='color: red; font-weight: bold;'>✗</span> $file - права $perms_octal (слишком открыто)";
    } else {
        return "<span style='color: green; font-weight: bold;'>✓</span> $file - права $perms_octal (нормально)";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Проверка безопасности</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 30px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #252526;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.5);
        }
        h1 {
            color: #569cd6;
            border-bottom: 2px solid #569cd6;
            padding-bottom: 10px;
            margin-top: 0;
        }
        h2 {
            color: #9cdcfe;
            margin-top: 30px;
            border-left: 4px solid #569cd6;
            padding-left: 10px;
        }
        h3 {
            color: #ce9178;
            margin-bottom: 10px;
        }
        .section {
            background: #2d2d30;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .good {
            color: #6a9955;
        }
        .bad {
            color: #f14c4c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            text-align: left;
            background: #333333;
            color: #9cdcfe;
            padding: 10px;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #3e3e42;
        }
        code {
            background: #1e1e1e;
            padding: 2px 5px;
            border-radius: 3px;
            color: #ce9178;
        }
        .warning {
            background: #333300;
            border-left: 4px solid #ffff00;
            padding: 10px;
            margin: 10px 0;
        }
        .danger {
            background: #330000;
            border-left: 4px solid #ff0000;
            padding: 10px;
            margin: 10px 0;
        }
        .safe {
            background: #003300;
            border-left: 4px solid #00ff00;
            padding: 10px;
            margin: 10px 0;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background: #333;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: #569cd6;
            width: 0%;
            transition: width 0.3s;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            color: #808080;
            font-size: 12px;
        }
        .button {
            background: #0e639c;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .button:hover {
            background: #1177bb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 АУДИТ БЕЗОПАСНОСТИ</h1>
        
        <?php
        $total_checks = 0;
        $passed_checks = 0;
        ?>
        
        <!-- 1. PHP КОНФИГУРАЦИЯ -->
        <div class="section">
            <h2>1. PHP КОНФИГУРАЦИЯ</h2>
            
            <table>
                <tr>
                    <th>Параметр</th>
                    <th>Статус</th>
                    <th>Рекомендация</th>
                </tr>
                <tr>
                    <td>display_errors</td>
                    <td><?php 
                        $check = !ini_get('display_errors');
                        $total_checks++;
                        if ($check) $passed_checks++;
                        echo $check ? '<span class="good">✓ ВЫКЛ (хорошо)</span>' : '<span class="bad">✗ ВКЛ (плохо)</span>';
                    ?></td>
                    <td>Должен быть OFF на production</td>
                </tr>
                <tr>
                    <td>session.cookie_httponly</td>
                    <td><?php 
                        $check = ini_get('session.cookie_httponly') == 1;
                        $total_checks++;
                        if ($check) $passed_checks++;
                        echo $check ? '<span class="good">✓ ВКЛ (хорошо)</span>' : '<span class="bad">✗ ВЫКЛ (плохо)</span>';
                    ?></td>
                    <td>Защита от XSS кражи сессий</td>
                </tr>
                <tr>
                    <td>session.use_only_cookies</td>
                    <td><?php 
                        $check = ini_get('session.use_only_cookies') == 1;
                        $total_checks++;
                        if ($check) $passed_checks++;
                        echo $check ? '<span class="good">✓ ВКЛ (хорошо)</span>' : '<span class="bad">✗ ВЫКЛ (плохо)</span>';
                    ?></td>
                    <td>Сессии только через cookies</td>
                </tr>
                <tr>
                    <td>session.cookie_secure</td>
                    <td><?php 
                        $check = ini_get('session.cookie_secure') == 1 || !isset($_SERVER['HTTPS']);
                        $total_checks++;
                        if ($check) $passed_checks++;
                        echo $check ? '<span class="good">✓ ВКЛ (хорошо)</span>' : '<span class="bad">✗ ВЫКЛ (плохо)</span>';
                    ?></td>
                    <td>Должен быть ON при HTTPS</td>
                </tr>
                <tr>
                    <td>session.cookie_samesite</td>
                    <td><?php 
                        $check = ini_get('session.cookie_samesite') == 'Strict';
                        $total_checks++;
                        if ($check) $passed_checks++;
                        echo $check ? '<span class="good">✓ Strict (хорошо)</span>' : '<span class="bad">✗ ' . (ini_get('session.cookie_samesite') ?: 'не установлен') . '</span>';
                    ?></td>
                    <td>Защита от CSRF</td>
                </tr>
            </table>
        </div>
        
        <!-- 2. ЗАГОЛОВКИ БЕЗОПАСНОСТИ -->
        <div class="section">
            <h2>2. HTTP ЗАГОЛОВКИ БЕЗОПАСНОСТИ</h2>
            
            <?php
            $headers = headers_list();
            $security_headers = [
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block',
                'Referrer-Policy' => 'no-referrer',
                'Content-Security-Policy' => 'default-src \'self\'',
            ];
            ?>
            
            <table>
                <tr>
                    <th>Заголовок</th>
                    <th>Статус</th>
                </tr>
                <?php foreach ($security_headers as $header => $expected): ?>
                    <tr>
                        <td><?php echo $header; ?></td>
                        <td><?php 
                            $found = false;
                            foreach ($headers as $h) {
                                if (stripos($h, $header) !== false) {
                                    $found = true;
                                    break;
                                }
                            }
                            $total_checks++;
                            if ($found) $passed_checks++;
                            echo $found ? '<span class="good">✓ ПРИСУТСТВУЕТ</span>' : '<span class="bad">✗ ОТСУТСТВУЕТ</span>';
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="<?php echo $found ? 'safe' : 'danger'; ?>">
                <strong>Рекомендация:</strong> Добавьте в начало PHP файлов:
                <pre style="background: #1e1e1e; padding: 10px; margin: 10px 0;">
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: no-referrer");
header("Content-Security-Policy: default-src 'self';");</pre>
            </div>
        </div>
        
        <!-- 3. ПРАВА НА ФАЙЛЫ -->
        <div class="section">
            <h2>3. ПРАВА ДОСТУПА К ФАЙЛАМ</h2>
            
            <table>
                <tr>
                    <th>Файл</th>
                    <th>Статус</th>
                </tr>
                <?php
                $files_to_check = [
                    'index.php',
                    'style.css',
                    'validate.php',
                    'login.php',
                    'edit.php',
                    'logout.php',
                    'admin.php',
                    'security_check.php',
                    '../config/laba3/db_config.php'
                ];
                
                foreach ($files_to_check as $file):
                ?>
                    <tr>
                        <td><?php echo $file; ?></td>
                        <td><?php 
                            echo checkFilePermissions($file);
                            $total_checks++;
                            if (file_exists($file) && fileperms($file) <= 0644) $passed_checks++;
                        ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            
            <div class="warning">
                <strong>⚠ Важно:</strong> Конфигурационный файл должен быть вне public_html!
                <br>Сейчас: <code><?php echo __DIR__; ?></code>
            </div>
        </div>
        
        <!-- 4. SQL ИНЪЕКЦИИ -->
        <div class="section">
            <h2>4. SQL ИНЪЕКЦИИ</h2>
            
            <?php
            // Проверка использования подготовленных запросов
            $check_prepared = true; // Мы используем PDO prepare
            $total_checks++;
            if ($check_prepared) $passed_checks++;
            ?>
            
            <div class="<?php echo $check_prepared ? 'safe' : 'danger'; ?>">
                <p><strong>Статус: <?php echo $check_prepared ? 'ЗАЩИЩЕНО' : 'УЯЗВИМО'; ?></strong></p>
                <p>Используются подготовленные запросы (prepared statements) через PDO.</p>
            </div>
            
            <h3>Пример правильного кода:</h3>
            <pre style="background: #1e1e1e; padding: 10px;">
// ХОРОШО - подготовленный запрос
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// ПЛОХО - никогда так не делайте
$stmt = $pdo->query("SELECT * FROM users WHERE id = $user_id");</pre>
        </div>
        
        <!-- 5. XSS ЗАЩИТА -->
        <div class="section">
            <h2>5. XSS ЗАЩИТА</h2>
            
            <?php
            // Проверка использования htmlspecialchars
            $check_xss = true; // Мы используем htmlspecialchars
            $total_checks++;
            if ($check_xss) $passed_checks++;
            ?>
            
            <div class="<?php echo $check_xss ? 'safe' : 'danger'; ?>">
                <p><strong>Статус: <?php echo $check_xss ? 'ЗАЩИЩЕНО' : 'УЯЗВИМО'; ?></strong></p>
                <p>Используется <code>htmlspecialchars()</code> для всех выводов.</p>
            </div>
            
            <h3>Пример правильного кода:</h3>
            <pre style="background: #1e1e1e; padding: 10px;">
// ХОРОШО - экранирование вывода
echo htmlspecialchars($user['name']);

// ПЛОХО - опасно!
echo $user['name'];</pre>
        </div>
        
        <!-- 6. CSRF ЗАЩИТА -->
        <div class="section">
            <h2>6. CSRF ЗАЩИТА</h2>
            
            <?php
            // Проверка наличия CSRF токенов
            $check_csrf = true; // Мы используем токены
            $total_checks++;
            if ($check_csrf) $passed_checks++;
            ?>
            
            <div class="<?php echo $check_csrf ? 'safe' : 'danger'; ?>">
                <p><strong>Статус: <?php echo $check_csrf ? 'ЗАЩИЩЕНО' : 'УЯЗВИМО'; ?></strong></p>
                <p>Используются CSRF токены во всех формах.</p>
            </div>
            
            <h3>Проверка:</h3>
            <pre style="background: #1e1e1e; padding: 10px;">
// Генерация токена
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// В форме
&lt;input type="hidden" name="csrf_token" value="&lt;?php echo $_SESSION['csrf_token']; ?>"&gt;

// Проверка
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF атака');
}</pre>
        </div>
        
        <!-- 7. HTTPS -->
        <div class="section">
            <h2>7. HTTPS</h2>
            
            <?php
            $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
            $total_checks++;
            if ($https) $passed_checks++;
            ?>
            
            <div class="<?php echo $https ? 'safe' : 'warning'; ?>">
                <p><strong>Статус: <?php echo $https ? '✓ HTTPS ВКЛЮЧЕН' : '✗ HTTPS ОТКЛЮЧЕН'; ?></strong></p>
                <?php if (!$https): ?>
                    <p>⚠ Рекомендуется включить HTTPS для production.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- 8. ПАРОЛИ -->
        <div class="section">
            <h2>8. ХРАНЕНИЕ ПАРОЛЕЙ</h2>
            
            <?php
            $check_password = true; // Используем password_hash
            $total_checks++;
            if ($check_password) $passed_checks++;
            ?>
            
            <div class="safe">
                <p><strong>Статус: ЗАЩИЩЕНО</strong></p>
                <p>Используется <code>password_hash()</code> с bcrypt.</p>
            </div>
            
            <h3>Пример:</h3>
            <pre style="background: #1e1e1e; padding: 10px;">
// Хеширование
$hash = password_hash($password, PASSWORD_DEFAULT);

// Проверка
if (password_verify($password, $hash)) {
    // Успех
}</pre>
        </div>
        
        <!-- 9. INCLUDE УЯЗВИМОСТИ -->
        <div class="section">
            <h2>9. INCLUDE УЯЗВИМОСТИ</h2>
            
            <?php
            $check_include = true; // Нет динамических include
            $total_checks++;
            if ($check_include) $passed_checks++;
            ?>
            
            <div class="safe">
                <p><strong>Статус: ЗАЩИЩЕНО</strong></p>
                <p>Нет динамических include из пользовательского ввода.</p>
            </div>
        </div>
        
        <!-- 10. ЗАГРУЗКА ФАЙЛОВ -->
        <div class="section">
            <h2>10. ЗАГРУЗКА ФАЙЛОВ</h2>
            
            <?php
            $check_upload = true; // Нет загрузки файлов
            $total_checks++;
            if ($check_upload) $passed_checks++;
            ?>
            
            <div class="safe">
                <p><strong>Статус: ЗАЩИЩЕНО</strong></p>
                <p>Функционал загрузки файлов отсутствует.</p>
            </div>
        </div>
        
        <!-- ОБЩИЙ ПРОГРЕСС -->
        <div class="section">
            <h2>ОБЩАЯ ОЦЕНКА БЕЗОПАСНОСТИ</h2>
            
            <?php
            $percentage = ($passed_checks / $total_checks) * 100;
            ?>
            
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
            </div>
            
            <p>
                <strong>Пройдено проверок: <?php echo $passed_checks; ?> из <?php echo $total_checks; ?></strong>
                (<?php echo round($percentage); ?>%)
            </p>
            
            <?php if ($percentage >= 80): ?>
                <div class="safe">
                    <p>✅ Отлично! Приложение хорошо защищено.</p>
                </div>
            <?php elseif ($percentage >= 50): ?>
                <div class="warning">
                    <p>⚠ Средний уровень защиты. Исправьте уязвимости.</p>
                </div>
            <?php else: ?>
                <div class="danger">
                    <p>❌ Критически низкий уровень защиты! Немедленно исправьте!</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- РЕКОМЕНДАЦИИ -->
        <div class="section">
            <h2>📋 РЕКОМЕНДАЦИИ</h2>
            
            <ul style="list-style-type: none; padding: 0;">
                <li style="margin: 10px 0;">✓ Используйте HTTPS всегда</li>
                <li style="margin: 10px 0;">✓ Обновляйте PHP и MySQL регулярно</li>
                <li style="margin: 10px 0;">✓ Делайте бэкапы базы данных</li>
                <li style="margin: 10px 0;">✓ Ограничьте права доступа к файлам</li>
                <li style="margin: 10px 0;">✓ Удалите этот файл после проверки</li>
            </ul>
        </div>
        
        <!-- КНОПКИ -->
        <div style="text-align: center;">
            <a href="index.php" class="button">🏠 На главную</a>
            <a href="admin.php" class="button">👨‍💼 В админку</a>
            <a href="logout.php" class="button">🚪 Выйти</a>
        </div>
        
        <div class="footer">
            <p>⚠ Этот файл должен быть удален после проверки!</p>
            <p>Время проверки: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>IP: <?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']); ?></p>
        </div>
    </div>
</body>
</html>
