<?php
require_once 'backend/config.php';
require_once 'backend/auth.php';

// Проверяем авторизацию
$auth = new Auth();
$user = $auth->getCurrentUser();
$userRole = $user ? $user['role_id'] : ROLE_GUEST;
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АвтоСервис ИТ - Комплексные услуги</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <i class="fas fa-tools mr-2"></i>
                    <i class="fas fa-laptop-code mr-2"></i>
                    АвтоСервис ИТ
                </div>
                <div class="flex items-center space-x-6">
                    <a href="#services" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-cogs mr-2"></i>Услуги
                    </a>
                    <?php if ($userRole >= ROLE_ADMIN): ?>
                    <a href="#admin" class="hover:text-code-blue transition-colors">
                        <i class="fas fa-user-shield mr-2"></i>Админ
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($user): ?>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm">
                            <i class="fas fa-user mr-1"></i>
                            <a href="profile.php" class="hover:text-code-blue transition-colors underline">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            </a>
                        </span>
                        <button onclick="logout()" class="btn btn-primary">
                            <i class="fas fa-sign-out-alt"></i>Выход
                        </button>
                    </div>
                    <?php else: ?>
                    <button onclick="showLoginModal()" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i>Вход
                    </button>
                    <button onclick="showRegisterModal()" class="btn btn-auto">
                        <i class="fas fa-user-plus"></i>Регистрация
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Главный заголовок -->
    <section class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-6xl font-bold mb-6 typing-animation">
                АвтоСервис ИТ
            </h1>
            <p class="text-xl mb-8 text-gray-300">
                <span class="text-auto-orange">Автосервис</span> + 
                <span class="text-tech-green">ИТ-услуги</span> = 
                <span class="text-code-blue">Полное решение</span>
            </p>
            
            <!-- Статистика -->
            <div class="grid md:grid-cols-3 gap-6 mb-12 max-w-4xl mx-auto">
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20">
                    <div class="text-3xl font-bold text-code-blue mb-2" id="total-services">0</div>
                    <div class="text-sm text-gray-300">Доступных услуг</div>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-auto-orange/20">
                    <div class="text-3xl font-bold text-auto-orange mb-2" id="total-orders">0</div>
                    <div class="text-sm text-gray-300">Выполненных заказов</div>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-tech-green/20">
                    <div class="text-3xl font-bold text-tech-green mb-2" id="total-clients">0</div>
                    <div class="text-sm text-gray-300">Довольных клиентов</div>
                </div>
            </div>
            
            <!-- Преимущества -->
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20 hover:border-code-blue/50 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-clock text-3xl text-code-blue mb-4"></i>
                    <h3 class="text-lg font-bold mb-2">24/7 Поддержка</h3>
                    <p class="text-sm text-gray-300">Круглосуточная техническая поддержка</p>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-auto-orange/20 hover:border-auto-orange/50 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-shield-alt text-3xl text-auto-orange mb-4"></i>
                    <h3 class="text-lg font-bold mb-2">Гарантия качества</h3>
                    <p class="text-sm text-gray-300">Гарантия на все виды работ</p>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-tech-green/20 hover:border-tech-green/50 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-users text-3xl text-tech-green mb-4"></i>
                    <h3 class="text-lg font-bold mb-2">Опытная команда</h3>
                    <p class="text-sm text-gray-300">Профессиональные специалисты</p>
                </div>
                <div class="bg-black/20 backdrop-blur-md rounded-lg p-6 border border-purple-500/20 hover:border-purple-500/50 transition-all duration-300 hover:scale-105">
                    <i class="fas fa-rocket text-3xl text-purple-500 mb-4"></i>
                    <h3 class="text-lg font-bold mb-2">Быстрое выполнение</h3>
                    <p class="text-sm text-gray-300">Оперативное решение задач</p>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="scrollToSection('services')" class="btn btn-auto">
                    <i class="fas fa-wrench"></i>Автосервис
                </button>
                <button onclick="scrollToSection('services')" class="btn btn-it">
                    <i class="fas fa-laptop-code"></i>ИТ-услуги
                </button>
                <?php if ($user): ?>
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-clipboard-list"></i>Мои заказы
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Услуги -->
    <section id="services" class="py-20 relative z-10">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-12">
                <i class="fas fa-cogs mr-4 text-code-blue"></i>
                Наши услуги
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="services-grid">
                <!-- Услуги будут загружены через AJAX -->
            </div>
        </div>
    </section>

    <!-- Админ панель -->
    <?php if ($userRole >= ROLE_ADMIN): ?>
    <section id="admin" class="py-20 relative z-10">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center mb-12">
                <i class="fas fa-user-shield mr-4 text-code-blue"></i>
                Панель администратора
            </h2>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="service-card text-center">
                    <i class="fas fa-users text-4xl text-code-blue mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Клиенты</h3>
                    <button onclick="manageClients()" class="btn btn-primary">Управление</button>
                </div>
                <div class="service-card text-center">
                    <i class="fas fa-car text-4xl text-auto-orange mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Автомобили</h3>
                    <button onclick="manageCars()" class="btn btn-auto">Управление</button>
                </div>
                <div class="service-card text-center">
                    <i class="fas fa-cogs text-4xl text-tech-green mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Услуги</h3>
                    <button onclick="manageServices()" class="btn btn-it">Управление</button>
                </div>
                <div class="service-card text-center">
                    <i class="fas fa-chart-bar text-4xl text-terminal-green mb-4"></i>
                    <h3 class="text-xl font-bold mb-2">Отчеты</h3>
                    <button onclick="showReports()" class="btn btn-primary">Просмотр</button>
                </div>
            </div>
            
            <!-- График доходов -->
            <div class="bg-black/20 backdrop-blur-md rounded-lg p-6">
                <h3 class="text-2xl font-bold mb-4">Доходы по месяцам</h3>
                <canvas id="revenueChart" width="400" height="200"></canvas>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Подвал -->
    <footer class="bg-black/40 backdrop-blur-md py-12 relative z-10">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 text-code-blue">
                        <i class="fas fa-tools mr-2"></i>АвтоСервис ИТ
                    </h3>
                    <p class="text-gray-300">
                        Комплексные услуги автосервиса и ИТ-поддержки. 
                        Профессиональный подход к каждому клиенту.
                    </p>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Контакты</h3>
                    <div class="space-y-2 text-gray-300">
                        <p><i class="fas fa-phone mr-2"></i>+7 (999) 123-45-67</p>
                        <p><i class="fas fa-envelope mr-2"></i>info@autoservice-it.ru</p>
                        <p><i class="fas fa-map-marker-alt mr-2"></i>г. Москва, ул. Примерная, 123</p>
                    </div>
                </div>
                <div>
                    <h3 class="text-xl font-bold mb-4">Режим работы</h3>
                    <div class="space-y-2 text-gray-300">
                        <p>Пн-Пт: 9:00 - 18:00</p>
                        <p>Сб: 10:00 - 16:00</p>
                        <p>Вс: выходной</p>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2024 АвтоСервис ИТ. Все права защищены.</p>
            </div>
        </div>
    </footer>

    <!-- Модальные окна -->
    <?php include 'src/modals.php'; ?>

    <!-- Скрипты -->
    <script src="src/js/modals.js"></script>
    <script src="src/app.js"></script>
    <script>
        // Глобальные функции для совместимости
        function loadServices() {
            if (window.app) {
                window.app.loadInitialData();
            }
        }
        
        function loadOrders() {
            if (window.app) {
                window.app.loadUserOrders();
            }
        }
        
        function loadStatistics() {
            // Загружаем статистику для главной страницы
            fetch('backend/api/statistics.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Анимированное обновление счетчиков
                        animateCounter('total-services', data.data.total_services);
                        animateCounter('total-orders', data.data.total_orders);
                        animateCounter('total-clients', data.data.total_clients);
                    }
                })
                .catch(error => console.error('Ошибка загрузки статистики:', error));
        }
        
        function animateCounter(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const duration = 2000; // 2 секунды
            const startTime = performance.now();
            
            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
                
                element.textContent = currentValue;
                
                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                }
            }
            
            requestAnimationFrame(updateCounter);
        }
        
        function loadRevenueChart() {
            if (window.app) {
                window.app.loadAdminPanel();
            }
        }
        
        // Инициализация при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            loadServices();
            loadStatistics();
            <?php if ($user): ?>
            loadOrders();
            <?php endif; ?>
            <?php if ($userRole >= ROLE_ADMIN): ?>
            loadRevenueChart();
            <?php endif; ?>
        });
    </script>
</body>
</html>