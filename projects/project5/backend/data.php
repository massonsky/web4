<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'auth.php';

/**
 * Класс для работы с данными
 */
class DataManager {
    private ?Database $db;
    private ?Auth $auth;
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
            $this->auth = new Auth();
        } catch (Exception $e) {
            logError("DataManager initialization error: " . $e->getMessage());
            // Продолжаем работу без базы данных для демонстрации
            $this->db = null;
            $this->auth = null;
        }
    }
    
    /**
     * Получение клиентов
     */
    public function getClients($page = 1, $limit = 20, $search = '') {
        if ($this->db === null) {
            return ['success' => false, 'error' => 'База данных недоступна'];
        }
        
        try {
            $offset = ($page - 1) * $limit;
            $searchCondition = '';
            $params = [];
            
            if (!empty($search)) {
                $searchCondition = "WHERE CONCAT(first_name, ' ', last_name) LIKE ? OR email LIKE ? OR phone LIKE ?";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }
            
            $sql = "SELECT client_id, first_name, last_name, email, phone, address, 
                           registration_date, is_active
                    FROM Clients 
                    {$searchCondition}
                    ORDER BY registration_date DESC
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $clients = $this->db->fetchAll($sql, $params);
            
            // Получаем общее количество
            $countSql = "SELECT COUNT(*) as total FROM Clients {$searchCondition}";
            $countParams = !empty($search) ? [$searchTerm, $searchTerm, $searchTerm] : [];
            $total = $this->db->fetchOne($countSql, $countParams)['total'];
            
            return [
                'clients' => $clients,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ];
            
        } catch (Exception $e) {
            logError("Ошибка в getClients: " . $e->getMessage());
            return [
                'clients' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'pages' => 0
            ];
        }
    }
    
    /**
     * Получение сотрудников
     */
    public function getEmployees($roleId = null) {
        try {
            $sql = "SELECT e.employee_id, e.first_name, e.last_name, e.email, e.phone,
                           e.hire_date, e.is_active, er.role_name
                    FROM Employees e
                    JOIN EmployeeRoles er ON e.role_id = er.role_id";
            
            $params = [];
            if ($roleId !== null) {
                $sql .= " WHERE e.role_id = ?";
                $params[] = $roleId;
            }
            
            $sql .= " ORDER BY e.first_name, e.last_name";
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            logError("Ошибка в getEmployees: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получение автомобилей клиента
     */
    public function getClientCars($clientId) {
        try {
            $sql = "SELECT c.car_id, c.vin, c.license_plate, c.year, c.mileage,
                           b.brand_name, m.model_name, ct.type_name
                    FROM Cars c
                    LEFT JOIN Brands b ON c.brand_id = b.brand_id
                    LEFT JOIN Models m ON c.model_id = m.model_id
                    LEFT JOIN CarTypes ct ON c.type_id = ct.type_id
                    WHERE c.client_id = ?
                    ORDER BY c.year DESC";
            
            return $this->db->fetchAll($sql, [$clientId]);
            
        } catch (Exception $e) {
            logError("Ошибка в getClientCars: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получение запчастей
     */
    public function getParts($search = '', $page = 1, $limit = 50) {
        try {
            $offset = ($page - 1) * $limit;
            $searchCondition = '';
            $params = [];
            
            if (!empty($search)) {
                $searchCondition = "WHERE part_name LIKE ? OR part_number LIKE ?";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm];
            }
            
            $sql = "SELECT part_id, part_name, part_number, price, stock_quantity, supplier
                    FROM Parts 
                    {$searchCondition}
                    ORDER BY part_name
                    LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll($sql, $params);
            
        } catch (Exception $e) {
            logError("Ошибка в getParts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Обновление статуса заказа
     */
    public function updateOrderStatus($orderId, $statusId, $notes = null) {
        try {
            $updateData = ['status_id' => $statusId];
            
            if ($notes !== null) {
                $updateData['notes'] = $notes;
            }
            
            // Если заказ завершен, устанавливаем дату завершения
            if ($statusId == STATUS_COMPLETED) {
                $updateData['actual_completion'] = date('Y-m-d H:i:s');
            }
            
            $rowsAffected = $this->db->update('orders', $updateData, 'order_id = ?', [$orderId]);
            
            return $rowsAffected > 0;
            
        } catch (Exception $e) {
            logError("Ошибка в updateOrderStatus: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Получение всех городов
     */
    public function getCities() {
        try {
            // Временные данные для демонстрации
            $cities = [
                ['city_id' => 1, 'city_name' => 'Москва'],
                ['city_id' => 2, 'city_name' => 'Санкт-Петербург'],
                ['city_id' => 3, 'city_name' => 'Новосибирск'],
                ['city_id' => 4, 'city_name' => 'Екатеринбург'],
                ['city_id' => 5, 'city_name' => 'Казань'],
                ['city_id' => 6, 'city_name' => 'Нижний Новгород']
            ];
            
            return ['success' => true, 'data' => $cities];
            
        } catch (Exception $e) {
            logError("Get cities error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении городов'];
        }
    }
    
    /**
     * Получение всех услуг с категориями
     */
    public function getServices() {
        if ($this->db === null) {
            // Временные данные для демонстрации
            $services = [
                [
                    'service_id' => 1,
                    'service_name' => 'Замена масла',
                    'category_name' => 'Техническое обслуживание',
                    'base_price' => 2500.00,
                    'duration_minutes' => 60,
                    'description' => 'Замена моторного масла и масляного фильтра'
                ],
                [
                    'service_id' => 2,
                    'service_name' => 'Диагностика двигателя',
                    'category_name' => 'Диагностика',
                    'base_price' => 1500.00,
                    'duration_minutes' => 45,
                    'description' => 'Компьютерная диагностика двигателя'
                ],
                [
                    'service_id' => 3,
                    'service_name' => 'Замена тормозных колодок',
                    'category_name' => 'Ремонт тормозной системы',
                    'base_price' => 3500.00,
                    'duration_minutes' => 90,
                    'description' => 'Замена передних или задних тормозных колодок'
                ],
                [
                    'service_id' => 4,
                    'service_name' => 'Шиномонтаж',
                    'category_name' => 'Шиномонтаж',
                    'base_price' => 1200.00,
                    'duration_minutes' => 30,
                    'description' => 'Снятие/установка колес, балансировка'
                ]
            ];
            
            return ['success' => true, 'data' => $services];
        }
        
        try {
            $sql = "SELECT s.service_id, s.service_name, sc.category_name, s.base_price, s.duration_minutes, s.description
                    FROM Services s
                    LEFT JOIN ServiceCategories sc ON s.category_id = sc.category_id
                    ORDER BY sc.category_name, s.service_name";
            
            $services = $this->db->fetchAll($sql);
            
            return ['success' => true, 'data' => $services];
            
        } catch (Exception $e) {
            logError("Get services error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении услуг'];
        }
    }
    
    /**
     * Получение категорий услуг
     */
    public function getServiceCategories() {
        if ($this->db === null) {
            $categories = [
                ['category_id' => 1, 'category_name' => 'Техническое обслуживание'],
                ['category_id' => 2, 'category_name' => 'Диагностика'],
                ['category_id' => 3, 'category_name' => 'Ремонт двигателя'],
                ['category_id' => 4, 'category_name' => 'Ремонт тормозной системы'],
                ['category_id' => 5, 'category_name' => 'Шиномонтаж']
            ];
            
            return ['success' => true, 'data' => $categories];
        }
        
        try {
            $categories = $this->db->fetchAll(
                "SELECT * FROM ServiceCategories ORDER BY category_name"
            );
            
            return ['success' => true, 'data' => $categories];
            
        } catch (Exception $e) {
            logError("Get service categories error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении категорий'];
        }
    }
    
    /**
     * Получение марок автомобилей
     */
    public function getBrands() {
        if ($this->db === null) {
            $brands = [
                ['brand_id' => 1, 'brand_name' => 'Toyota'],
                ['brand_id' => 2, 'brand_name' => 'BMW'],
                ['brand_id' => 3, 'brand_name' => 'Mercedes-Benz'],
                ['brand_id' => 4, 'brand_name' => 'Audi'],
                ['brand_id' => 5, 'brand_name' => 'Volkswagen']
            ];
            
            return ['success' => true, 'data' => $brands];
        }
        
        try {
            $types = $this->db->fetchAll(
                "SELECT * FROM Brands ORDER BY brand_name"
            );
            
            return ['success' => true, 'data' => $types];
            
        } catch (Exception $e) {
            logError("Get brands error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении марок'];
        }
    }
    
    /**
     * Получение моделей по марке
     */
    public function getModelsByBrand($brandId) {
        try {
            $models = $this->db->fetchAll(
                "SELECT * FROM Models WHERE brand_id = ? ORDER BY model_name",
                [$brandId]
            );
            
            return ['success' => true, 'data' => $models];
            
        } catch (Exception $e) {
            logError("Get models error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении моделей'];
        }
    }
    
    /**
     * Получение типов автомобилей
     */
    public function getCarTypes() {
        try {
            $types = $this->db->fetchAll(
                "SELECT * FROM CarTypes ORDER BY type_name"
            );
            
            return ['success' => true, 'data' => $types];
            
        } catch (Exception $e) {
            logError("Get car types error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении типов автомобилей'];
        }
    }
    
    /**
     * Получение статусов заказов
     */
    public function getOrderStatuses() {
        try {
            $statuses = $this->db->fetchAll(
                "SELECT * FROM OrderStatuses ORDER BY status_id"
            );
            
            return ['success' => true, 'data' => $statuses];
            
        } catch (Exception $e) {
            logError("Get order statuses error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении статусов заказов'];
        }
    }
    
    /**
     * Получение ролей сотрудников
     */
    public function getEmployeeRoles() {
        try {
            $roles = $this->db->fetchAll(
                "SELECT * FROM EmployeeRoles ORDER BY role_id"
            );
            
            return ['success' => true, 'data' => $roles];
            
        } catch (Exception $e) {
            logError("Get employee roles error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении ролей'];
        }
    }
    
    /**
     * Создание нового заказа
     */
    public function createOrder($orderData) {
        try {
            $this->db->beginTransaction();
            
            // Подготавливаем данные для вставки заказа
            $orderInsertData = [
                'car_id' => $orderData['car_id'],
                'employee_id' => $orderData['employee_id'] ?? null,
                'order_date' => date('Y-m-d H:i:s'),
                'estimated_completion' => $orderData['estimated_completion'] ?? null,
                'status_id' => $orderData['status_id'] ?? STATUS_NEW,
                'notes' => $orderData['notes'] ?? null
            ];
            
            // Создаем заказ
            $orderId = $this->db->insert('Orders', $orderInsertData);
            
            // Добавляем услуги к заказу
            if (!empty($orderData['services'])) {
                foreach ($orderData['services'] as $service) {
                    $serviceData = [
                        'order_id' => $orderId,
                        'service_id' => $service['service_id'],
                        'quantity' => $service['quantity'] ?? 1,
                        'unit_price' => $service['unit_price']
                    ];
                    $this->db->insert('OrderServices', $serviceData);
                }
            }
            
            // Добавляем запчасти к заказу (если есть)
            if (!empty($orderData['parts'])) {
                foreach ($orderData['parts'] as $part) {
                    $partData = [
                        'order_id' => $orderId,
                        'part_id' => $part['part_id'],
                        'quantity' => $part['quantity'],
                        'unit_price' => $part['unit_price']
                    ];
                    $this->db->insert('OrderParts', $partData);
                }
            }
            
            // Рассчитываем общую стоимость
            $totalCost = $this->calculateOrderTotal($orderId);
            $this->db->update('Orders', ['total_cost' => $totalCost], 'order_id = ?', [$orderId]);
            
            $this->db->commit();
            
            return [
                'order_id' => $orderId,
                'total_cost' => $totalCost
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            logError("Ошибка в createOrder: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Создание или получение автомобиля
     */
    private function createOrGetCar($carInfo, $clientId) {
        // Проверяем, есть ли уже такой автомобиль у клиента
        $existingCar = $this->db->fetchOne("
            SELECT car_id FROM Cars 
            WHERE client_id = ? AND model_id = ? AND year = ? AND license_plate = ?
        ", [
            $clientId,
            $carInfo['model_id'],
            $carInfo['year'],
            $carInfo['license_plate']
        ]);
        
        if ($existingCar) {
            return $existingCar['car_id'];
        }
        
        // Создаем новый автомобиль
        $sql = "INSERT INTO Cars (client_id, model_id, year, license_plate, color, vin, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $this->db->query($sql, [
            $clientId,
            $carInfo['model_id'],
            $carInfo['year'],
            $carInfo['license_plate'],
            $carInfo['color'] ?? null,
            $carInfo['vin'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Добавление услуг к заказу
     */
    private function addServicesToOrder($orderId, $services) {
        foreach ($services as $service) {
            $serviceData = $this->db->fetchOne(
                "SELECT service_name, price FROM Services WHERE service_id = ?",
                [$service['service_id']]
            );
            
            if ($serviceData) {
                $sql = "INSERT INTO OrderServices (order_id, service_id, quantity, price)
                        VALUES (?, ?, ?, ?)";
                
                $this->db->query($sql, [
                    $orderId,
                    $service['service_id'],
                    $service['quantity'] ?? 1,
                    $serviceData['price']
                ]);
            }
        }
    }
    
    /**
     * Расчет общей стоимости заказа
     */
    private function calculateOrderTotal($orderId) {
        try {
            // Стоимость услуг
            $servicesCost = $this->db->fetchOne(
                "SELECT COALESCE(SUM(quantity * unit_price), 0) as total 
                 FROM OrderServices WHERE order_id = ?", 
                [$orderId]
            )['total'];
            
            // Стоимость запчастей
            $partsCost = $this->db->fetchOne(
                "SELECT COALESCE(SUM(quantity * unit_price), 0) as total 
                 FROM OrderParts WHERE order_id = ?", 
                [$orderId]
            )['total'];
            
            return $servicesCost + $partsCost;
            
        } catch (Exception $e) {
            logError("Ошибка в calculateOrderTotal: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Получение заказов пользователя
     */
    public function getUserOrders($userId, $page = 1, $limit = 10) {
        try {
            $offset = ($page - 1) * $limit;
            
            $sql = "SELECT o.*, os.status_name, os.color as status_color,
                           CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                           c.license_plate, CONCAT(b.brand_name, ' ', m.model_name) as car_info
                    FROM Orders o
                    JOIN OrderStatuses os ON o.status_id = os.status_id
                    LEFT JOIN Employees e ON o.employee_id = e.employee_id
                    LEFT JOIN Cars c ON o.car_id = c.car_id
                    LEFT JOIN Models m ON c.model_id = m.model_id
                    LEFT JOIN Brands b ON m.brand_id = b.brand_id
                    WHERE o.client_id = ?
                    ORDER BY o.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $orders = $this->db->fetchAll($sql, [$userId, $limit, $offset]);
            
            // Получаем услуги для каждого заказа
            foreach ($orders as &$order) {
                $order['services'] = $this->db->fetchAll("
                    SELECT os.*, s.service_name
                    FROM OrderServices os
                    JOIN Services s ON os.service_id = s.service_id
                    WHERE os.order_id = ?
                ", [$order['order_id']]);
            }
            
            // Получаем общее количество заказов
            $total = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM Orders WHERE client_id = ?",
                [$userId]
            )['count'];
            
            return [
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total
                ]
            ];
            
        } catch (Exception $e) {
            logError("Get user orders error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при получении заказов'];
        }
    }
    
    /**
     * Валидация данных заказа
     */
    private function validateOrderData($data) {
        $errors = [];
        
        if (empty($data['description'])) {
            $errors['description'] = 'Описание заказа обязательно';
        }
        
        if (empty($data['services']) || !is_array($data['services'])) {
            $errors['services'] = 'Необходимо выбрать хотя бы одну услугу';
        }
        
        return $errors;
    }
    
    /**
     * Отправка email уведомления
     */
    public function sendEmailNotification($to, $subject, $message, $isHtml = true) {
        try {
            require_once 'email.php';
            $emailManager = new EmailManager();
            
            $result = $emailManager->sendEmail($to, $subject, $message, $isHtml);
            
            if ($result) {
                return ['success' => true, 'message' => 'Уведомление отправлено'];
            } else {
                return ['success' => false, 'error' => 'Ошибка при отправке уведомления'];
            }
            
        } catch (Exception $e) {
            logError("Send email error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Ошибка при отправке уведомления'];
        }
    }
}

// Логирование входящего запроса
logError("Request received - isAjax: " . (isAjaxRequest() ? 'true' : 'false'));
if (function_exists('getallheaders')) {
    logError("Headers: " . json_encode(getallheaders()));
} else {
    logError("Headers: getallheaders() not available");
}

if (isAjaxRequest()) {
    try {
        $action = $_GET['action'] ?? $_POST['action'] ?? '';
        // Резервный парсинг action из QUERY_STRING, если он пустой
        if ($action === '' && isset($_SERVER['QUERY_STRING'])) {
            $queryParams = [];
            parse_str($_SERVER['QUERY_STRING'], $queryParams);
            if (!empty($queryParams['action'])) {
                $action = $queryParams['action'];
            }
        }
        
        // Принудительно создаем лог файл и записываем в него
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/error.log';
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] AJAX request received: action=" . $action . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'NOT SET') . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] GET params: " . json_encode($_GET) . "\n", FILE_APPEND | LOCK_EX);
        file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] POST params: " . json_encode($_POST) . "\n", FILE_APPEND | LOCK_EX);
        
        $dataManager = new DataManager();
        
        switch ($action) {
            case 'get_cities':
                logError("Processing get_cities request");
                $result = $dataManager->getCities();
                logError("get_cities result: " . json_encode($result));
                sendJsonResponse($result);
                break;
            
            case 'get_services':
                $result = $dataManager->getServices();
                sendJsonResponse($result);
                break;
            
        case 'get_service_categories':
            $result = $dataManager->getServiceCategories();
            sendJsonResponse($result);
            break;
            
        case 'get_brands':
            $result = $dataManager->getBrands();
            sendJsonResponse($result);
            break;
            
        case 'get_models':
            $brandId = (int)($_GET['brand_id'] ?? 0);
            if ($brandId) {
                $result = $dataManager->getModelsByBrand($brandId);
            } else {
                $result = ['success' => false, 'error' => 'Не указана марка'];
            }
            sendJsonResponse($result);
            break;
            
        case 'get_car_types':
            $result = $dataManager->getCarTypes();
            sendJsonResponse($result);
            break;
            
        case 'get_order_statuses':
            $result = $dataManager->getOrderStatuses();
            sendJsonResponse($result);
            break;
            
        case 'get_employee_roles':
            $result = $dataManager->getEmployeeRoles();
            sendJsonResponse($result);
            break;
            
        case 'create_order':
            $data = [
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'services' => $_POST['services'] ?? [],
                'car_info' => $_POST['car_info'] ?? []
            ];
            
            $result = $dataManager->createOrder($data);
            sendJsonResponse($result);
            break;
            
        case 'get_user_orders':
            $auth = new Auth();
            $currentUser = $auth->getCurrentUser();
            
            if (!$currentUser) {
                sendJsonResponse(['success' => false, 'error' => 'Необходима авторизация'], 401);
                break;
            }
            
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            
            $result = $dataManager->getUserOrders($currentUser['id'], $page, $limit);
            sendJsonResponse($result);
            break;
            
        case 'send_email':
            $to = sanitizeInput($_POST['to'] ?? '');
            $subject = sanitizeInput($_POST['subject'] ?? '');
            $message = sanitizeInput($_POST['message'] ?? '');
            $isHtml = isset($_POST['is_html']);
            
            $result = $dataManager->sendEmailNotification($to, $subject, $message, $isHtml);
            sendJsonResponse($result);
            break;
            
        case 'get_employee_roles':
            $stmt = $pdo->prepare("SELECT * FROM employee_roles ORDER BY name");
            $stmt->execute();
            $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $roles]);
            break;

        default:
            logError("Unknown action: " . $action);
            $resp = ['success' => false, 'error' => 'Неизвестное действие'];
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                $resp['debug'] = [
                    'action' => $action,
                    'GET' => $_GET,
                    'POST' => $_POST,
                    'QUERY_STRING' => $_SERVER['QUERY_STRING'] ?? null,
                    'headers_available' => function_exists('getallheaders'),
                ];
            }
            sendJsonResponse($resp, 400);
    }
} catch (Exception $e) {
    logError("AJAX request error: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'error' => 'Внутренняя ошибка сервера'], 500);
}
} else {
    logError("Non-AJAX request to data.php");
    http_response_code(400);
    echo "Bad Request";
}
?>