<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['cargo_auth']) || $_SESSION['cargo_auth'] !== true) {
    header('Location: ../index.html');
    exit();
}

// Функция для чтения заказов из файлов логов
function getOrders() {
    $orders = [];
    $errors = [];
    $log_dir = __DIR__ . '/logs';
    
    if (!is_dir($log_dir)) {
        if (!mkdir($log_dir, 0755, true)) {
            $_SESSION['basket_error'] = 'Не удалось создать директорию для логов';
        }
        return $orders;
    }
    
    if (!is_readable($log_dir)) {
        $_SESSION['basket_error'] = 'Нет доступа к директории логов';
        return $orders;
    }
    
    $log_files = glob($log_dir . '/orders_*.log');
    
    if ($log_files === false) {
        $_SESSION['basket_error'] = 'Ошибка при поиске файлов логов';
        return $orders;
    }
    
    foreach ($log_files as $file) {
        if (file_exists($file) && is_readable($file)) {
            $lines = @file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines === false) {
                $errors[] = basename($file);
                continue;
            }
            
            foreach ($lines as $line_num => $line) {
                $parts = explode(' | ', $line);
                if (count($parts) >= 6) {
                    $orders[] = [
                        'date' => trim($parts[0]),
                        'order_number' => str_replace('Order: ', '', trim($parts[1])),
                        'customer' => str_replace('Customer: ', '', trim($parts[2])),
                        'email' => str_replace('Email: ', '', trim($parts[3])),
                        'route' => str_replace('Route: ', '', trim($parts[4])),
                        'total' => str_replace('Total: ', '', trim($parts[5]))
                    ];
                } else {
                    // Игнорируем поврежденные строки, но не показываем ошибку
                }
            }
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['basket_warning'] = 'Некоторые файлы логов недоступны: ' . implode(', ', $errors);
    }
    
    // Сортировка по дате (новые сначала)
    usort($orders, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });
    
    return $orders;
}

$orders = getOrders();
$total_orders = count($orders);
$total_amount = array_sum(array_map(function($order) {
    // Удаляем символ рубля и пробелы (разделители тысяч)
    $clean_total = str_replace(['₽', ' '], '', $order['total']);
    return (float)$clean_total;
}, $orders));

// Статистика по маршрутам
$route_stats = [];
foreach ($orders as $order) {
    $route = $order['route'];
    if (!isset($route_stats[$route])) {
        $route_stats[$route] = ['count' => 0, 'total' => 0];
    }
    $route_stats[$route]['count']++;
    // Удаляем символ рубля и пробелы (разделители тысяч)
    $clean_total = str_replace(['₽', ' '], '', $order['total']);
    $route_stats[$route]['total'] += (float)$clean_total;
}

// Фильтрация
$filter_route = $_GET['filter_route'] ?? '';
$search_query = $_GET['search'] ?? '';

$filtered_orders = $orders;
if ($filter_route) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($filter_route) {
        return strpos($order['route'], $filter_route) !== false;
    });
}

if ($search_query) {
    $filtered_orders = array_filter($filtered_orders, function($order) use ($search_query) {
        return stripos($order['customer'], $search_query) !== false || 
               stripos($order['order_number'], $search_query) !== false ||
               stripos($order['email'], $search_query) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина заказов - Cargo Transport</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'code-dark': '#0d1117',
                        'code-blue': '#58a6ff',
                        'code-green': '#7c3aed',
                        'code-purple': '#a855f7',
                        'terminal-green': '#00ff00'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="bg-code-dark text-white min-h-screen relative overflow-x-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0">
        <div class="code-matrix"></div>
        <div class="floating-elements"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black/20 backdrop-blur-md z-50 border-b border-code-blue/20">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-2xl font-bold text-code-blue">
                    <i class="fas fa-truck mr-2"></i>
                    Cargo Transport
                </div>
                <div class="flex items-center space-x-6">
                    <span class="text-terminal-green font-mono">admin@cargo:~$</span>
                    <a href="order.php" class="hover:text-code-blue transition-colors font-mono">
                        <i class="fas fa-plus mr-2"></i>newOrder()
                    </a>
                    <a href="../index.html" class="hover:text-code-blue transition-colors font-mono">
                        <i class="fas fa-home mr-2"></i>home()
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['basket_error'])): ?>
    <div class="pt-20 pb-4">
        <div class="container mx-auto px-6">
            <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-400 mr-2"></i>
                    <span class="text-red-400 font-mono"><?php echo htmlspecialchars($_SESSION['basket_error']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['basket_error']); endif; ?>

    <?php if (isset($_SESSION['basket_warning'])): ?>
    <div class="pt-4 pb-4">
        <div class="container mx-auto px-6">
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-400 mr-2"></i>
                    <span class="text-yellow-400 font-mono"><?php echo htmlspecialchars($_SESSION['basket_warning']); ?></span>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['basket_warning']); endif; ?>

    <!-- Main Content -->
    <main class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-code-purple/20 rounded-full mb-6 animate-pulse">
                    <i class="fas fa-shopping-basket text-3xl text-code-purple"></i>
                </div>
                <h1 class="text-4xl md:text-6xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-code-purple to-code-blue font-mono">
                    orderBasket()
                </h1>
                <p class="text-gray-400 font-mono">// История и управление заказами грузовых перевозок</p>
            </div>

            <!-- Statistics Dashboard -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="terminal-window p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-code-blue font-mono">totalOrders()</h3>
                        <i class="fas fa-chart-line text-code-blue"></i>
                    </div>
                    <div class="text-3xl font-bold text-terminal-green font-mono"><?= $total_orders ?></div>
                    <p class="text-gray-400 text-sm font-mono">заказов оформлено</p>
                </div>

                <div class="terminal-window p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-code-purple font-mono">totalAmount()</h3>
                        <i class="fas fa-ruble-sign text-code-purple"></i>
                    </div>
                    <div class="text-3xl font-bold text-terminal-green font-mono"><?= number_format($total_amount, 0, '.', ' ') ?>₽</div>
                    <p class="text-gray-400 text-sm font-mono">общая сумма</p>
                </div>

                <div class="terminal-window p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-yellow-400 font-mono">avgOrder()</h3>
                        <i class="fas fa-calculator text-yellow-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-terminal-green font-mono">
                        <?= $total_orders > 0 ? number_format($total_amount / $total_orders, 0, '.', ' ') : 0 ?>₽
                    </div>
                    <p class="text-gray-400 text-sm font-mono">средний чек</p>
                </div>
            </div>

            <!-- Route Statistics -->
            <?php if (!empty($route_stats)): ?>
            <div class="terminal-window p-6 mb-8">
                <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-400 ml-4 font-mono">route.statistics</span>
                </div>
                
                <h2 class="text-2xl font-bold text-code-blue mb-6 font-mono">
                    <i class="fas fa-route mr-2"></i>Статистика по маршрутам
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($route_stats as $route => $stats): ?>
                    <div class="bg-gray-900/30 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-bold text-white font-mono text-sm"><?= htmlspecialchars($route) ?></h3>
                            <span class="text-terminal-green font-mono"><?= $stats['count'] ?> заказов</span>
                        </div>
                        <div class="text-right">
                            <span class="text-code-purple font-mono font-bold"><?= number_format($stats['total'], 0, '.', ' ') ?>₽</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="terminal-window p-6 mb-8">
                <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-400 ml-4 font-mono">filter.orders</span>
                </div>
                
                <form method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" 
                               placeholder="Поиск по имени, номеру заказа или email..." 
                               class="w-full bg-gray-900/50 border border-gray-600 rounded-lg px-4 py-2 text-white font-mono focus:border-code-blue focus:outline-none">
                    </div>
                    <div>
                        <select name="filter_route" class="bg-gray-900/50 border border-gray-600 rounded-lg px-4 py-2 text-white font-mono focus:border-code-blue focus:outline-none">
                            <option value="">Все маршруты</option>
                            <option value="Санкт-Петербург" <?= $filter_route === 'Санкт-Петербург' ? 'selected' : '' ?>>Санкт-Петербург</option>
                            <option value="Севастополь" <?= $filter_route === 'Севастополь' ? 'selected' : '' ?>>Севастополь</option>
                            <option value="Амстердам" <?= $filter_route === 'Амстердам' ? 'selected' : '' ?>>Амстердам</option>
                            <option value="Лиссабон" <?= $filter_route === 'Лиссабон' ? 'selected' : '' ?>>Лиссабон</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-code-blue hover:bg-code-blue/80 text-white px-6 py-2 rounded-lg font-mono transition-colors">
                        <i class="fas fa-search mr-2"></i>filter()
                    </button>
                    <?php if ($filter_route || $search_query): ?>
                    <a href="basket.php" class="bg-gray-600 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-mono transition-colors text-center">
                        <i class="fas fa-times mr-2"></i>clear()
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Orders List -->
            <div class="terminal-window p-6">
                <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-400 ml-4 font-mono">orders.list</span>
                </div>
                
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-terminal-green font-mono">
                        <i class="fas fa-list mr-2"></i>Список заказов
                    </h2>
                    <span class="text-gray-400 font-mono">
                        Показано: <?= count($filtered_orders) ?> из <?= $total_orders ?>
                    </span>
                </div>

                <?php if (empty($filtered_orders)): ?>
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-600/20 rounded-full mb-4">
                            <i class="fas fa-inbox text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-400 mb-2 font-mono">Заказы не найдены</h3>
                        <p class="text-gray-500 font-mono">// Попробуйте изменить параметры поиска</p>
                        <a href="order.php" class="inline-block mt-4 bg-code-blue hover:bg-code-blue/80 text-white px-6 py-2 rounded-lg font-mono transition-colors">
                            <i class="fas fa-plus mr-2"></i>Создать заказ
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($filtered_orders as $index => $order): ?>
                        <div class="bg-gray-900/30 border border-gray-700 rounded-lg p-4 hover:border-code-blue/50 transition-colors order-card" 
                             data-order="<?= $index ?>">
                            <div class="flex flex-col md:flex-row md:items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <span class="bg-code-blue/20 text-code-blue px-2 py-1 rounded text-sm font-mono mr-3">
                                            <?= htmlspecialchars($order['order_number']) ?>
                                        </span>
                                        <span class="text-gray-400 font-mono text-sm">
                                            <?= date('d.m.Y H:i', strtotime($order['date'])) ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <h3 class="font-bold text-white font-mono"><?= htmlspecialchars($order['customer']) ?></h3>
                                        <p class="text-gray-400 font-mono text-sm"><?= htmlspecialchars($order['email']) ?></p>
                                    </div>
                                    <div class="text-code-purple font-mono text-sm">
                                        <i class="fas fa-route mr-1"></i>
                                        <?= htmlspecialchars($order['route']) ?>
                                    </div>
                                </div>
                                <div class="mt-4 md:mt-0 md:text-right">
                                    <div class="text-2xl font-bold text-terminal-green font-mono mb-2">
                                        <?= htmlspecialchars($order['total']) ?>
                                    </div>
                                    <div class="flex md:justify-end space-x-2">
                                        <button onclick="toggleOrderDetails(<?= $index ?>)" 
                                                class="bg-gray-600 hover:bg-gray-500 text-white px-3 py-1 rounded text-sm font-mono transition-colors">
                                            <i class="fas fa-eye mr-1"></i>view()
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Order Details (Hidden by default) -->
                            <div id="order-details-<?= $index ?>" class="hidden mt-4 pt-4 border-t border-gray-600">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm font-mono">
                                    <div>
                                        <h4 class="text-code-blue font-bold mb-2">Информация о заказе</h4>
                                        <div class="space-y-1 text-gray-300">
                                            <div>Номер: <?= htmlspecialchars($order['order_number']) ?></div>
                                            <div>Дата: <?= date('d.m.Y H:i:s', strtotime($order['date'])) ?></div>
                                            <div>Маршрут: <?= htmlspecialchars($order['route']) ?></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="text-code-purple font-bold mb-2">Заказчик</h4>
                                        <div class="space-y-1 text-gray-300">
                                            <div>Имя: <?= htmlspecialchars($order['customer']) ?></div>
                                            <div>Email: <?= htmlspecialchars($order['email']) ?></div>
                                            <div>Стоимость: <?= htmlspecialchars($order['total']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination could be added here for large datasets -->
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
                <a href="order.php" class="bg-gradient-to-r from-code-blue to-code-purple text-white px-8 py-3 rounded-lg font-semibold hover:shadow-lg hover:shadow-code-blue/25 transition-all duration-300 transform hover:scale-105 text-center font-mono">
                    <i class="fas fa-plus mr-2"></i>newOrder()
                </a>
                <a href="../index.html" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-center font-mono">
                            <i class="fas fa-home mr-2"></i>home()
                        </a>
            </div>
        </div>
    </main>

    <script>
        // Проверка авторизации
        if (localStorage.getItem('cargo_auth') !== 'true') {
            window.location.href = 'index.html';
        }

        // Функция для показа/скрытия деталей заказа
        function toggleOrderDetails(index) {
            const details = document.getElementById(`order-details-${index}`);
            const button = event.target.closest('button');
            const icon = button.querySelector('i');
            
            if (details.classList.contains('hidden')) {
                details.classList.remove('hidden');
                details.classList.add('animate-fade-in');
                icon.className = 'fas fa-eye-slash mr-1';
                button.innerHTML = '<i class="fas fa-eye-slash mr-1"></i>hide()';
            } else {
                details.classList.add('hidden');
                details.classList.remove('animate-fade-in');
                icon.className = 'fas fa-eye mr-1';
                button.innerHTML = '<i class="fas fa-eye mr-1"></i>view()';
            }
        }

        // Анимация появления карточек заказов
        document.addEventListener('DOMContentLoaded', function() {
            const orderCards = document.querySelectorAll('.order-card');
            orderCards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
            });
        });

        // Автоматическое обновление статистики (каждые 30 секунд)
        setInterval(() => {
            // В реальном приложении здесь был бы AJAX запрос
            console.log('Checking for new orders...');
        }, 30000);
    </script>
</body>
</html>