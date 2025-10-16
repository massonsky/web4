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
    <title>Мои заказы - АвтоСервис ИТ</title>
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
                    <a href="profile.php" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-user mr-2"></i>Профиль
                    </a>
                    <?php if ($userRole >= ROLE_ADMIN): ?>
                    <a href="dashboard.php" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-user-shield mr-2"></i>Админ панель
                    </a>
                    <?php endif; ?>
                    
                    <div class="flex items-center space-x-4">
                        <a href="profile.php" class="text-sm text-code-blue hover:text-white transition-colors">
                            <i class="fas fa-user mr-1"></i>
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </a>
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
            <div class="flex justify-between items-center mb-8">
                <h1 class="text-4xl font-bold">
                    <i class="fas fa-clipboard-list mr-4 text-code-blue"></i>
                    Мои заказы
                </h1>
                <button onclick="showNewOrderModal()" class="btn btn-primary">
                    <i class="fas fa-plus mr-2"></i>Новый заказ
                </button>
            </div>

            <!-- Фильтры -->
            <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20 mb-8">
                <div class="grid md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Статус</label>
                        <select id="status-filter" class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            <option value="">Все статусы</option>
                            <option value="1">Новый</option>
                            <option value="2">В работе</option>
                            <option value="3">Ожидает запчасти</option>
                            <option value="4">Готов</option>
                            <option value="5">Завершен</option>
                            <option value="6">Отменен</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Автомобиль</label>
                        <select id="car-filter" class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                            <option value="">Все автомобили</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Дата от</label>
                        <input type="date" id="date-from" class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Дата до</label>
                        <input type="date" id="date-to" class="w-full px-3 py-2 bg-black/30 border border-gray-600 rounded-md text-white focus:border-code-blue focus:outline-none">
                    </div>
                </div>
                <div class="mt-4 flex space-x-4">
                    <button onclick="applyFilters()" class="btn btn-primary">
                        <i class="fas fa-filter mr-2"></i>Применить фильтры
                    </button>
                    <button onclick="resetFilters()" class="btn btn-auto">
                        <i class="fas fa-times mr-2"></i>Сбросить
                    </button>
                </div>
            </div>

            <!-- Статистика -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-auto-orange/20 text-center">
                    <div class="text-3xl font-bold text-auto-orange mb-2" id="total-orders">0</div>
                    <div class="text-sm text-gray-300">Всего заказов</div>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-yellow-500/20 text-center">
                    <div class="text-3xl font-bold text-yellow-400 mb-2" id="active-orders">0</div>
                    <div class="text-sm text-gray-300">Активных</div>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-green-500/20 text-center">
                    <div class="text-3xl font-bold text-green-400 mb-2" id="completed-orders">0</div>
                    <div class="text-sm text-gray-300">Завершенных</div>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20 text-center">
                    <div class="text-3xl font-bold text-code-blue mb-2" id="total-spent">0 ₽</div>
                    <div class="text-sm text-gray-300">Потрачено</div>
                </div>
            </div>

            <!-- Таблица заказов -->
            <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20">
                <div class="overflow-x-auto">
                    <table class="data-table" id="orders-table">
                        <thead>
                            <tr>
                                <th>№ заказа</th>
                                <th>Автомобиль</th>
                                <th>Услуги</th>
                                <th>Статус</th>
                                <th>Сумма</th>
                                <th>Дата создания</th>
                                <th>Дата завершения</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Заказы будут загружены через AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Пагинация -->
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-300">
                        Показано <span id="showing-from">0</span>-<span id="showing-to">0</span> из <span id="total-count">0</span> заказов
                    </div>
                    <div class="flex space-x-2" id="pagination">
                        <!-- Пагинация будет сгенерирована через JavaScript -->
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
        let currentPage = 1;
        let currentFilters = {};

        // Загрузка заказов
        function loadOrders(page = 1, filters = {}) {
            currentPage = page;
            currentFilters = filters;
            
            const params = new URLSearchParams({
                page: page,
                limit: 10,
                ...filters
            });

            fetch(`backend/api/user-orders.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayOrders(data.data.orders);
                        updatePagination(data.data.pagination);
                        updateStatistics(data.data.statistics);
                    } else {
                        showNotification('Ошибка загрузки заказов: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Ошибка сети', 'error');
                });
        }

        // Отображение заказов в таблице
        function displayOrders(orders) {
            const tbody = document.querySelector('#orders-table tbody');
            tbody.innerHTML = '';

            if (orders.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center text-gray-400 py-8">Заказы не найдены</td></tr>';
                return;
            }

            orders.forEach(order => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="font-mono">#${order.order_id}</td>
                    <td>
                        <div class="text-sm">
                            <div class="font-medium">${order.car_brand} ${order.car_model}</div>
                            <div class="text-gray-400">${order.license_plate} (${order.car_year})</div>
                        </div>
                    </td>
                    <td>
                        <div class="text-sm">
                            ${order.services.map(service => `<div class="mb-1">${service.service_name}</div>`).join('')}
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-${order.status_id}">
                            ${order.status_name}
                        </span>
                    </td>
                    <td class="font-mono text-code-blue">${order.total_cost} ₽</td>
                    <td class="text-sm text-gray-300">${formatDate(order.order_date)}</td>
                    <td class="text-sm text-gray-300">${order.completion_date ? formatDate(order.completion_date) : '-'}</td>
                    <td>
                        <div class="flex space-x-2">
                            <button onclick="viewOrder(${order.order_id})" class="btn-sm btn-primary" title="Просмотр">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${order.status_id <= 2 ? `
                                <button onclick="editOrder(${order.order_id})" class="btn-sm btn-it" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="cancelOrder(${order.order_id})" class="btn-sm btn-danger" title="Отменить">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Обновление пагинации
        function updatePagination(pagination) {
            document.getElementById('showing-from').textContent = pagination.from;
            document.getElementById('showing-to').textContent = pagination.to;
            document.getElementById('total-count').textContent = pagination.total;

            const paginationContainer = document.getElementById('pagination');
            paginationContainer.innerHTML = '';

            if (pagination.total_pages <= 1) return;

            // Предыдущая страница
            if (pagination.current_page > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'btn-sm btn-auto';
                prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                prevBtn.onclick = () => loadOrders(pagination.current_page - 1, currentFilters);
                paginationContainer.appendChild(prevBtn);
            }

            // Номера страниц
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = i === pagination.current_page ? 'btn-sm btn-primary' : 'btn-sm btn-auto';
                pageBtn.textContent = i;
                pageBtn.onclick = () => loadOrders(i, currentFilters);
                paginationContainer.appendChild(pageBtn);
            }

            // Следующая страница
            if (pagination.current_page < pagination.total_pages) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'btn-sm btn-auto';
                nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                nextBtn.onclick = () => loadOrders(pagination.current_page + 1, currentFilters);
                paginationContainer.appendChild(nextBtn);
            }
        }

        // Обновление статистики
        function updateStatistics(stats) {
            document.getElementById('total-orders').textContent = stats.total_orders;
            document.getElementById('active-orders').textContent = stats.active_orders;
            document.getElementById('completed-orders').textContent = stats.completed_orders;
            document.getElementById('total-spent').textContent = stats.total_spent + ' ₽';
        }

        // Применение фильтров
        function applyFilters() {
            const filters = {
                status_id: document.getElementById('status-filter').value,
                car_id: document.getElementById('car-filter').value,
                date_from: document.getElementById('date-from').value,
                date_to: document.getElementById('date-to').value
            };

            // Удаляем пустые фильтры
            Object.keys(filters).forEach(key => {
                if (!filters[key]) delete filters[key];
            });

            loadOrders(1, filters);
        }

        // Сброс фильтров
        function resetFilters() {
            document.getElementById('status-filter').value = '';
            document.getElementById('car-filter').value = '';
            document.getElementById('date-from').value = '';
            document.getElementById('date-to').value = '';
            loadOrders(1, {});
        }

        // Загрузка автомобилей для фильтра
        function loadCarsForFilter() {
            fetch('backend/api/user-cars.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const carFilter = document.getElementById('car-filter');
                        data.data.forEach(car => {
                            const option = document.createElement('option');
                            option.value = car.car_id;
                            option.textContent = `${car.brand} ${car.model} (${car.license_plate})`;
                            carFilter.appendChild(option);
                        });
                    }
                });
        }

        // Просмотр заказа
        function viewOrder(orderId) {
            // Открываем модальное окно с деталями заказа
            fetch(`backend/api/order-details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showOrderDetailsModal(data.data);
                    } else {
                        showNotification('Ошибка загрузки деталей заказа', 'error');
                    }
                });
        }

        // Отмена заказа
        function cancelOrder(orderId) {
            if (confirm('Вы уверены, что хотите отменить этот заказ?')) {
                fetch('backend/api/cancel-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ order_id: orderId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('Заказ отменен', 'success');
                        loadOrders(currentPage, currentFilters);
                    } else {
                        showNotification('Ошибка: ' + data.message, 'error');
                    }
                });
            }
        }

        // Форматирование даты
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU') + ' ' + date.toLocaleTimeString('ru-RU', {hour: '2-digit', minute: '2-digit'});
        }

        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            loadOrders();
            loadCarsForFilter();
        });
    </script>
</body>
</html>