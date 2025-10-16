<?php
// Получаем статистику в зависимости от типа пользователя
if ($user_type === 'client') {
    // Статистика для клиента
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_orders,
            COALESCE(SUM(CASE WHEN status IN ('pending', 'in_progress') THEN 1 ELSE 0 END), 0) as active_orders
        FROM Orders WHERE client_id = ?
    ");
    $stmt->execute([$user_id]);
    $client_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Количество автомобилей
    $stmt = $pdo->prepare("SELECT COUNT(*) as car_count FROM Cars WHERE client_id = ?");
    $stmt->execute([$user_id]);
    $car_count = $stmt->fetchColumn();
    
    // Последние заказы
    $stmt = $pdo->prepare("
        SELECT o.*, os.status_name 
        FROM Orders o 
        JOIN OrderStatuses os ON o.status = os.status_code 
        WHERE o.client_id = ? 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} elseif ($user_type === 'employee') {
    // Статистика для сотрудника/админа
    if ($user_role === 'admin') {
        // Общая статистика для админа
        $stmt = $pdo->query("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_orders,
                COALESCE(SUM(CASE WHEN status IN ('pending', 'in_progress') THEN 1 ELSE 0 END), 0) as active_orders
            FROM Orders
        ");
        $admin_stats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Количество клиентов
        $client_count = $pdo->query("SELECT COUNT(*) FROM Clients")->fetchColumn();
        
        // Количество сотрудников
        $employee_count = $pdo->query("SELECT COUNT(*) FROM Employees")->fetchColumn();
        
        // Доход за месяц
        $stmt = $pdo->query("
            SELECT COALESCE(SUM(total_amount), 0) as monthly_income 
            FROM Orders 
            WHERE status = 'completed' 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $monthly_income = $stmt->fetchColumn();
        
    } else {
        // Статистика для обычного сотрудника
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_orders,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_orders,
                COALESCE(SUM(CASE WHEN status IN ('pending', 'in_progress') THEN 1 ELSE 0 END), 0) as active_orders
            FROM Orders WHERE assigned_employee_id = ?
        ");
        $stmt->execute([$user_id]);
        $employee_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Последние заказы для сотрудника
    $stmt = $pdo->prepare("
        SELECT o.*, os.status_name, c.name as client_name 
        FROM Orders o 
        JOIN OrderStatuses os ON o.status = os.status_code 
        JOIN Clients c ON o.client_id = c.client_id
        WHERE o.assigned_employee_id = ? OR ? = 'admin'
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$user_id, $user_role]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <?php if ($user_type === 'client'): ?>
        <!-- Статистика для клиента -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $client_stats['total_orders'] ?></h3>
                    <p class="mb-0">Всего заказов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $client_stats['completed_orders'] ?></h3>
                    <p class="mb-0">Завершено</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $client_stats['active_orders'] ?></h3>
                    <p class="mb-0">В работе</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-car-front fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $car_count ?></h3>
                    <p class="mb-0">Автомобилей</p>
                </div>
            </div>
        </div>
        
    <?php elseif ($user_type === 'employee' && $user_role === 'admin'): ?>
        <!-- Статистика для администратора -->
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $admin_stats['total_orders'] ?></h3>
                    <p class="mb-0">Всего заказов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $client_count ?></h3>
                    <p class="mb-0">Клиентов</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-person-badge fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $employee_count ?></h3>
                    <p class="mb-0">Сотрудников</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= number_format($monthly_income, 0, ',', ' ') ?> ₽</h3>
                    <p class="mb-0">Доход за месяц</p>
                </div>
            </div>
        </div>
        
    <?php elseif ($user_type === 'employee'): ?>
        <!-- Статистика для сотрудника -->
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $employee_stats['total_orders'] ?></h3>
                    <p class="mb-0">Мои заказы</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $employee_stats['completed_orders'] ?></h3>
                    <p class="mb-0">Завершено</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card stats-card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock fs-1 mb-3"></i>
                    <h3 class="mb-1"><?= $employee_stats['active_orders'] ?></h3>
                    <p class="mb-0">В работе</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Последние заказы -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    <?= $user_type === 'client' ? 'Мои последние заказы' : 'Последние заказы' ?>
                </h5>
                <a href="#" class="btn btn-outline-primary btn-sm" 
                   data-tab="<?= $user_type === 'client' ? 'orders' : 'orders-manage' ?>">
                    Все заказы
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-1 mb-3"></i>
                        <p>Заказов пока нет</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>№ заказа</th>
                                    <?php if ($user_type === 'employee'): ?>
                                        <th>Клиент</th>
                                    <?php endif; ?>
                                    <th>Дата</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><strong>#<?= $order['order_id'] ?></strong></td>
                                        <?php if ($user_type === 'employee'): ?>
                                            <td><?= htmlspecialchars($order['client_name']) ?></td>
                                        <?php endif; ?>
                                        <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                        <td><?= number_format($order['total_amount'], 0, ',', ' ') ?> ₽</td>
                                        <td>
                                            <span class="badge bg-<?= 
                                                $order['status'] === 'completed' ? 'success' : 
                                                ($order['status'] === 'in_progress' ? 'warning' : 'secondary') 
                                            ?>">
                                                <?= htmlspecialchars($order['status_name']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewOrder(<?= $order['order_id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($user_type === 'client'): ?>
    <!-- Быстрые действия для клиента -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-lightning me-2"></i>
                        Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-primary w-100" onclick="loadPage('cart')">
                                <i class="bi bi-cart-plus me-2"></i>
                                Перейти в корзину
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-success w-100" onclick="window.location.href='index.php#services'">
                                <i class="bi bi-plus-circle me-2"></i>
                                Заказать услугу
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-info w-100" onclick="loadPage('cars')">
                                <i class="bi bi-car-front me-2"></i>
                                Мои автомобили
                            </button>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-warning w-100" onclick="loadPage('profile')">
                                <i class="bi bi-person-gear me-2"></i>
                                Настройки профиля
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function viewOrder(orderId) {
    // Здесь будет модальное окно с деталями заказа
    alert('Просмотр заказа #' + orderId + ' (функция в разработке)');
}
</script>