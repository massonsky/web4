<?php
/**
 * Конфигурационный файл для проекта 5: ИТ + Автомастерская
 * Содержит настройки подключения к БД и основные константы
 */

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'autoservicedb');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Настройки сессий и cookies
define('SESSION_LIFETIME', 3600); // 1 час
define('COOKIE_LIFETIME', 86400 * 7); // 7 дней
define('COOKIE_NAME', 'autoservice_auth');

// Настройки безопасности
define('HASH_ALGO', 'sha256');
define('SALT', 'autoservice_db_2025_salt');

// Роли пользователей
define('ROLE_GUEST', 0);
define('ROLE_CLIENT', 1);
define('ROLE_EMPLOYEE', 2);
define('ROLE_ADMIN', 3);

// Статусы заказов
define('STATUS_NEW', 1);
define('STATUS_IN_PROGRESS', 2);
define('STATUS_COMPLETED', 3);
define('STATUS_CANCELLED', 4);

// Настройки email
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@autoservice-it.ru');
define('FROM_NAME', 'АвтоСервис ИТ');

// Пути к файлам
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');
define('LOG_PATH', ROOT_PATH . '/logs/');

// Настройки отладки
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);

/**
 * Класс для работы с базой данных
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Ошибка подключения к базе данных: " . $e->getMessage());
            } else {
                die("Ошибка подключения к базе данных");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Выполнение подготовленного запроса
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception("Ошибка выполнения запроса: " . $e->getMessage());
            } else {
                throw new Exception("Ошибка выполнения запроса");
            }
        }
    }
    
    /**
     * Получение одной записи
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Получение всех записей
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Получение ID последней вставленной записи
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Начало транзакции
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Подтверждение транзакции
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Откат транзакции
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    /**
     * Вставка данных в таблицу
     */
    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($data);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception("Ошибка вставки данных: " . $e->getMessage());
            } else {
                throw new Exception("Ошибка вставки данных");
            }
        }
    }
    
    /**
     * Обновление данных в таблице
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "{$key} = :{$key}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $params = array_merge($data, $whereParams);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new Exception("Ошибка обновления данных: " . $e->getMessage());
            } else {
                throw new Exception("Ошибка обновления данных");
            }
        }
    }
}

/**
 * Функция для логирования ошибок
 */
function logError($message, $file = 'error.log') {
    if (LOG_ERRORS) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] $message" . PHP_EOL;
        
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        file_put_contents(LOG_PATH . $file, $logMessage, FILE_APPEND | LOCK_EX);
    }
}

/**
 * Функция для безопасного хеширования паролей
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Функция для проверки пароля
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Функция для генерации случайного токена
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Функция для очистки входных данных
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Функция для проверки AJAX запросов
 */
function isAjaxRequest() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Функция для отправки JSON ответа
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Функция для проверки прав доступа
 */
function checkPermission($requiredRole) {
    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        return false;
    }
    
    return $_SESSION['user_role'] >= $requiredRole;
}

/**
 * Функция для редиректа
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

// Установка обработчиков ошибок
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $message = "Error [$errno]: $errstr in $errfile on line $errline";
    logError($message);
    
    if (DEBUG_MODE) {
        echo $message;
    }
});

set_exception_handler(function($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . 
               " in " . $exception->getFile() . 
               " on line " . $exception->getLine();
    logError($message);
    
    if (DEBUG_MODE) {
        echo $message;
    }
});

// Настройка часового пояса
date_default_timezone_set('Europe/Moscow');

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>