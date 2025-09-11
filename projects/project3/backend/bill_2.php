<?php
session_start();
require_once 'config.php';
require_once 'EmailService.php';

// Проверка авторизации
if (!isset($_SESSION['cargo_auth']) || $_SESSION['cargo_auth'] !== true) {
    header('Location: index.html');
    exit();
}

// Проверка данных заказа
if (!isset($_SESSION['order_data'])) {
    header('Location: order.php');
    exit();
}

$order_data = $_SESSION['order_data'];

// Базовые данные для расчетов
$routes = [
    // Основные международные маршруты
    'spb-amsterdam' => [
        'name' => 'Санкт-Петербург → Амстердам',
        'price' => 28500,
        'distance' => 1650,
        'from' => 'Санкт-Петербург',
        'to' => 'Амстердам',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [52.3676, 4.9041]]
    ],
    'spb-lisbon' => [
        'name' => 'Санкт-Петербург → Лиссабон',
        'price' => 35200,
        'distance' => 3200,
        'from' => 'Санкт-Петербург',
        'to' => 'Лиссабон',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [38.7223, -9.1393]]
    ],
    'sevastopol-amsterdam' => [
        'name' => 'Севастополь → Амстердам',
        'price' => 32800,
        'distance' => 2150,
        'from' => 'Севастополь',
        'to' => 'Амстердам',
        'coords' => ['from' => [44.6160, 33.5254], 'to' => [52.3676, 4.9041]]
    ],
    'sevastopol-lisbon' => [
        'name' => 'Севастополь → Лиссабон',
        'price' => 38900,
        'distance' => 2850,
        'from' => 'Севастополь',
        'to' => 'Лиссабон',
        'coords' => ['from' => [44.6160, 33.5254], 'to' => [38.7223, -9.1393]]
    ],
    
    // Дополнительные внутренние маршруты
    'spb-moscow' => [
        'name' => 'Санкт-Петербург → Москва',
        'price' => 3500,
        'distance' => 635,
        'from' => 'Санкт-Петербург',
        'to' => 'Москва',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [55.7558, 37.6176]]
    ],
    'moscow-kazan' => [
        'name' => 'Москва → Казань',
        'price' => 4200,
        'distance' => 815,
        'from' => 'Москва',
        'to' => 'Казань',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [55.8304, 49.0661]]
    ],
    'spb-ekb' => [
        'name' => 'Санкт-Петербург → Екатеринбург',
        'price' => 8800,
        'distance' => 1766,
        'from' => 'Санкт-Петербург',
        'to' => 'Екатеринбург',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [56.8431, 60.6454]]
    ],
    'moscow-nsk' => [
        'name' => 'Москва → Новосибирск',
        'price' => 14500,
        'distance' => 3354,
        'from' => 'Москва',
        'to' => 'Новосибирск',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [55.0084, 82.9357]]
    ],
    'spb-nn' => [
        'name' => 'Санкт-Петербург → Нижний Новгород',
        'price' => 4800,
        'distance' => 920,
        'from' => 'Санкт-Петербург',
        'to' => 'Нижний Новгород',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [56.2965, 43.9361]]
    ],
    'moscow-rostov' => [
        'name' => 'Москва → Ростов-на-Дону',
        'price' => 6200,
        'distance' => 1076,
        'from' => 'Москва',
        'to' => 'Ростов-на-Дону',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [47.2357, 39.7015]]
    ],
    'spb-samara' => [
        'name' => 'Санкт-Петербург → Самара',
        'price' => 7200,
        'distance' => 1340,
        'from' => 'Санкт-Петербург',
        'to' => 'Самара',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [53.2001, 50.1500]]
    ],
    'moscow-volgograd' => [
        'name' => 'Москва → Волгоград',
        'price' => 5800,
        'distance' => 1073,
        'from' => 'Москва',
        'to' => 'Волгоград',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [48.7080, 44.5133]]
    ],
    'spb-ufa' => [
        'name' => 'Санкт-Петербург → Уфа',
        'price' => 8200,
        'distance' => 1520,
        'from' => 'Санкт-Петербург',
        'to' => 'Уфа',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [54.7388, 55.9721]]
    ],
    'moscow-krasnodar' => [
        'name' => 'Москва → Краснодар',
        'price' => 7800,
        'distance' => 1350,
        'from' => 'Москва',
        'to' => 'Краснодар',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [45.0355, 38.9753]]
    ],
    'spb-perm' => [
        'name' => 'Санкт-Петербург → Пермь',
        'price' => 9200,
        'distance' => 1640,
        'from' => 'Санкт-Петербург',
        'to' => 'Пермь',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [58.0105, 56.2502]]
    ],
    'moscow-voronezh' => [
        'name' => 'Москва → Воронеж',
        'price' => 3800,
        'distance' => 515,
        'from' => 'Москва',
        'to' => 'Воронеж',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [51.6720, 39.1843]]
    ],
    'spb-chelyabinsk' => [
        'name' => 'Санкт-Петербург → Челябинск',
        'price' => 9800,
        'distance' => 1777,
        'from' => 'Санкт-Петербург',
        'to' => 'Челябинск',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [55.1644, 61.4368]]
    ]
];

$loading_prices = [
    '2.5' => ['standard' => 10, 'no_tilt' => 30],
    '5' => ['standard' => 20, 'no_tilt' => 80],
    '10' => ['standard' => 40, 'no_tilt' => 150]
];

$additional_services = [
    'insurance' => ['name' => 'Расширенная страховка', 'price' => 10],
    'customs' => ['name' => 'Оформление грузов', 'price' => 20],
    'legal' => ['name' => 'Юридическое сопровождение', 'price' => 30]
];

// Функция расчета стоимости
function calculatePrice($order_data, $routes, $loading_prices, $additional_services) {
    $calculation = [
        'base_price' => 0,
        'loading_price' => 0,
        'services_price' => 0,
        'total_price' => 0
    ];
    
    if (isset($routes[$order_data['route']])) {
        $route_price = $routes[$order_data['route']]['price'];
        
        // Рассчитываем общую стоимость для всех контейнеров
        $total_base_price = 0;
        foreach ($order_data['containers'] as $container) {
            $weight_ratio = (float)$container['weight'] / 10;
            $total_base_price += $route_price * $weight_ratio;
        }
        $calculation['base_price'] = $total_base_price;
    }
    
    // Рассчитываем стоимость погрузки для всех контейнеров
    $total_loading_price = 0;
    foreach ($order_data['containers'] as $container) {
        if (isset($loading_prices[$container['weight']])) {
            $loading_price = $loading_prices[$container['weight']][$container['loading']];
            $total_loading_price += $loading_price;
        }
    }
    
    if ($order_data['weekend_loading']) {
        $total_loading_price *= 2;
    }
    
    $calculation['loading_price'] = $total_loading_price;
    
    foreach ($order_data['services'] as $service) {
        if (isset($additional_services[$service])) {
            $calculation['services_price'] += $additional_services[$service]['price'];
        }
    }
    
    $calculation['total_price'] = $calculation['base_price'] + $calculation['loading_price'] + $calculation['services_price'];
    
    return $calculation;
}

$calculation = calculatePrice($order_data, $routes, $loading_prices, $additional_services);

// Обработка подтверждения заказа
$order_confirmed = false;
$confirmation_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    try {
        // Генерация номера заказа
        $order_number = 'CRG-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Подготовка данных для записи
        $route_info = $routes[$order_data['route']];
        $total_weight = array_sum(array_column($order_data['containers'], 'weight'));
        $services_list = [];
        foreach ($order_data['services'] as $service) {
            if (isset($additional_services[$service])) {
                $services_list[] = $additional_services[$service]['name'];
            }
        }
        
        $order_info = [
            'order_number' => $order_number,
            'date' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'route' => $route_info['name'],
            'from' => $route_info['from'],
            'to' => $route_info['to'],
            'distance' => $route_info['distance'],
            'weight' => $total_weight,
            'cargo_type' => $order_data['cargo_description'] ?: 'Контейнерные перевозки',
            'loading' => $order_data['weekend_loading'],
            'additional_services' => implode(', ', $services_list),
            'customer_name' => $order_data['customer_name'],
            'customer_email' => $order_data['customer_email'],
            'customer_phone' => $order_data['customer_phone'],
            'company_name' => $order_data['company_name'],
            'container_count' => $order_data['container_count'],
            'containers' => $order_data['containers'],
            'weekend_loading' => $order_data['weekend_loading'],
            'services' => $order_data['services'],
            'cargo_description' => $order_data['cargo_description'],
            'total_price' => $calculation['total_price'],
            'base_cost' => $calculation['base_price'],
            'loading_cost' => $calculation['loading_price'],
            'additional_cost' => $calculation['services_price'],
            'total_cost' => $calculation['total_price']
        ];
        
        // Запись в файл
        $log_dir = 'backend/logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        
        $log_file = $log_dir . '/orders_' . date('Y-m') . '.log';
        $log_entry = date('Y-m-d H:i:s') . " | Order: {$order_number} | Customer: {$order_data['customer_name']} | Email: {$order_data['customer_email']} | Route: {$routes[$order_data['route']]['name']} | Total: {$calculation['total_price']}₽\n";
        
        file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
        
        // Подготовка email
        $email_subject = "Подтверждение заказа грузовых перевозок #{$order_number}";
        $email_body = "
        Уважаемый(ая) {$order_data['customer_name']}!
        
        Ваш заказ на грузовые перевозки успешно оформлен.
        
        Номер заказа: {$order_number}
        Дата: " . date('d.m.Y H:i') . "
        Маршрут: {$routes[$order_data['route']]['name']}
        Количество контейнеров: {$order_data['container_count']}
        Контейнеры: {$order_data['container_count']} шт
                        Детали контейнеров: " . implode(', ', array_map(function($c) { return $c['weight'] . 'т (' . ($c['loading'] === 'standard' ? 'стандарт' : 'не кантовать') . ')'; }, $order_data['containers'])) . "
        Погрузка в выходные: " . ($order_data['weekend_loading'] ? 'Да' : 'Нет') . "
        
        Общая стоимость: {$calculation['total_price']}₽
        
        С уважением,
        Команда Cargo Transport
        ";
        
        // Отправка email через EmailService
        try {
            $emailService = new EmailService();
            $emailSent = $emailService->sendOrderConfirmation($order_info);
        } catch (Exception $e) {
            error_log('Email service error: ' . $e->getMessage());
            $emailSent = false;
        }
        
        // Запись email в файл для демонстрации
        $email_log = $log_dir . '/emails_' . date('Y-m') . '.log';
        $email_entry = "\n=== EMAIL SENT ===\n";
        $email_entry .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $email_entry .= "To: {$order_data['customer_email']}\n";
        $email_entry .= "Subject: {$email_subject}\n";
        $email_entry .= "Body: {$email_body}\n";
        $email_entry .= "==================\n\n";
        
        file_put_contents($email_log, $email_entry, FILE_APPEND | LOCK_EX);
        
        $order_confirmed = true;
        $_SESSION['confirmed_order'] = $order_info;
        
        // Редирект для предотвращения повторной отправки формы
        header('Location: bill_2.php?confirmed=1');
        exit();
        
    } catch (Exception $e) {
        $confirmation_error = 'Ошибка при подтверждении заказа: ' . $e->getMessage();
    }
}

// Проверка параметра confirmed из URL
if (isset($_GET['confirmed']) && $_GET['confirmed'] == '1' && isset($_SESSION['confirmed_order'])) {
    $order_confirmed = true;
}
?>

<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Подтверждение заказа - Cargo Transport</title>
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
                    <?php if (!$order_confirmed): ?>
                    <a href="bill_1.php" class="hover:text-code-blue transition-colors font-mono">
                        <i class="fas fa-arrow-left mr-2"></i>back()
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="<?= $order_confirmed ? 'pt-24 pb-8' : 'pt-32 pb-20' ?> relative z-10">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <?php if ($order_confirmed): ?>
                    <!-- Success State -->
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-terminal-green/20 rounded-full mb-4 animate-bounce">
                            <i class="fas fa-check text-3xl text-terminal-green"></i>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-terminal-green to-code-blue font-mono">
                            orderConfirmed()
                        </h1>
                        <p class="text-gray-400 font-mono text-sm">// Заказ успешно подтвержден и отправлен в обработку</p>
                    </div>

                    <!-- Order Confirmation -->
                    <div class="terminal-window p-4 mb-4">
                        <div class="terminal-header px-3 py-2 flex items-center space-x-2 mb-4">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">order.confirmed</span>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="text-xl font-bold text-terminal-green mb-2 font-mono">
                                Заказ #<?php echo $_SESSION['confirmed_order']['order_number']; ?>
                            </h2>
                            <p class="text-gray-400 font-mono text-sm">Создан: <?php echo $_SESSION['confirmed_order']['date']; ?></p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <h3 class="text-lg font-bold text-code-blue mb-2 font-mono">
                                    <i class="fas fa-info-circle mr-2"></i>Детали заказа
                                </h3>
                                <div class="space-y-2 font-mono text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Маршрут:</span>
                                        <span class="text-white"><?php echo $_SESSION['confirmed_order']['route']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Контейнеры:</span>
                                        <span class="text-white"><?php echo $_SESSION['confirmed_order']['container_count']; ?> контейнеров</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Погрузка:</span>
                                        <span class="text-white">Смешанная погрузка</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Стоимость:</span>
                                        <span class="text-terminal-green font-bold"><?php echo $_SESSION['confirmed_order']['total_price']; ?>₽</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <h3 class="text-lg font-bold text-code-purple mb-2 font-mono">
                                    <i class="fas fa-user mr-2"></i>Заказчик
                                </h3>
                                <div class="space-y-2 font-mono text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Имя:</span>
                                        <span class="text-white"><?php echo $_SESSION['confirmed_order']['customer_name']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Email:</span>
                                        <span class="text-white"><?php echo $_SESSION['confirmed_order']['customer_email']; ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Телефон:</span>
                                        <span class="text-white"><?php echo $_SESSION['confirmed_order']['customer_phone']; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 p-3 bg-terminal-green/10 border border-terminal-green/30 rounded-lg">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-envelope text-terminal-green mr-2"></i>
                                <span class="font-mono text-terminal-green font-bold text-sm">Email отправлен</span>
                            </div>
                            <p class="text-gray-400 font-mono text-xs">
                                Подтверждение заказа отправлено на <?php echo $order_data['customer_email']; ?>
                            </p>
                        </div>

                        <div class="mt-2 p-3 bg-code-blue/10 border border-code-blue/30 rounded-lg">
                            <div class="flex items-center mb-1">
                                <i class="fas fa-file-alt text-code-blue mr-2"></i>
                                <span class="font-mono text-code-blue font-bold text-sm">Логирование</span>
                            </div>
                            <p class="text-gray-400 font-mono text-xs">
                                Информация о заказе сохранена в файл логов
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="basket.php" class="bg-gradient-to-r from-code-blue to-code-purple text-white px-6 py-2 rounded-lg font-semibold hover:shadow-lg hover:shadow-code-blue/25 transition-all duration-300 transform hover:scale-105 text-center font-mono text-sm">
                            <i class="fas fa-shopping-basket mr-2"></i>viewBasket()
                        </a>
                        <a href="order.php" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 text-center font-mono text-sm">
                            <i class="fas fa-plus mr-2"></i>newOrder()
                        </a>
                        <a href="../index.html" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-300 text-center font-mono text-sm">
                            <i class="fas fa-home mr-2"></i>home()
                        </a>
                    </div>

                <?php else: ?>
                    <!-- Confirmation State -->
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-400/20 rounded-full mb-4 animate-pulse">
                            <i class="fas fa-exclamation-triangle text-2xl text-yellow-400"></i>
                        </div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2 text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-red-400 font-mono">
                            confirmOrder()
                        </h1>
                        <p class="text-gray-400 font-mono text-sm">// Подтверждение заказа и отправка уведомлений</p>
                    </div>

                    <!-- Final Review -->
                    <div class="terminal-window p-4 mb-6">
                        <div class="terminal-header px-3 py-2 flex items-center space-x-2 mb-4">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">order.review</span>
                        </div>
                        
                        <h2 class="text-xl font-bold text-yellow-400 mb-4 font-mono">
                            <i class="fas fa-clipboard-check mr-2"></i>Финальная проверка заказа
                        </h2>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="bg-gray-900/30 p-3 rounded-lg">
                                    <h3 class="text-code-blue font-bold mb-2 font-mono text-sm">Маршрут и груз</h3>
                                    <div class="space-y-1 text-xs font-mono">
                                        <div>Маршрут: <?= htmlspecialchars($routes[$order_data['route']]['name']) ?></div>
                                        <div>Контейнеры: <?= $order_data['container_count'] ?> шт</div>
                                        <div>Детали: <?= implode(', ', array_map(function($c) { return $c['weight'] . 'т (' . ($c['loading'] === 'standard' ? 'стандарт' : 'не кантовать') . ')'; }, $order_data['containers'])) ?></div>
                                        <div>Выходные: <?= ($order_data['weekend_loading'] ? 'Да (+100%)' : 'Нет') ?></div>
                                    </div>
                                </div>

                                <div class="bg-gray-900/30 p-3 rounded-lg">
                                    <h3 class="text-code-purple font-bold mb-2 font-mono text-sm">Заказчик</h3>
                                    <div class="space-y-1 text-xs font-mono">
                                        <div>Имя: <?= htmlspecialchars($order_data['customer_name']) ?></div>
                                        <div>Email: <?= htmlspecialchars($order_data['customer_email']) ?></div>
                                        <div>Телефон: <?= htmlspecialchars($order_data['customer_phone']) ?></div>
                                        <?php if ($order_data['company_name']): ?>
                                        <div>Компания: <?= htmlspecialchars($order_data['company_name']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="bg-gray-900/30 p-3 rounded-lg">
                                    <h3 class="text-terminal-green font-bold mb-2 font-mono text-sm">Стоимость</h3>
                                    <div class="space-y-1 text-xs font-mono">
                                        <div class="flex justify-between">
                                            <span>Базовая цена:</span>
                                            <span><?= number_format($calculation['base_price'], 0, ',', ' ') ?>₽</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Погрузка:</span>
                                            <span><?= number_format($calculation['loading_price'], 0, ',', ' ') ?>₽</span>
                                        </div>
                                        <?php if ($calculation['services_price'] > 0): ?>
                                        <div class="flex justify-between">
                                            <span>Услуги:</span>
                                            <span><?= number_format($calculation['services_price'], 0, ',', ' ') ?>₽</span>
                                        </div>
                                        <?php endif; ?>
                                        <hr class="border-gray-600 my-1">
                                        <div class="flex justify-between text-sm font-bold text-terminal-green">
                                            <span>Итого:</span>
                                            <span><?= number_format($calculation['total_price'], 0, ',', ' ') ?>₽</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-yellow-400/10 border border-yellow-400/30 p-3 rounded-lg">
                                    <h3 class="text-yellow-400 font-bold mb-2 font-mono text-sm">
                                        <i class="fas fa-info-circle mr-1"></i>Что произойдет?
                                    </h3>
                                    <ul class="text-xs font-mono space-y-1 text-gray-300">
                                        <li>• Заказ получит номер</li>
                                        <li>• Email подтверждение</li>
                                        <li>• Сохранение в системе</li>
                                        <li>• Звонок менеджера</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($confirmation_error): ?>
                    <div class="bg-red-500/10 border border-red-500/30 p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                            <span class="text-red-400 font-mono"><?= htmlspecialchars($confirmation_error) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Confirmation Form -->
                    <form method="POST" class="text-center">
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="bill_1.php" class="bg-gray-700 hover:bg-gray-600 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-300 text-center font-mono">
                                <i class="fas fa-arrow-left mr-2"></i>back()
                            </a>
                            <button type="submit" name="confirm_order" class="bg-gradient-to-r from-terminal-green to-code-blue text-white px-12 py-3 rounded-lg font-semibold hover:shadow-lg hover:shadow-terminal-green/25 transition-all duration-300 transform hover:scale-105 font-mono">
                                <i class="fas fa-check mr-2"></i>confirmOrder()
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Проверка авторизации
        if (localStorage.getItem('cargo_auth') !== 'true') {
            window.location.href = 'index.html';
        }

        <?php if ($order_confirmed): ?>
        // Анимация успешного подтверждения
        document.addEventListener('DOMContentLoaded', function() {
            // Прокрутка вверх при успешном подтверждении
            window.scrollTo({ top: 0, behavior: 'smooth' });
            
            // Конфетти эффект
            setTimeout(() => {
                for (let i = 0; i < 50; i++) {
                    createConfetti();
                }
            }, 500);
        });

        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = ['#58a6ff', '#00ff00', '#a855f7', '#ffaa00'][Math.floor(Math.random() * 4)];
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.zIndex = '1000';
            confetti.style.borderRadius = '50%';
            
            document.body.appendChild(confetti);
            
            const animation = confetti.animate([
                { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                { transform: 'translateY(100vh) rotate(360deg)', opacity: 0 }
            ], {
                duration: 3000,
                easing: 'linear'
            });
            
            animation.onfinish = () => confetti.remove();
        }
        <?php endif; ?>
    </script>
</body>
</html>