<?php
session_start();
require_once 'backend/config.php';

// Проверяем авторизацию
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$user_name = $_SESSION['user_name'] ?? 'Пользователь';

// Получаем роль пользователя для админа
$user_role = null;
if ($user_type === 'employee') {
    $stmt = $pdo->prepare("SELECT role FROM Employees WHERE employee_id = ?");
    $stmt->execute([$user_id]);
    $user_role = $stmt->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - AutoService</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateX(5px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Боковая панель -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <!-- Информация о пользователе -->
                    <div class="text-center mb-4">
                        <div class="user-avatar mx-auto">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h6 class="mb-1"><?= htmlspecialchars($user_name) ?></h6>
                        <small class="text-light">
                            <?php
                            if ($user_type === 'client') {
                                echo 'Клиент';
                            } elseif ($user_type === 'employee') {
                                echo $user_role === 'admin' ? 'Администратор' : 'Сотрудник';
                            }
                            ?>
                        </small>
                    </div>

                    <!-- Навигационное меню -->
                    <nav class="nav flex-column">
                        <?php if ($user_type === 'client'): ?>
                            <!-- Меню для клиентов -->
                            <a class="nav-link active" href="#" data-tab="overview">
                                <i class="bi bi-house-door me-2"></i>Обзор
                            </a>
                            <a class="nav-link" href="#" data-tab="cart">
                                <i class="bi bi-cart me-2"></i>Корзина
                                <span class="notification-badge" id="cart-count" style="display: none;">0</span>
                            </a>
                            <a class="nav-link" href="#" data-tab="orders">
                                <i class="bi bi-list-ul me-2"></i>Мои заказы
                            </a>
                            <a class="nav-link" href="#" data-tab="cars">
                                <i class="bi bi-car-front me-2"></i>Мои автомобили
                            </a>
                            <a class="nav-link" href="#" data-tab="profile">
                                <i class="bi bi-person me-2"></i>Профиль
                            </a>
                        <?php elseif ($user_type === 'employee' && $user_role !== 'admin'): ?>
                            <!-- Меню для сотрудников -->
                            <a class="nav-link active" href="#" data-tab="overview">
                                <i class="bi bi-house-door me-2"></i>Обзор
                            </a>
                            <a class="nav-link" href="#" data-tab="orders-manage">
                                <i class="bi bi-clipboard-check me-2"></i>Управление заказами
                            </a>
                            <a class="nav-link" href="#" data-tab="clients">
                                <i class="bi bi-people me-2"></i>Клиенты
                            </a>
                            <a class="nav-link" href="#" data-tab="schedule">
                                <i class="bi bi-calendar me-2"></i>Расписание
                            </a>
                            <a class="nav-link" href="#" data-tab="profile">
                                <i class="bi bi-person me-2"></i>Профиль
                            </a>
                        <?php elseif ($user_type === 'employee' && $user_role === 'admin'): ?>
                            <!-- Меню для администратора -->
                            <a class="nav-link active" href="#" data-tab="dashboard">
                                <i class="bi bi-speedometer2 me-2"></i>Дашборд
                            </a>
                            <a class="nav-link" href="#" data-tab="orders-manage">
                                <i class="bi bi-clipboard-check me-2"></i>Заказы
                            </a>
                            <a class="nav-link" href="#" data-tab="clients">
                                <i class="bi bi-people me-2"></i>Клиенты
                            </a>
                            <a class="nav-link" href="#" data-tab="employees">
                                <i class="bi bi-person-badge me-2"></i>Сотрудники
                            </a>
                            <a class="nav-link" href="#" data-tab="services">
                                <i class="bi bi-tools me-2"></i>Услуги
                            </a>
                            <a class="nav-link" href="#" data-tab="analytics">
                                <i class="bi bi-graph-up me-2"></i>Аналитика
                            </a>
                            <a class="nav-link" href="#" data-tab="settings">
                                <i class="bi bi-gear me-2"></i>Настройки
                            </a>
                        <?php endif; ?>
                        
                        <hr class="my-3">
                        <a class="nav-link" href="#" onclick="logout()">
                            <i class="bi bi-box-arrow-right me-2"></i>Выход
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Основной контент -->
            <div class="col-md-9 col-lg-10 main-content p-0">
                <!-- Заголовок -->
                <div class="bg-white border-bottom p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0" id="page-title">Личный кабинет</h4>
                        <div class="d-flex align-items-center">
                            <div class="position-relative me-3">
                                <i class="bi bi-bell fs-5 text-muted"></i>
                                <span class="notification-badge" id="notification-count" style="display: none;">0</span>
                            </div>
                            <span class="text-muted"><?= date('d.m.Y H:i') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Контент страниц -->
                <div class="p-4">
                    <!-- Загрузка контента будет происходить через AJAX -->
                    <div id="content-area">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                            <p class="mt-3 text-muted">Загрузка данных...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Глобальные переменные
        const userType = '<?= $user_type ?>';
        const userRole = '<?= $user_role ?>';
        const userId = <?= $user_id ?>;

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            // Загружаем первую страницу
            if (userType === 'client') {
                loadPage('overview');
            } else if (userType === 'employee' && userRole === 'admin') {
                loadPage('dashboard');
            } else {
                loadPage('overview');
            }

            // Обновляем счетчики
            updateNotificationCount();
            if (userType === 'client') {
                updateCartCount();
            }

            // Обработчики навигации
            document.querySelectorAll('[data-tab]').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const tab = this.getAttribute('data-tab');
                    
                    // Обновляем активную ссылку
                    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Загружаем контент
                    loadPage(tab);
                });
            });
        });

        // Функция загрузки страницы
        function loadPage(page) {
            const contentArea = document.getElementById('content-area');
            const pageTitle = document.getElementById('page-title');
            
            // Показываем загрузку
            contentArea.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    <p class="mt-3 text-muted">Загрузка данных...</p>
                </div>
            `;

            // Обновляем заголовок
            const titles = {
                'overview': 'Обзор',
                'dashboard': 'Дашборд',
                'cart': 'Корзина',
                'orders': 'Мои заказы',
                'orders-manage': 'Управление заказами',
                'cars': 'Мои автомобили',
                'clients': 'Клиенты',
                'employees': 'Сотрудники',
                'services': 'Услуги',
                'analytics': 'Аналитика',
                'settings': 'Настройки',
                'profile': 'Профиль',
                'schedule': 'Расписание'
            };
            pageTitle.textContent = titles[page] || 'Личный кабинет';

            // Загружаем контент через AJAX
            fetch(`dashboard_content.php?page=${page}`)
                .then(response => response.text())
                .then(html => {
                    contentArea.innerHTML = html;
                    // Инициализируем скрипты для загруженной страницы
                    initPageScripts(page);
                })
                .catch(error => {
                    console.error('Ошибка загрузки:', error);
                    contentArea.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            Ошибка загрузки данных. Попробуйте обновить страницу.
                        </div>
                    `;
                });
        }

        // Инициализация скриптов для конкретных страниц
        function initPageScripts(page) {
            // Здесь будут инициализироваться скрипты для каждой страницы
            console.log('Инициализация скриптов для страницы:', page);
        }

        // Обновление счетчика уведомлений
        function updateNotificationCount() {
            fetch('backend/dashboard_api.php?action=get_notification_count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-count');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Ошибка получения уведомлений:', error));
        }

        // Обновление счетчика корзины (только для клиентов)
        function updateCartCount() {
            if (userType !== 'client') return;
            
            fetch('backend/dashboard_api.php?action=get_cart_count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('cart-count');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'flex';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Ошибка получения корзины:', error));
        }

        // Функция выхода
        function logout() {
            if (confirm('Вы уверены, что хотите выйти?')) {
                window.location.href = 'backend/logout.php';
            }
        }

        // Обновляем счетчики каждые 30 секунд
        setInterval(() => {
            updateNotificationCount();
            if (userType === 'client') {
                updateCartCount();
            }
        }, 30000);
    </script>
</body>
</html>