<?php
session_start();
require_once 'backend/config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    http_response_code(403);
    echo '<div class="alert alert-danger">Доступ запрещен</div>';
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$page = $_GET['page'] ?? 'overview';

// Получаем роль пользователя для админа
$user_role = null;
if ($user_type === 'employee') {
    $stmt = $pdo->prepare("SELECT role FROM Employees WHERE employee_id = ?");
    $stmt->execute([$user_id]);
    $user_role = $stmt->fetchColumn();
}

// Проверяем права доступа к странице
function checkAccess($page, $user_type, $user_role) {
    $client_pages = ['overview', 'cart', 'orders', 'cars', 'profile'];
    $employee_pages = ['overview', 'orders-manage', 'clients', 'schedule', 'profile'];
    $admin_pages = ['dashboard', 'orders-manage', 'clients', 'employees', 'services', 'analytics', 'settings'];
    
    if ($user_type === 'client' && in_array($page, $client_pages)) {
        return true;
    } elseif ($user_type === 'employee' && $user_role === 'admin' && in_array($page, $admin_pages)) {
        return true;
    } elseif ($user_type === 'employee' && $user_role !== 'admin' && in_array($page, $employee_pages)) {
        return true;
    }
    
    return false;
}

if (!checkAccess($page, $user_type, $user_role)) {
    echo '<div class="alert alert-danger">Доступ к этой странице запрещен</div>';
    exit();
}

// Загружаем контент в зависимости от страницы
switch ($page) {
    case 'overview':
        include 'dashboard_pages/overview.php';
        break;
    case 'dashboard':
        include 'dashboard_pages/dashboard.php';
        break;
    case 'cart':
        include 'dashboard_pages/cart.php';
        break;
    case 'orders':
        include 'dashboard_pages/orders.php';
        break;
    case 'orders-manage':
        include 'dashboard_pages/orders_manage.php';
        break;
    case 'cars':
        include 'dashboard_pages/cars.php';
        break;
    case 'clients':
        include 'dashboard_pages/clients.php';
        break;
    case 'employees':
        include 'dashboard_pages/employees.php';
        break;
    case 'services':
        include 'dashboard_pages/services.php';
        break;
    case 'analytics':
        include 'dashboard_pages/analytics.php';
        break;
    case 'settings':
        include 'dashboard_pages/settings.php';
        break;
    case 'profile':
        include 'dashboard_pages/profile.php';
        break;
    case 'schedule':
        include 'dashboard_pages/schedule.php';
        break;
    default:
        echo '<div class="alert alert-warning">Страница не найдена</div>';
        break;
}
?>