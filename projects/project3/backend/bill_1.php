<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['cargo_auth']) || $_SESSION['cargo_auth'] !== true) {
    header('Location: ../index.html');
    exit();
}

// Проверка наличия данных формы
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST)) {
    $_SESSION['bill_error'] = 'Доступ к странице расчета возможен только после заполнения формы заказа';
    header('Location: order.php');
    exit();
}

// Проверка данных формы
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: order.php');
    exit();
}

// Базовые цены за 10т
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

// Цены на погрузку
$loading_prices = [
    '2.5' => ['standard' => 10, 'no_tilt' => 30],
    '5' => ['standard' => 20, 'no_tilt' => 80],
    '10' => ['standard' => 40, 'no_tilt' => 150]
];

// Дополнительные услуги
$additional_services = [
    'insurance' => ['name' => 'Расширенная страховка', 'price' => 10],
    'customs' => ['name' => 'Оформление грузов', 'price' => 20],
    'legal' => ['name' => 'Юридическое сопровождение', 'price' => 30]
];

// Валидация и получение данных из формы
$errors = [];

// Отладочная информация - что приходит в POST
file_put_contents('debug_post.txt', 'POST data: ' . print_r($_POST, true) . "\n", FILE_APPEND);

// Проверка обязательных полей
if (empty($_POST['route']) || !isset($routes[$_POST['route']])) {
    $errors[] = 'Выберите корректный маршрут';
}

$container_count = (int)($_POST['container_count'] ?? 0);
if ($container_count < 1 || $container_count > 10) {
    $errors[] = 'Количество контейнеров должно быть от 1 до 10';
}

// Проверка веса и типа погрузки для каждого контейнера
for ($i = 1; $i <= $container_count; $i++) {
    $weight_field = "container_{$i}_weight";
    $loading_field = "container_{$i}_loading";
    
    if (empty($_POST[$weight_field]) || !isset($loading_prices[$_POST[$weight_field]])) {
        $errors[] = "Выберите корректный вес для контейнера {$i}";
    }
    
    if (empty($_POST[$loading_field]) || !in_array($_POST[$loading_field], ['standard', 'no_tilt'])) {
        $errors[] = "Выберите тип погрузки для контейнера {$i}";
    }
}

$customer_name = trim($_POST['customer_name'] ?? '');
if (empty($customer_name)) {
    $errors[] = 'Введите имя клиента';
}

$customer_email = trim($_POST['customer_email'] ?? '');
if (empty($customer_email) || strpos($customer_email, '@') === false) {
    $errors[] = 'Введите email адрес';
}

$customer_phone = trim($_POST['customer_phone'] ?? '');
if (empty($customer_phone)) {
    $errors[] = 'Введите номер телефона';
}

// Если есть ошибки, перенаправляем обратно
if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: order.php?error=validation');
    exit();
}

// Собираем данные контейнеров
$containers = [];
for ($i = 1; $i <= $container_count; $i++) {
    $containers[$i] = [
        'weight' => $_POST["container_{$i}_weight"],
        'loading' => $_POST["container_{$i}_loading"]
    ];
}

$order_data = [
    'route' => $_POST['route'],
    'container_count' => $container_count,
    'containers' => $containers,
    'weekend_loading' => isset($_POST['weekend_loading']),
    'services' => $_POST['services'] ?? [],
    'customer_name' => $customer_name,
    'customer_email' => $customer_email,
    'customer_phone' => $customer_phone,
    'company_name' => trim($_POST['company_name'] ?? ''),
    'cargo_description' => trim($_POST['cargo_description'] ?? '')
];

// Сохранение данных в сессию
$_SESSION['order_data'] = $order_data;

// Расчет стоимости
function calculatePrice($order_data, $routes, $loading_prices, $additional_services) {
    $calculation = [
        'base_price' => 0,
        'loading_price' => 0,
        'weekend_multiplier' => 1,
        'services_price' => 0,
        'total_price' => 0,
        'containers_breakdown' => []
    ];
    
    // Расчет для каждого контейнера
    foreach ($order_data['containers'] as $container_num => $container) {
        $container_calc = [
            'base_price' => 0,
            'loading_price' => 0
        ];
        
        // Базовая цена маршрута для контейнера
        if (isset($routes[$order_data['route']])) {
            $route_price = $routes[$order_data['route']]['price'];
            // Пересчет цены в зависимости от веса контейнера
            $weight_ratio = (float)$container['weight'] / 10;
            $container_calc['base_price'] = $route_price * $weight_ratio;
            $calculation['base_price'] += $container_calc['base_price'];
        }
        
        // Цена погрузки для контейнера
        if (isset($loading_prices[$container['weight']])) {
            $loading_price = $loading_prices[$container['weight']][$container['loading']];
            $container_calc['loading_price'] = $loading_price;
            
            // Удвоение цены в выходные
            if ($order_data['weekend_loading']) {
                $calculation['weekend_multiplier'] = 2;
                $container_calc['loading_price'] *= 2;
            }
            
            $calculation['loading_price'] += $container_calc['loading_price'];
        }
        
        $calculation['containers_breakdown'][$container_num] = $container_calc;
    }
    
    // Дополнительные услуги
    foreach ($order_data['services'] as $service) {
        if (isset($additional_services[$service])) {
            $calculation['services_price'] += $additional_services[$service]['price'];
        }
    }
    
    // Общая стоимость
    $calculation['total_price'] = $calculation['base_price'] + $calculation['loading_price'] + $calculation['services_price'];
    
    return $calculation;
}

$calculation = calculatePrice($order_data, $routes, $loading_prices, $additional_services);

// Сохранение заказа в лог
$order_number = 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
$log_dir = __DIR__ . '/logs';
$log_file = $log_dir . '/orders_' . date('Y-m') . '.log';

// Создание директории если не существует
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0755, true);
}

// Запись в лог
$log_entry = sprintf(
    "%s | Order: %s | Customer: %s | Email: %s | Route: %s | Total: %s₽\n",
    date('Y-m-d H:i:s'),
    $order_number,
    $order_data['customer_name'],
    $order_data['customer_email'],
    $routes[$order_data['route']]['name'],
    number_format($calculation['total_price'], 0, '.', ' ')
);

$log_success = @file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

if ($log_success !== false) {
    $_SESSION['order_success'] = "Заказ #{$order_number} успешно создан и сохранен";
} else {
    $_SESSION['order_warning'] = "Заказ создан, но возникла проблема с сохранением в систему";
}
?>

<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расчет стоимости - Cargo Transport</title>
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
                        <i class="fas fa-arrow-left mr-2"></i>back()
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['order_success'])): ?>
    <div class="pt-20 pb-4">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-2"></i>
                        <span class="text-green-400 font-mono"><?php echo htmlspecialchars($_SESSION['order_success']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['order_success']); endif; ?>

    <?php if (isset($_SESSION['order_warning'])): ?>
    <div class="pt-4 pb-4">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-triangle text-yellow-400 mr-2"></i>
                        <span class="text-yellow-400 font-mono"><?php echo htmlspecialchars($_SESSION['order_warning']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['order_warning']); endif; ?>

    <!-- Main Content -->
    <main id="bill_1" class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <div class="max-w-6xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-terminal-green/20 rounded-full mb-6 animate-pulse">
                        <i class="fas fa-calculator text-3xl text-terminal-green"></i>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-terminal-green to-code-blue font-mono">
                        calculatePrice()
                    </h1>
                    <p class="text-gray-400 font-mono">// Расчет стоимости грузовых перевозок</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Order Details -->
                    <div class="space-y-6">
                        <!-- Route Info -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">order.details</span>
                            </div>
                            <h3 class="text-xl font-bold text-code-blue mb-4 font-mono">
                                <i class="fas fa-route mr-2"></i>Маршрут
                            </h3>
                            <div class="space-y-2 font-mono text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">route:</span>
                                    <span class="text-white"><?= $routes[$order_data['route']]['name'] ?? 'Не выбран' ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">basePrice:</span>
                                    <span class="text-terminal-green"><?= $routes[$order_data['route']]['price'] ?? 0 ?>₽/10т</span>
                                </div>
                            </div>
                        </div>

                        <!-- Container Info -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">container.params</span>
                            </div>
                            <h3 class="text-xl font-bold text-code-purple mb-4 font-mono">
                                <i class="fas fa-boxes mr-2"></i>Контейнеры
                            </h3>
                            <div class="space-y-2 font-mono text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">count:</span>
                                    <span class="text-white"><?= $order_data['container_count'] ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">containers:</span>
                                    <span class="text-white"><?= implode(', ', array_map(function($c) { return $c['weight'] . 'т'; }, $order_data['containers'])) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">loadingTypes:</span>
                                    <span class="text-white"><?= implode(', ', array_map(function($c) { return $c['loading'] === 'standard' ? 'стандарт' : 'не кантовать'; }, $order_data['containers'])) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">weekendLoading:</span>
                                    <span class="<?= $order_data['weekend_loading'] ? 'text-yellow-400' : 'text-gray-500' ?>"><?= $order_data['weekend_loading'] ? 'true' : 'false' ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Services -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">services.list</span>
                            </div>
                            <h3 class="text-xl font-bold text-yellow-400 mb-4 font-mono">
                                <i class="fas fa-plus-circle mr-2"></i>Дополнительные услуги
                            </h3>
                            <?php if (empty($order_data['services'])): ?>
                                <p class="text-gray-400 font-mono text-sm">// Дополнительные услуги не выбраны</p>
                            <?php else: ?>
                                <div class="space-y-2 font-mono text-sm">
                                    <?php foreach ($order_data['services'] as $service): ?>
                                        <?php if (isset($additional_services[$service])): ?>
                                        <div class="flex justify-between">
                                            <span class="text-white"><?= $additional_services[$service]['name'] ?></span>
                                            <span class="text-terminal-green">+<?= $additional_services[$service]['price'] ?>₽</span>
                                        </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Customer Info -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">customer.data</span>
                            </div>
                            <h3 class="text-xl font-bold text-red-400 mb-4 font-mono">
                                <i class="fas fa-user mr-2"></i>Заказчик
                            </h3>
                            <div class="space-y-2 font-mono text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-400">name:</span>
                                    <span class="text-white"><?= htmlspecialchars($order_data['customer_name']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">email:</span>
                                    <span class="text-white"><?= htmlspecialchars($order_data['customer_email']) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">phone:</span>
                                    <span class="text-white"><?= htmlspecialchars($order_data['customer_phone']) ?></span>
                                </div>
                                <?php if ($order_data['company_name']): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-400">company:</span>
                                    <span class="text-white"><?= htmlspecialchars($order_data['company_name']) ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Price Calculation -->
                    <div class="space-y-6">
                        <!-- Calculation Breakdown -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">price.calculation</span>
                            </div>
                            <h3 class="text-2xl font-bold text-terminal-green mb-6 font-mono">
                                <i class="fas fa-calculator mr-2"></i>Расчет стоимости
                            </h3>
                            
                            <div class="space-y-4 font-mono">
                                <!-- Base Price -->
                                <div class="bg-gray-900/30 p-4 rounded-lg border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-400">basePrice:</span>
                                        <span class="text-white"><?= number_format($calculation['base_price'], 0) ?>₽</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= $routes[$order_data['route']]['price'] ?? 0 ?>₽ × (<?= implode(' + ', array_map(function($c) { return $c['weight'] . '/10т'; }, $order_data['containers'])) ?>)
                                    </div>
                                </div>

                                <!-- Loading Price -->
                                <div class="bg-gray-900/30 p-4 rounded-lg border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-400">loadingPrice:</span>
                                        <span class="text-white"><?= number_format($calculation['loading_price'], 0) ?>₽</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?= implode(' + ', array_map(function($c) use ($loading_prices) { return ($loading_prices[$c['weight']][$c['loading']] ?? 0) . '₽'; }, $order_data['containers'])) ?>
                                        <?php if ($order_data['weekend_loading']): ?>
                                            × 2 (выходные)
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Services Price -->
                                <?php if ($calculation['services_price'] > 0): ?>
                                <div class="bg-gray-900/30 p-4 rounded-lg border border-gray-700">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-gray-400">servicesPrice:</span>
                                        <span class="text-white"><?= number_format($calculation['services_price'], 0) ?>₽</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Дополнительные услуги
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Total -->
                                <div class="bg-gradient-to-r from-terminal-green/20 to-code-blue/20 p-6 rounded-lg border border-terminal-green/50">
                                    <div class="flex justify-between items-center">
                                        <span class="text-terminal-green text-xl font-bold">totalPrice:</span>
                                        <span class="text-terminal-green text-3xl font-bold"><?= number_format($calculation['total_price'], 0) ?>₽</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Code Preview -->
                        <div class="terminal-window p-6">
                            <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm text-gray-400 ml-4 font-mono">calculation.js</span>
                            </div>
                            <h3 class="text-lg font-bold text-code-purple mb-4 font-mono">
                                <i class="fas fa-code mr-2"></i>Код расчета
                            </h3>
                            <div class="bg-black/50 p-4 rounded-lg font-mono text-sm overflow-x-auto">
                                <div class="text-gray-400">// Расчет общей стоимости</div>
                                <div class="text-code-blue">function</div> <div class="text-yellow-400">calculateTotal</div><div class="text-white">() {</div>
                                <div class="ml-4 text-white">const basePrice = <?= $calculation['base_price'] ?>;</div>
                                <div class="ml-4 text-white">const loadingPrice = <?= $calculation['loading_price'] ?>;</div>
                                <div class="ml-4 text-white">const servicesPrice = <?= $calculation['services_price'] ?>;</div>
                                <div class="ml-4 text-code-blue">return</div> <div class="text-white">basePrice + loadingPrice + servicesPrice;</div>
                                <div class="text-white">}</div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a href="order.php" class="flex-1 bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 text-center font-mono">
                            <i class="fas fa-arrow-left mr-2"></i>back()
                        </a>
                        <a href="bill_2.php" class="flex-1 bg-gradient-to-r from-code-blue to-code-purple text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg hover:shadow-code-blue/25 transition-all duration-300 transform hover:scale-105 text-center font-mono">
                            <i class="fas fa-arrow-right mr-2"></i>continue()
                        </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Проверка авторизации
        if (localStorage.getItem('cargo_auth') !== 'true') {
            window.location.href = 'index.html';
        }

        // Анимация появления элементов
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.terminal-window');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'all 0.5s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>