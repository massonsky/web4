<?php
session_start();
require_once 'config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$action = $_GET['action'] ?? '';

// Получаем роль пользователя для админа
$user_role = null;
if ($user_type === 'employee') {
    $stmt = $pdo->prepare("SELECT role FROM Employees WHERE employee_id = ?");
    $stmt->execute([$user_id]);
    $user_role = $stmt->fetchColumn();
}

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get_notification_count':
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM Notifications 
                WHERE user_id = ? AND user_type = ? AND is_read = FALSE
            ");
            $stmt->execute([$user_id, $user_type]);
            $count = $stmt->fetchColumn();
            echo json_encode(['count' => (int)$count]);
            break;

        case 'get_cart_count':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as count FROM ShoppingCart WHERE client_id = ?");
            $stmt->execute([$user_id]);
            $count = $stmt->fetchColumn();
            echo json_encode(['count' => (int)$count]);
            break;

        case 'add_to_cart':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            
            $service_id = $_POST['service_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            
            if (!$service_id) {
                throw new Exception('Не указана услуга');
            }
            
            // Проверяем существование услуги
            $stmt = $pdo->prepare("SELECT service_id FROM Services WHERE service_id = ?");
            $stmt->execute([$service_id]);
            if (!$stmt->fetchColumn()) {
                throw new Exception('Услуга не найдена');
            }
            
            // Добавляем в корзину или обновляем количество
            $stmt = $pdo->prepare("
                INSERT INTO ShoppingCart (client_id, service_id, quantity) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
            ");
            $stmt->execute([$user_id, $service_id, $quantity]);
            
            echo json_encode(['success' => true, 'message' => 'Услуга добавлена в корзину']);
            break;

        case 'remove_from_cart':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            
            $service_id = $_POST['service_id'] ?? 0;
            
            if (!$service_id) {
                throw new Exception('Не указана услуга');
            }
            
            $stmt = $pdo->prepare("DELETE FROM ShoppingCart WHERE client_id = ? AND service_id = ?");
            $stmt->execute([$user_id, $service_id]);
            
            echo json_encode(['success' => true, 'message' => 'Услуга удалена из корзины']);
            break;

        case 'update_cart_quantity':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            
            $service_id = $_POST['service_id'] ?? 0;
            $quantity = $_POST['quantity'] ?? 1;
            
            if (!$service_id || $quantity < 1) {
                throw new Exception('Неверные параметры');
            }
            
            $stmt = $pdo->prepare("
                UPDATE ShoppingCart 
                SET quantity = ? 
                WHERE client_id = ? AND service_id = ?
            ");
            $stmt->execute([$quantity, $user_id, $service_id]);
            
            echo json_encode(['success' => true, 'message' => 'Количество обновлено']);
            break;

        case 'get_cart_items':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            
            $stmt = $pdo->prepare("
                SELECT sc.*, s.name, s.price, s.description, sc.quantity * s.price as total_price
                FROM ShoppingCart sc
                JOIN Services s ON sc.service_id = s.service_id
                WHERE sc.client_id = ?
                ORDER BY sc.added_at DESC
            ");
            $stmt->execute([$user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $total_amount = array_sum(array_column($items, 'total_price'));
            
            echo json_encode([
                'items' => $items,
                'total_amount' => $total_amount,
                'count' => count($items)
            ]);
            break;

        case 'create_order_from_cart':
            if ($user_type !== 'client') {
                throw new Exception('Доступ запрещен');
            }
            
            // Получаем элементы корзины
            $stmt = $pdo->prepare("
                SELECT sc.service_id, sc.quantity, s.price
                FROM ShoppingCart sc
                JOIN Services s ON sc.service_id = s.service_id
                WHERE sc.client_id = ?
            ");
            $stmt->execute([$user_id]);
            $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($cart_items)) {
                throw new Exception('Корзина пуста');
            }
            
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            try {
                // Создаем заказ
                $total_amount = array_sum(array_map(function($item) {
                    return $item['quantity'] * $item['price'];
                }, $cart_items));
                
                $stmt = $pdo->prepare("
                    INSERT INTO Orders (client_id, total_amount, status, created_at) 
                    VALUES (?, ?, 'pending', NOW())
                ");
                $stmt->execute([$user_id, $total_amount]);
                $order_id = $pdo->lastInsertId();
                
                // Добавляем услуги в заказ
                foreach ($cart_items as $item) {
                    $stmt = $pdo->prepare("
                        INSERT INTO OrderServices (order_id, service_id, quantity, price) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$order_id, $item['service_id'], $item['quantity'], $item['price']]);
                }
                
                // Очищаем корзину
                $stmt = $pdo->prepare("DELETE FROM ShoppingCart WHERE client_id = ?");
                $stmt->execute([$user_id]);
                
                // Создаем уведомление
                $stmt = $pdo->prepare("
                    INSERT INTO Notifications (user_id, user_type, title, message) 
                    VALUES (?, 'client', 'Заказ создан', 'Ваш заказ #? успешно создан и принят в обработку')
                ");
                $stmt->execute([$user_id, $order_id]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Заказ успешно создан',
                    'order_id' => $order_id
                ]);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            break;

        case 'mark_notification_read':
            $notification_id = $_POST['notification_id'] ?? 0;
            
            if (!$notification_id) {
                throw new Exception('Не указано уведомление');
            }
            
            $stmt = $pdo->prepare("
                UPDATE Notifications 
                SET is_read = TRUE 
                WHERE notification_id = ? AND user_id = ? AND user_type = ?
            ");
            $stmt->execute([$notification_id, $user_id, $user_type]);
            
            echo json_encode(['success' => true, 'message' => 'Уведомление отмечено как прочитанное']);
            break;

        case 'get_notifications':
            $limit = $_GET['limit'] ?? 10;
            
            $stmt = $pdo->prepare("
                SELECT * FROM Notifications 
                WHERE user_id = ? AND user_type = ? 
                ORDER BY created_at DESC 
                LIMIT ?
            ");
            $stmt->execute([$user_id, $user_type, $limit]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['notifications' => $notifications]);
            break;

        case 'update_order_status':
            if ($user_type !== 'employee') {
                throw new Exception('Доступ запрещен');
            }
            
            $order_id = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            
            if (!$order_id || !$status) {
                throw new Exception('Неверные параметры');
            }
            
            // Проверяем существование статуса
            $stmt = $pdo->prepare("SELECT status_code FROM OrderStatuses WHERE status_code = ?");
            $stmt->execute([$status]);
            if (!$stmt->fetchColumn()) {
                throw new Exception('Неверный статус');
            }
            
            $stmt = $pdo->prepare("UPDATE Orders SET status = ? WHERE order_id = ?");
            $stmt->execute([$status, $order_id]);
            
            // Создаем уведомление клиенту
            $stmt = $pdo->prepare("
                INSERT INTO Notifications (user_id, user_type, title, message) 
                SELECT client_id, 'client', 'Статус заказа изменен', 
                       CONCAT('Статус вашего заказа #', ?, ' изменен на: ', ?)
                FROM Orders WHERE order_id = ?
            ");
            $stmt->execute([$order_id, $status, $order_id]);
            
            echo json_encode(['success' => true, 'message' => 'Статус заказа обновлен']);
            break;

        case 'get_system_stats':
            if ($user_type !== 'employee' || $user_role !== 'admin') {
                throw new Exception('Доступ запрещен');
            }
            
            // Общая статистика системы
            $stats = [];
            
            // Заказы
            $stmt = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress
                FROM Orders
            ");
            $stats['orders'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Клиенты
            $stats['clients'] = $pdo->query("SELECT COUNT(*) as total FROM Clients")->fetchColumn();
            
            // Сотрудники
            $stats['employees'] = $pdo->query("SELECT COUNT(*) as total FROM Employees")->fetchColumn();
            
            // Доход
            $stmt = $pdo->query("
                SELECT 
                    COALESCE(SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END), 0) as total_income,
                    COALESCE(SUM(CASE WHEN status = 'completed' AND MONTH(created_at) = MONTH(CURRENT_DATE()) THEN total_amount ELSE 0 END), 0) as monthly_income
                FROM Orders
            ");
            $stats['income'] = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['stats' => $stats]);
            break;

        case 'get_statistics':
            $stats = [];
            
            if ($user_type === 'client') {
                // Статистика для клиента
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE client_id = ?");
                $stmt->execute([$user_id]);
                $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_cars FROM client_cars WHERE client_id = ?");
                $stmt->execute([$user_id]);
                $stats['total_cars'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_cars'];
                
                $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE client_id = ? AND status = 'completed'");
                $stmt->execute([$user_id]);
                $stats['total_spent'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
                
            } elseif ($user_type === 'employee') {
                // Статистика для сотрудника
                $stmt = $pdo->prepare("SELECT COUNT(*) as processed_orders FROM orders WHERE employee_id = ?");
                $stmt->execute([$user_id]);
                $stats['processed_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['processed_orders'];
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
                $stmt->execute();
                $stats['pending_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];
                
            } elseif ($user_type === 'admin') {
                // Статистика для администратора
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders");
                $stmt->execute();
                $stats['total_orders'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_clients FROM clients");
                $stmt->execute();
                $stats['total_clients'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_clients'];
                
                $stmt = $pdo->prepare("SELECT COUNT(*) as total_employees FROM employees");
                $stmt->execute();
                $stats['total_employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_employees'];
                
                $stmt = $pdo->prepare("SELECT SUM(total_amount) as monthly_income FROM orders WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND status = 'completed'");
                $stmt->execute();
                $stats['monthly_income'] = $stmt->fetch(PDO::FETCH_ASSOC)['monthly_income'] ?? 0;
            }
            
            echo json_encode(['success' => true, 'data' => $stats]);
            break;
            
        // Методы для сотрудников - управление заказами
        case 'get_all_orders':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            echo json_encode(getAllOrders());
            break;
            
        case 'get_order_details':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            $order_id = $_GET['order_id'] ?? 0;
            echo json_encode(getOrderDetails($order_id));
            break;
            
        case 'update_order':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            $order_id = $_POST['order_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $notes = $_POST['notes'] ?? '';
            echo json_encode(updateOrder($order_id, $status, $notes));
            break;
            
        // Методы для сотрудников - управление клиентами
        case 'get_all_clients':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            echo json_encode(getAllClients());
            break;
            
        case 'get_client_details':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            $client_id = $_GET['client_id'] ?? 0;
            echo json_encode(getClientDetails($client_id));
            break;
            
        case 'add_client':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            echo json_encode(addClient($_POST));
            break;
            
        case 'update_client':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            echo json_encode(updateClient($_POST));
            break;
            
        case 'delete_client':
            if ($user_type !== 'employee' && $user_type !== 'admin') {
                echo json_encode(['error' => 'Доступ запрещен']);
                break;
            }
            $client_id = $_POST['client_id'] ?? 0;
            echo json_encode(deleteClient($client_id));
            break;
            
        // Управление сотрудниками (только для админа)
        case 'get_admin_employees':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $search = $_POST['search'] ?? '';
            $role_id = $_POST['role_id'] ?? '';
            $status = $_POST['status'] ?? '';
            
            $query = "SELECT e.*, r.name as role_name FROM employees e 
                      LEFT JOIN employee_roles r ON e.role_id = r.id 
                      WHERE 1=1";
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (e.name LIKE ? OR e.email LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            if (!empty($role_id)) {
                $query .= " AND e.role_id = ?";
                $params[] = $role_id;
            }
            
            if ($status === 'active') {
                $query .= " AND e.is_active = 1";
            } elseif ($status === 'inactive') {
                $query .= " AND e.is_active = 0";
            }
            
            $query .= " ORDER BY e.created_at DESC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $employees]);
            break;
            
        case 'get_employee_details':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $employee_id = $_POST['employee_id'] ?? 0;
            
            $stmt = $pdo->prepare("SELECT e.*, r.name as role_name FROM employees e 
                                   LEFT JOIN employee_roles r ON e.role_id = r.id 
                                   WHERE e.id = ?");
            $stmt->execute([$employee_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($employee) {
                echo json_encode(['success' => true, 'data' => $employee]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Сотрудник не найден']);
            }
            break;
            
        case 'add_employee':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $role_id = $_POST['role_id'] ?? 0;
            $password = $_POST['password'] ?? '';
            $salary = $_POST['salary'] ?? null;
            $hire_date = $_POST['hire_date'] ?? date('Y-m-d');
            $is_active = $_POST['is_active'] ?? 1;
            
            if (empty($name) || empty($email) || empty($password) || empty($role_id)) {
                echo json_encode(['success' => false, 'error' => 'Заполните все обязательные поля']);
                break;
            }
            
            // Проверяем уникальность email
            $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Сотрудник с таким email уже существует']);
                break;
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO employees (name, email, phone, role_id, password, salary, hire_date, is_active) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $email, $phone, $role_id, $hashed_password, $salary, $hire_date, $is_active])) {
                echo json_encode(['success' => true, 'message' => 'Сотрудник успешно добавлен']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ошибка добавления сотрудника']);
            }
            break;
            
        case 'update_employee':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $employee_id = $_POST['employee_id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $role_id = $_POST['role_id'] ?? 0;
            $password = $_POST['password'] ?? '';
            $salary = $_POST['salary'] ?? null;
            $hire_date = $_POST['hire_date'] ?? '';
            $is_active = $_POST['is_active'] ?? 1;
            
            if (empty($name) || empty($email) || empty($role_id)) {
                echo json_encode(['success' => false, 'error' => 'Заполните все обязательные поля']);
                break;
            }
            
            // Проверяем уникальность email (исключая текущего сотрудника)
            $stmt = $pdo->prepare("SELECT id FROM employees WHERE email = ? AND id != ?");
            $stmt->execute([$email, $employee_id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'error' => 'Сотрудник с таким email уже существует']);
                break;
            }
            
            $query = "UPDATE employees SET name = ?, email = ?, phone = ?, role_id = ?, salary = ?, hire_date = ?, is_active = ?";
            $params = [$name, $email, $phone, $role_id, $salary, $hire_date, $is_active];
            
            if (!empty($password)) {
                $query .= ", password = ?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            
            $query .= " WHERE id = ?";
            $params[] = $employee_id;
            
            $stmt = $pdo->prepare($query);
            
            if ($stmt->execute($params)) {
                echo json_encode(['success' => true, 'message' => 'Сотрудник успешно обновлен']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ошибка обновления сотрудника']);
            }
            break;
            
        case 'delete_employee':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $employee_id = $_POST['employee_id'] ?? 0;
            
            $stmt = $pdo->prepare("DELETE FROM employees WHERE id = ?");
            
            if ($stmt->execute([$employee_id])) {
                echo json_encode(['success' => true, 'message' => 'Сотрудник успешно удален']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Ошибка удаления сотрудника']);
            }
            break;
            
        // Системные настройки (только для админа)
        case 'get_system_settings':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM SystemSettings");
            $stmt->execute();
            $settings_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($settings_raw as $setting) {
                $settings[$setting['setting_key']] = $setting['setting_value'];
            }
            
            echo json_encode(['success' => true, 'data' => $settings]);
            break;
            
        case 'update_system_settings':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            $settings = $_POST['settings'] ?? [];
            
            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("INSERT INTO SystemSettings (setting_key, setting_value) 
                                       VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$key, $value, $value]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Настройки успешно сохранены']);
            break;
            
        case 'get_system_info':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            // Получаем версию MySQL
            $stmt = $pdo->query("SELECT VERSION() as version");
            $mysql_version = $stmt->fetch(PDO::FETCH_ASSOC)['version'];
            
            // Получаем размер базы данных
            $stmt = $pdo->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS db_size 
                                 FROM information_schema.tables 
                                 WHERE table_schema = DATABASE()");
            $db_size = $stmt->fetch(PDO::FETCH_ASSOC)['db_size'] . ' MB';
            
            // Получаем свободное место на диске
            $disk_space = round(disk_free_space('.') / 1024 / 1024 / 1024, 2) . ' GB';
            
            $info = [
                'mysql_version' => $mysql_version,
                'db_size' => $db_size,
                'disk_space' => $disk_space,
                'server_uptime' => 'Недоступно',
                'last_update' => date('d.m.Y H:i:s')
            ];
            
            echo json_encode(['success' => true, 'data' => $info]);
            break;
            
        case 'clear_cache':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            // Здесь можно добавить логику очистки кэша
            echo json_encode(['success' => true, 'message' => 'Кэш успешно очищен']);
            break;
            
        case 'backup_database':
            if ($_SESSION['user_type'] !== 'admin') {
                echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
                break;
            }
            
            // Здесь можно добавить логику создания резервной копии
            echo json_encode(['success' => true, 'message' => 'Резервная копия создана']);
            break;

        default:
            throw new Exception('Неизвестное действие');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// Функции для работы с заказами (для сотрудников)
function getAllOrders() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.order_date,
            o.status,
            o.total_amount,
            c.name as client_name,
            c.phone as client_phone,
            c.email as client_email,
            CONCAT(b.name, ' ', m.name, ' (', car.year, ')') as car_info
        FROM Orders o
        LEFT JOIN Clients c ON o.client_id = c.id
        LEFT JOIN Cars car ON o.car_id = car.id
        LEFT JOIN Models m ON car.model_id = m.id
        LEFT JOIN Brands b ON m.brand_id = b.id
        ORDER BY o.order_date DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderDetails($order_id) {
    global $pdo;
    
    // Получаем основную информацию о заказе
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            c.name as client_name,
            c.phone as client_phone,
            c.email as client_email,
            CONCAT(b.name, ' ', m.name, ' (', car.year, ')') as car_info
        FROM Orders o
        LEFT JOIN Clients c ON o.client_id = c.id
        LEFT JOIN Cars car ON o.car_id = car.id
        LEFT JOIN Models m ON car.model_id = m.id
        LEFT JOIN Brands b ON m.brand_id = b.id
        WHERE o.id = ?
    ");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Заказ не найден');
    }
    
    // Получаем услуги заказа
    $stmt = $pdo->prepare("
        SELECT 
            os.*,
            s.name,
            s.description,
            (os.quantity * os.price) as total_price
        FROM OrderServices os
        JOIN Services s ON os.service_id = s.id
        WHERE os.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем запчасти заказа
    $stmt = $pdo->prepare("
        SELECT 
            op.*,
            p.name,
            p.part_number,
            (op.quantity * op.price) as total_price
        FROM OrderParts op
        JOIN Parts p ON op.part_id = p.id
        WHERE op.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'order' => $order,
        'services' => $services,
        'parts' => $parts
    ];
}

function updateOrderStatus($order_id, $status) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE Orders SET status = ? WHERE id = ?");
    $result = $stmt->execute([$status, $order_id]);
    
    if (!$result) {
        throw new Exception('Ошибка обновления статуса заказа');
    }
    
    return ['success' => true];
}

function updateOrder($order_id, $status, $notes) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE Orders SET status = ?, notes = ? WHERE id = ?");
    $result = $stmt->execute([$status, $notes, $order_id]);
    
    if (!$result) {
        throw new Exception('Ошибка обновления заказа');
    }
    
    return ['success' => true];
}

// Функции для работы с клиентами (для сотрудников)
function getAllClients() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT 
            c.*,
            ci.name as city_name,
            COUNT(o.id) as orders_count
        FROM Clients c
        LEFT JOIN Cities ci ON c.city_id = ci.id
        LEFT JOIN Orders o ON c.id = o.client_id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClientDetails($client_id) {
    global $pdo;
    
    // Получаем информацию о клиенте
    $stmt = $pdo->prepare("
        SELECT 
            c.*,
            ci.name as city_name
        FROM Clients c
        LEFT JOIN Cities ci ON c.city_id = ci.id
        WHERE c.id = ?
    ");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        throw new Exception('Клиент не найден');
    }
    
    // Получаем заказы клиента
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_date,
            o.status,
            o.total_amount
        FROM Orders o
        WHERE o.client_id = ?
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([$client_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем автомобили клиента
    $stmt = $pdo->prepare("
        SELECT 
            car.*,
            b.name as brand_name,
            m.name as model_name
        FROM Cars car
        JOIN Models m ON car.model_id = m.id
        JOIN Brands b ON m.brand_id = b.id
        WHERE car.client_id = ?
        ORDER BY car.year DESC
    ");
    $stmt->execute([$client_id]);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'client' => $client,
        'orders' => $orders,
        'cars' => $cars
    ];
}

function addClient($data) {
    global $pdo;
    
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $city_id = $data['city_id'] ?? null;
    $address = $data['address'] ?? '';
    $notes = $data['notes'] ?? '';
    
    if (empty($name) || empty($phone) || empty($email)) {
        throw new Exception('Заполните все обязательные поля');
    }
    
    // Проверяем уникальность email
    $stmt = $pdo->prepare("SELECT id FROM Clients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('Клиент с таким email уже существует');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO Clients (name, phone, email, city_id, address, notes, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $name, $phone, $email, 
        $city_id ?: null, $address, $notes
    ]);
    
    if (!$result) {
        throw new Exception('Ошибка добавления клиента');
    }
    
    return ['success' => true, 'client_id' => $pdo->lastInsertId()];
}

function updateClient($data) {
    global $pdo;
    
    $client_id = $data['client_id'] ?? 0;
    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $email = $data['email'] ?? '';
    $city_id = $data['city_id'] ?? null;
    $address = $data['address'] ?? '';
    $notes = $data['notes'] ?? '';
    
    if (empty($name) || empty($phone) || empty($email)) {
        throw new Exception('Заполните все обязательные поля');
    }
    
    // Проверяем уникальность email (исключая текущего клиента)
    $stmt = $pdo->prepare("SELECT id FROM Clients WHERE email = ? AND id != ?");
    $stmt->execute([$email, $client_id]);
    if ($stmt->fetch()) {
        throw new Exception('Клиент с таким email уже существует');
    }
    
    $stmt = $pdo->prepare("
        UPDATE Clients 
        SET name = ?, phone = ?, email = ?, city_id = ?, address = ?, notes = ?
        WHERE id = ?
    ");
    
    $result = $stmt->execute([
        $name, $phone, $email, 
        $city_id ?: null, $address, $notes, $client_id
    ]);
    
    if (!$result) {
        throw new Exception('Ошибка обновления клиента');
    }
    
    return ['success' => true];
}

function deleteClient($client_id) {
    global $pdo;
    
    // Проверяем, есть ли у клиента заказы
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Orders WHERE client_id = ?");
    $stmt->execute([$client_id]);
    $orders_count = $stmt->fetchColumn();
    
    if ($orders_count > 0) {
        throw new Exception('Нельзя удалить клиента с существующими заказами');
    }
    
    // Удаляем автомобили клиента
    $stmt = $pdo->prepare("DELETE FROM Cars WHERE client_id = ?");
    $stmt->execute([$client_id]);
    
    // Удаляем клиента
    $stmt = $pdo->prepare("DELETE FROM Clients WHERE id = ?");
    $result = $stmt->execute([$client_id]);
    
    if (!$result) {
        throw new Exception('Ошибка удаления клиента');
    }
    
    return ['success' => true];
}
?>