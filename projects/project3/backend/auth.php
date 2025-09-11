<?php
session_start();
header('Content-Type: application/json');

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Метод не разрешен']);
    exit();
}

// Получение данных
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Валидация входных данных
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit();
}

// Проверка учетных данных
if ($username === 'admin' && $password === '123') {
    $_SESSION['cargo_auth'] = true;
    $_SESSION['username'] = $username;
    $_SESSION['login_time'] = time();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Авторизация успешна',
        'user' => $username
    ]);
} else {
    // Логирование неудачных попыток входа
    $log_entry = date('Y-m-d H:i:s') . " | Failed login attempt | Username: $username | IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";
    file_put_contents(__DIR__ . '/logs/auth_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Неверные учетные данные. Попробуйте admin/123'
    ]);
}
?>