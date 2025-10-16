<?php
require_once 'backend/config.php';
require_once 'backend/auth.php';

// Проверяем авторизацию
$auth = new Auth();
$user = $auth->getCurrentUser();

if (!$user) {
    header('Location: index.php');
    exit;
}

$userRole = $user['role_id'];
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - АвтоСервис ИТ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'code-dark': '#0d1117',
                        'code-blue': '#58a6ff',
                        'code-green': '#7c3aed',
                        'terminal-green': '#00ff00',
                        'auto-orange': '#ff6b35',
                        'auto-blue': '#004e89',
                        'tech-green': '#00d4aa'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-code-dark text-white min-h-screen">
    <!-- Анимированный фон -->
    <div class="fixed inset-0 z-0">
        <div class="code-matrix"></div>
        <div class="floating-elements"></div>
    </div>

    <!-- Навигация -->
    <nav class="fixed top-0 w-full bg-black/20 backdrop-blur-md z-50 border-b border-code-blue/20">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-2xl font-bold text-code-blue">
                    <a href="index.php" class="hover:text-white transition-colors">
                        <i class="fas fa-tools mr-2"></i>
                        <i class="fas fa-laptop-code mr-2"></i>
                        АвтоСервис ИТ
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="index.php" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-home mr-2"></i>Главная
                    </a>
                    <a href="orders.php" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-clipboard-list mr-2"></i>Мои заказы
                    </a>
                    <?php if ($userRole >= ROLE_ADMIN): ?>
                    <a href="dashboard.php" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-user-shield mr-2"></i>Админ панель
                    </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-code-blue">
                            <i class="fas fa-user mr-1"></i>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </span>
                        <button onclick="logout()" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt"></i>Выход
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Основной контент -->
    <main class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <h1 class="text-4xl font-bold text-center mb-12">
                <i class="fas fa-user-circle mr-4 text-code-blue"></i>
                Личный кабинет
            </h1>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Информация о пользователе -->
                <div class="lg:col-span-1">
                    <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20">
                        <h2 class="text-2xl font-bold mb-6 text-code-blue">
                            <i class="fas fa-id-card mr-2"></i>Профиль
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Имя</label>
                                <input type="text" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" 
                                       class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Фамилия</label>
                                <input type="text" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" 
                                       class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                                <input type="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" 
                                       class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Телефон</label>
                                <input type="tel" id="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                       class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-1">Роль</label>
                                <input type="text" value="<?= $userRole == ROLE_ADMIN ? 'Администратор' : ($userRole == ROLE_EMPLOYEE ? 'Сотрудник' : 'Клиент') ?>" 
                                       class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-gray-400" readonly>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-3">
                            <button onclick="updateProfile()" class="w-full btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Сохранить изменения
                            </button>
                            <button onclick="showChangePasswordModal()" class="w-full btn btn-auto">
                                <i class="fas fa-key mr-2"></i>Изменить пароль
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Статистика и активность -->
                <div class="lg:col-span-2">
                    <div class="grid md:grid-cols-2 gap-6 mb-8">
                        <!-- Статистика заказов -->
                        <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-auto-orange/20">
                            <h3 class="text-xl font-bold mb-4 text-auto-orange">
                                <i class="fas fa-chart-bar mr-2"></i>Мои заказы
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Всего заказов:</span>
                                    <span class="font-bold" id="total-user-orders">0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Активных:</span>
                                    <span class="font-bold text-yellow-400" id="active-user-orders">0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Завершенных:</span>
                                    <span class="font-bold text-green-400" id="completed-user-orders">0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-300">Общая сумма:</span>
                                    <span class="font-bold text-code-blue" id="total-user-spent">0 ₽</span>
                                </div>
                            </div>
                        </div>

                        <!-- Мои автомобили -->
                        <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-tech-green/20">
                            <h3 class="text-xl font-bold mb-4 text-tech-green">
                                <i class="fas fa-car mr-2"></i>Мои автомобили
                            </h3>
                            <div id="user-cars" class="space-y-2">
                                <!-- Автомобили будут загружены через AJAX -->
                            </div>
                            <button onclick="showAddCarModal()" class="w-full mt-4 btn btn-it">
                                <i class="fas fa-plus mr-2"></i>Добавить автомобиль
                            </button>
                        </div>
                    </div>

                    <!-- Последние заказы -->
                    <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20">
                        <h3 class="text-xl font-bold mb-4 text-code-blue">
                            <i class="fas fa-history mr-2"></i>Последние заказы
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="data-table" id="recent-orders-table">
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Автомобиль</th>
                                        <th>Статус</th>
                                        <th>Сумма</th>
                                        <th>Дата</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Заказы будут загружены через AJAX -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-center mt-4">
                            <a href="orders.php" class="btn btn-primary">
                                <i class="fas fa-list mr-2"></i>Все заказы
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Модальные окна -->
    <?php include 'src/modals.php'; ?>

    <!-- Скрипты -->
    <script src="src/js/modals.js"></script>
    <script src="src/app.js"></script>
    <script>
        // Функции для работы с профилем
        function updateProfile() {
            const data = {
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value
            };

            fetch('backend/api/profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Профиль успешно обновлен', 'success');
                } else {
                    showNotification('Ошибка: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Ошибка сети', 'error');
            });
        }

        function loadUserData() {
            // Загружаем статистику пользователя
            fetch('backend/api/user-stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-user-orders').textContent = data.data.total_orders;
                        document.getElementById('active-user-orders').textContent = data.data.active_orders;
                        document.getElementById('completed-user-orders').textContent = data.data.completed_orders;
                        document.getElementById('total-user-spent').textContent = data.data.total_spent + ' ₽';
                    }
                });

            // Загружаем автомобили пользователя
            fetch('backend/api/user-cars.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const carsContainer = document.getElementById('user-cars');
                        carsContainer.innerHTML = '';
                        
                        if (data.data.length === 0) {
                            carsContainer.innerHTML = '<p class="text-gray-400 text-sm">Автомобили не добавлены</p>';
                        } else {
                            data.data.forEach(car => {
                                const carElement = document.createElement('div');
                                carElement.className = 'flex justify-between items-center p-2 bg-black/30 rounded';
                                carElement.innerHTML = `
                                    <span class="text-sm">${car.brand} ${car.model} (${car.year})</span>
                                    <span class="text-xs text-gray-400">${car.license_plate}</span>
                                `;
                                carsContainer.appendChild(carElement);
                            });
                        }
                    }
                });

            // Загружаем последние заказы
            fetch('backend/api/user-orders.php?limit=5')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.querySelector('#recent-orders-table tbody');
                        tbody.innerHTML = '';
                        
                        if (data.data.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-gray-400">Заказы не найдены</td></tr>';
                        } else {
                            data.data.forEach(order => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>#${order.order_id}</td>
                                    <td>${order.car_info}</td>
                                    <td><span class="status-badge status-${order.status_id}">${order.status_name}</span></td>
                                    <td>${order.total_cost} ₽</td>
                                    <td>${order.order_date}</td>
                                    <td>
                                        <button onclick="viewOrder(${order.order_id})" class="btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                `;
                                tbody.appendChild(row);
                            });
                        }
                    }
                });
        }

        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            loadUserData();
        });
    </script>
</body>
</html>