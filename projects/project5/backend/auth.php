<?php
require_once 'config.php';

/**
 * Класс для работы с авторизацией и аутентификацией
 */
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Регистрация нового пользователя
     */
    public function register($data) {
        try {
            // Валидация данных
            $errors = $this->validateRegistrationData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Проверяем, не существует ли уже пользователь с таким email
            $existingUser = $this->db->fetchOne(
                "SELECT client_id FROM Clients WHERE email = ?", 
                [$data['email']]
            );
            
            if ($existingUser) {
                return ['success' => false, 'errors' => ['email' => 'Пользователь с таким email уже существует']];
            }
            
            // Хешируем пароль
            $hashedPassword = hashPassword($data['password']);
            
            // Вставляем нового клиента
            $sql = "INSERT INTO Clients (first_name, last_name, phone, email, password, city_id, address, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['phone'],
                $data['email'],
                $hashedPassword,
                $data['city_id'] ?? null,
                $data['address'] ?? null
            ]);
            
            $clientId = $this->db->lastInsertId();
            
            // Автоматически авторизуем пользователя
            $this->loginById($clientId, ROLE_CLIENT);
            
            return ['success' => true, 'message' => 'Регистрация успешно завершена'];
            
        } catch (Exception $e) {
            logError("Registration error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Ошибка при регистрации']];
        }
    }
    
    /**
     * Авторизация пользователя
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Сначала ищем среди клиентов
            $user = $this->db->fetchOne(
                "SELECT client_id as id, first_name, last_name, email, password, 'client' as type 
                 FROM Clients WHERE email = ?", 
                [$email]
            );
            
            // Если не найден среди клиентов, ищем среди сотрудников
            if (!$user) {
                $user = $this->db->fetchOne(
                    "SELECT e.employee_id as id, e.first_name, e.last_name, e.phone, e.salary, 
                            er.role_name, e.role_id, e.password, 'employee' as type
                     FROM Employees e 
                     JOIN EmployeeRoles er ON e.role_id = er.role_id 
                     WHERE e.phone = ?", 
                    [$email]
                );
            }
            
            if (!$user || !verifyPassword($password, $user['password'])) {
                return ['success' => false, 'error' => 'Неверный email или пароль'];
            }
            
            // Определяем роль пользователя
            $roleId = ROLE_CLIENT;
            if ($user['type'] === 'employee') {
                $roleId = $user['role_id'];
            }
            
            // Создаем сессию
            $this->createSession($user['id'], $user['type'], $roleId, $user);
            
            // Устанавливаем cookie если нужно
            if ($rememberMe) {
                $this->setRememberMeCookie($user['id'], $user['type']);
            }
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            logError("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при авторизации'];
        }
    }
    
    /**
     * Авторизация по ID (для автоматической авторизации после регистрации)
     */
    private function loginById($id, $roleId, $type = 'client') {
        if ($type === 'client') {
            $user = $this->db->fetchOne(
                "SELECT client_id as id, first_name, last_name, email FROM Clients WHERE client_id = ?", 
                [$id]
            );
        } else {
            $user = $this->db->fetchOne(
                "SELECT employee_id as id, first_name, last_name, phone FROM Employees WHERE employee_id = ?", 
                [$id]
            );
        }
        
        if ($user) {
            $this->createSession($id, $type, $roleId, $user);
        }
    }
    
    /**
     * Создание сессии
     */
    private function createSession($userId, $userType, $roleId, $userData) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_role'] = $roleId;
        $_SESSION['user_data'] = $userData;
        $_SESSION['login_time'] = time();
        
        // Регенерируем ID сессии для безопасности
        session_regenerate_id(true);
    }
    
    /**
     * Установка cookie для "Запомнить меня"
     */
    private function setRememberMeCookie($userId, $userType) {
        $token = generateToken();
        $expiry = time() + COOKIE_LIFETIME;
        
        // Сохраняем токен в базе данных
        $this->db->query(
            "INSERT INTO user_tokens (user_id, user_type, token, expires_at) VALUES (?, ?, ?, FROM_UNIXTIME(?))",
            [$userId, $userType, $token, $expiry]
        );
        
        // Устанавливаем cookie
        setcookie(COOKIE_NAME, $token, $expiry, '/', '', false, true);
    }
    
    /**
     * Проверка авторизации по cookie
     */
    public function checkRememberMeCookie() {
        if (!isset($_COOKIE[COOKIE_NAME])) {
            return false;
        }
        
        $token = $_COOKIE[COOKIE_NAME];
        
        // Ищем токен в базе данных
        $tokenData = $this->db->fetchOne(
            "SELECT user_id, user_type FROM user_tokens 
             WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
        
        if (!$tokenData) {
            // Удаляем недействительный cookie
            setcookie(COOKIE_NAME, '', time() - 3600, '/');
            return false;
        }
        
        // Авторизуем пользователя
        if ($tokenData['user_type'] === 'client') {
            $this->loginById($tokenData['user_id'], ROLE_CLIENT, 'client');
        } else {
            $employee = $this->db->fetchOne(
                "SELECT role_id FROM Employees WHERE employee_id = ?",
                [$tokenData['user_id']]
            );
            $this->loginById($tokenData['user_id'], $employee['role_id'], 'employee');
        }
        
        return true;
    }
    
    /**
     * Выход из системы
     */
    public function logout() {
        // Удаляем токен из базы данных
        if (isset($_COOKIE[COOKIE_NAME])) {
            $this->db->query(
                "DELETE FROM user_tokens WHERE token = ?",
                [$_COOKIE[COOKIE_NAME]]
            );
            setcookie(COOKIE_NAME, '', time() - 3600, '/');
        }
        
        // Очищаем сессию
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Получение текущего пользователя
     */
    public function getCurrentUser() {
        // Проверяем сессию
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_data'];
        }
        
        // Проверяем cookie
        if ($this->checkRememberMeCookie()) {
            return $_SESSION['user_data'];
        }
        
        return null;
    }
    
    /**
     * Проверка прав доступа
     */
    public function hasPermission($requiredRole) {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        return $_SESSION['user_role'] >= $requiredRole;
    }
    
    /**
     * Валидация данных регистрации
     */
    private function validateRegistrationData($data) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Имя обязательно для заполнения';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Фамилия обязательна для заполнения';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email обязателен для заполнения';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный формат email';
        }
        
        if (empty($data['password'])) {
            $errors['password'] = 'Пароль обязателен для заполнения';
        } elseif (strlen($data['password']) < 6) {
            $errors['password'] = 'Пароль должен содержать минимум 6 символов';
        }
        
        if ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }
        
        if (!empty($data['phone']) && !preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $data['phone'])) {
            $errors['phone'] = 'Некорректный формат телефона';
        }
        
        return $errors;
    }
}

// Обработка AJAX запросов только при прямом обращении к auth.php
// Это предотвращает перехват запросов при include из других скриптов
if (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'auth.php' && isAjaxRequest()) {
    $auth = new Auth();
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            $email = sanitizeInput($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $rememberMe = isset($_POST['remember_me']);
            
            $result = $auth->login($email, $password, $rememberMe);
            sendJsonResponse($result);
            break;
            
        case 'register':
            $data = [
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'city_id' => (int)($_POST['city_id'] ?? 0) ?: null,
                'address' => sanitizeInput($_POST['address'] ?? '')
            ];
            
            $result = $auth->register($data);
            sendJsonResponse($result);
            break;
            
        case 'logout':
            $result = $auth->logout();
            sendJsonResponse($result);
            break;
            
        case 'check_auth':
            $user = $auth->getCurrentUser();
            if ($user) {
                sendJsonResponse(['success' => true, 'user' => $user]);
            } else {
                sendJsonResponse(['success' => false, 'user' => null]);
            }
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 400);
    }
}
?>