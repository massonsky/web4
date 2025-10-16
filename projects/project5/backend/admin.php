<?php
require_once 'config.php';
require_once 'auth.php';

/**
 * Класс для административных операций
 */
class Admin {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
    }
    
    /**
     * Проверка прав администратора
     */
    private function checkAdminPermission() {
        if (!$this->auth->hasPermission(ROLE_ADMIN)) {
            sendJsonResponse(['success' => false, 'error' => 'Недостаточно прав доступа'], 403);
            exit;
        }
    }
    
    /**
     * Получение всех клиентов
     */
    public function getClients($page = 1, $limit = 20, $search = '') {
        $this->checkAdminPermission();
        
        $offset = ($page - 1) * $limit;
        $searchCondition = '';
        $params = [];
        
        if (!empty($search)) {
            $searchCondition = "WHERE CONCAT(c.first_name, ' ', c.last_name, ' ', c.email, ' ', c.phone) LIKE ?";
            $params[] = "%$search%";
        }
        
        $sql = "SELECT c.*, ci.city_name,
                       COUNT(o.order_id) as total_orders,
                       COALESCE(SUM(o.total_cost), 0) as total_spent
                FROM Clients c
                LEFT JOIN Cities ci ON c.city_id = ci.city_id
                LEFT JOIN Orders o ON c.client_id = o.client_id
                $searchCondition
                GROUP BY c.client_id
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $clients = $this->db->fetchAll($sql, $params);
        
        // Получаем общее количество для пагинации
        $countSql = "SELECT COUNT(DISTINCT c.client_id) as total FROM Clients c $searchCondition";
        $countParams = !empty($search) ? ["%$search%"] : [];
        $total = $this->db->fetchOne($countSql, $countParams)['total'];
        
        return [
            'success' => true,
            'data' => $clients,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }
    
    /**
     * Получение всех сотрудников
     */
    public function getEmployees() {
        $this->checkAdminPermission();
        
        $sql = "SELECT e.*, er.role_name, er.description as role_description
                FROM Employees e
                JOIN EmployeeRoles er ON e.role_id = er.role_id
                ORDER BY e.hire_date DESC";
        
        $employees = $this->db->fetchAll($sql);
        
        return ['success' => true, 'data' => $employees];
    }
    
    /**
     * Получение всех заказов
     */
    public function getOrders($page = 1, $limit = 20, $status = '', $search = '') {
        $this->checkAdminPermission();
        
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];
        
        if (!empty($status)) {
            $conditions[] = "o.status_id = ?";
            $params[] = $status;
        }
        
        if (!empty($search)) {
            $conditions[] = "(CONCAT(c.first_name, ' ', c.last_name) LIKE ? OR o.order_id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT o.*, 
                       CONCAT(c.first_name, ' ', c.last_name) as client_name,
                       c.phone as client_phone,
                       os.status_name,
                       CONCAT(e.first_name, ' ', e.last_name) as employee_name
                FROM Orders o
                JOIN Clients c ON o.client_id = c.client_id
                JOIN OrderStatuses os ON o.status_id = os.status_id
                LEFT JOIN Employees e ON o.employee_id = e.employee_id
                $whereClause
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $orders = $this->db->fetchAll($sql, $params);
        
        // Получаем общее количество
        $countSql = "SELECT COUNT(*) as total FROM Orders o 
                     JOIN Clients c ON o.client_id = c.client_id 
                     $whereClause";
        $countParams = array_slice($params, 0, -2); // Убираем limit и offset
        $total = $this->db->fetchOne($countSql, $countParams)['total'];
        
        return [
            'success' => true,
            'data' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($total / $limit),
                'total_items' => $total,
                'items_per_page' => $limit
            ]
        ];
    }
    
    /**
     * Создание нового сотрудника
     */
    public function createEmployee($data) {
        $this->checkAdminPermission();
        
        try {
            // Валидация
            $errors = $this->validateEmployeeData($data);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            // Проверяем уникальность email
            $existing = $this->db->fetchOne(
                "SELECT employee_id FROM Employees WHERE email = ?",
                [$data['email']]
            );
            
            if ($existing) {
                return ['success' => false, 'errors' => ['email' => 'Сотрудник с таким email уже существует']];
            }
            
            $hashedPassword = hashPassword($data['password']);
            
            $sql = "INSERT INTO Employees (first_name, last_name, email, phone, password, role_id, hire_date, salary, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->db->query($sql, [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $hashedPassword,
                $data['role_id'],
                $data['hire_date'],
                $data['salary']
            ]);
            
            return ['success' => true, 'message' => 'Сотрудник успешно создан'];
            
        } catch (Exception $e) {
            logError("Create employee error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Ошибка при создании сотрудника']];
        }
    }
    
    /**
     * Обновление сотрудника
     */
    public function updateEmployee($id, $data) {
        $this->checkAdminPermission();
        
        try {
            $errors = $this->validateEmployeeData($data, $id);
            if (!empty($errors)) {
                return ['success' => false, 'errors' => $errors];
            }
            
            $sql = "UPDATE Employees SET 
                    first_name = ?, last_name = ?, email = ?, phone = ?, 
                    role_id = ?, hire_date = ?, salary = ?";
            $params = [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['phone'],
                $data['role_id'],
                $data['hire_date'],
                $data['salary']
            ];
            
            // Обновляем пароль только если он указан
            if (!empty($data['password'])) {
                $sql .= ", password = ?";
                $params[] = hashPassword($data['password']);
            }
            
            $sql .= " WHERE employee_id = ?";
            $params[] = $id;
            
            $this->db->query($sql, $params);
            
            return ['success' => true, 'message' => 'Сотрудник успешно обновлен'];
            
        } catch (Exception $e) {
            logError("Update employee error: " . $e->getMessage());
            return ['success' => false, 'errors' => ['general' => 'Ошибка при обновлении сотрудника']];
        }
    }
    
    /**
     * Удаление сотрудников
     */
    public function deleteEmployees($ids) {
        $this->checkAdminPermission();
        
        try {
            if (empty($ids) || !is_array($ids)) {
                return ['success' => false, 'error' => 'Не выбраны сотрудники для удаления'];
            }
            
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "DELETE FROM Employees WHERE employee_id IN ($placeholders)";
            
            $this->db->query($sql, $ids);
            
            return ['success' => true, 'message' => 'Сотрудники успешно удалены'];
            
        } catch (Exception $e) {
            logError("Delete employees error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при удалении сотрудников'];
        }
    }
    
    /**
     * Получение статистики
     */
    public function getStatistics() {
        $this->checkAdminPermission();
        
        try {
            // Общая статистика
            $stats = [];
            
            // Количество клиентов
            $stats['total_clients'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM Clients")['count'];
            
            // Количество заказов
            $stats['total_orders'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM Orders")['count'];
            
            // Общая выручка
            $stats['total_revenue'] = $this->db->fetchOne("SELECT COALESCE(SUM(total_cost), 0) as sum FROM Orders WHERE status_id = 4")['sum'];
            
            // Количество сотрудников
            $stats['total_employees'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM Employees")['count'];
            
            // Статистика по месяцам (последние 12 месяцев)
            $monthlyStats = $this->db->fetchAll("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    COUNT(*) as orders_count,
                    COALESCE(SUM(total_cost), 0) as revenue
                FROM Orders 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                ORDER BY month
            ");
            
            // Статистика по статусам заказов
            $statusStats = $this->db->fetchAll("
                SELECT os.status_name, COUNT(*) as count
                FROM Orders o
                JOIN OrderStatuses os ON o.status_id = os.status_id
                GROUP BY o.status_id, os.status_name
            ");
            
            // Топ услуги
            $topServices = $this->db->fetchAll("
                SELECT s.service_name, COUNT(*) as orders_count, SUM(ors.quantity * ors.price) as revenue
                FROM OrderServices ors
                JOIN Services s ON ors.service_id = s.service_id
                GROUP BY s.service_id, s.service_name
                ORDER BY orders_count DESC
                LIMIT 10
            ");
            
            return [
                'success' => true,
                'data' => [
                    'general' => $stats,
                    'monthly' => $monthlyStats,
                    'statuses' => $statusStats,
                    'top_services' => $topServices
                ]
            ];
            
        } catch (Exception $e) {
            logError("Get statistics error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении статистики'];
        }
    }
    
    /**
     * Валидация данных сотрудника
     */
    private function validateEmployeeData($data, $excludeId = null) {
        $errors = [];
        
        if (empty($data['first_name'])) {
            $errors['first_name'] = 'Имя обязательно';
        }
        
        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Фамилия обязательна';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email обязателен';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Некорректный email';
        } else {
            // Проверяем уникальность email
            $sql = "SELECT employee_id FROM Employees WHERE email = ?";
            $params = [$data['email']];
            
            if ($excludeId) {
                $sql .= " AND employee_id != ?";
                $params[] = $excludeId;
            }
            
            $existing = $this->db->fetchOne($sql, $params);
            if ($existing) {
                $errors['email'] = 'Email уже используется';
            }
        }
        
        if (empty($data['role_id'])) {
            $errors['role_id'] = 'Роль обязательна';
        }
        
        if (!empty($data['password']) && strlen($data['password']) < 6) {
            $errors['password'] = 'Пароль должен содержать минимум 6 символов';
        }
        
        if (!empty($data['salary']) && !is_numeric($data['salary'])) {
            $errors['salary'] = 'Зарплата должна быть числом';
        }
        
        return $errors;
    }
}

// Обработка AJAX запросов
if (isAjaxRequest()) {
    $admin = new Admin();
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_clients':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $search = sanitizeInput($_GET['search'] ?? '');
            
            $result = $admin->getClients($page, $limit, $search);
            sendJsonResponse($result);
            break;
            
        case 'get_employees':
            $result = $admin->getEmployees();
            sendJsonResponse($result);
            break;
            
        case 'get_orders':
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 20);
            $status = sanitizeInput($_GET['status'] ?? '');
            $search = sanitizeInput($_GET['search'] ?? '');
            
            $result = $admin->getOrders($page, $limit, $status, $search);
            sendJsonResponse($result);
            break;
            
        case 'create_employee':
            $data = [
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role_id' => (int)($_POST['role_id'] ?? 0),
                'hire_date' => $_POST['hire_date'] ?? date('Y-m-d'),
                'salary' => (float)($_POST['salary'] ?? 0)
            ];
            
            $result = $admin->createEmployee($data);
            sendJsonResponse($result);
            break;
            
        case 'update_employee':
            $id = (int)($_POST['employee_id'] ?? 0);
            $data = [
                'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
                'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'role_id' => (int)($_POST['role_id'] ?? 0),
                'hire_date' => $_POST['hire_date'] ?? '',
                'salary' => (float)($_POST['salary'] ?? 0)
            ];
            
            $result = $admin->updateEmployee($id, $data);
            sendJsonResponse($result);
            break;
            
        case 'delete_employees':
            $ids = $_POST['ids'] ?? [];
            if (is_string($ids)) {
                $ids = json_decode($ids, true);
            }
            
            $result = $admin->deleteEmployees($ids);
            sendJsonResponse($result);
            break;
            
        case 'get_statistics':
            $result = $admin->getStatistics();
            sendJsonResponse($result);
            break;
            
        default:
            sendJsonResponse(['success' => false, 'error' => 'Неизвестное действие'], 400);
    }
}
?>