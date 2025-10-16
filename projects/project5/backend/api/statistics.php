<?php
require_once '../config.php';
require_once '../auth.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Получаем статистику
    $stats = [];
    
    // Общее количество услуг
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Services");
    $stats['total_services'] = $stmt->fetchColumn();
    
    // Общее количество выполненных заказов (статус "Завершен")
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Orders WHERE status_id = 3");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    // Общее количество клиентов
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Clients");
    $stats['total_clients'] = $stmt->fetchColumn();
    
    // Дополнительная статистика для админов
    $auth = new Auth();
    $user = $auth->getCurrentUser();
    
    if ($user && $user['role_id'] >= ROLE_ADMIN) {
        // Общая сумма заказов
        $stmt = $pdo->query("SELECT COALESCE(SUM(total_cost), 0) as total FROM Orders WHERE status_id = 3");
        $stats['total_revenue'] = $stmt->fetchColumn();
        
        // Количество активных заказов
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Orders WHERE status_id IN (1, 2)");
        $stats['active_orders'] = $stmt->fetchColumn();
        
        // Количество сотрудников
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM Employees");
        $stats['total_employees'] = $stmt->fetchColumn();
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка получения статистики: ' . $e->getMessage()
    ]);
}
?>