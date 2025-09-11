<?php
session_start();

// Проверка авторизации
if (!isset($_SESSION['cargo_auth']) || $_SESSION['cargo_auth'] !== true) {
    header('Location: ../index.html');
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
    ],
    'moscow-saratov' => [
        'name' => 'Москва → Саратов',
        'price' => 5200,
        'distance' => 858,
        'from' => 'Москва',
        'to' => 'Саратов',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [51.5406, 46.0086]]
    ],
    
    // Долгие маршруты (свыше 2000 км)
    'spb-omsk' => [
        'name' => 'Санкт-Петербург → Омск',
        'price' => 12800,
        'distance' => 2555,
        'from' => 'Санкт-Петербург',
        'to' => 'Омск',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [54.9885, 73.3242]]
    ],
    'moscow-tula' => [
        'name' => 'Москва → Тула',
        'price' => 2200,
        'distance' => 193,
        'from' => 'Москва',
        'to' => 'Тула',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [54.1961, 37.6182]]
    ],
    'spb-tyumen' => [
        'name' => 'Санкт-Петербург → Тюмень',
        'price' => 11200,
        'distance' => 2144,
        'from' => 'Санкт-Петербург',
        'to' => 'Тюмень',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [57.1522, 65.5272]]
    ],
    'moscow-yaroslavl' => [
        'name' => 'Москва → Ярославль',
        'price' => 2800,
        'distance' => 282,
        'from' => 'Москва',
        'to' => 'Ярославль',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [57.6261, 39.8845]]
    ],
    'spb-irkutsk' => [
        'name' => 'Санкт-Петербург → Иркутск',
        'price' => 18500,
        'distance' => 5191,
        'from' => 'Санкт-Петербург',
        'to' => 'Иркутск',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [52.2978, 104.2964]]
    ],
    'moscow-kaluga' => [
        'name' => 'Москва → Калуга',
        'price' => 2400,
        'distance' => 188,
        'from' => 'Москва',
        'to' => 'Калуга',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [54.5293, 36.2754]]
    ],
    'spb-vladivostok' => [
        'name' => 'Санкт-Петербург → Владивосток',
        'price' => 28500,
        'distance' => 9259,
        'from' => 'Санкт-Петербург',
        'to' => 'Владивосток',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [43.1056, 131.8735]]
    ],
    'moscow-sochi' => [
        'name' => 'Москва → Сочи',
        'price' => 9200,
        'distance' => 1623,
        'from' => 'Москва',
        'to' => 'Сочи',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [43.6028, 39.7342]]
    ],
    'spb-murmansk' => [
        'name' => 'Санкт-Петербург → Мурманск',
        'price' => 6800,
        'distance' => 1448,
        'from' => 'Санкт-Петербург',
        'to' => 'Мурманск',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [68.9585, 33.0827]]
    ],
    'moscow-minsk' => [
        'name' => 'Москва → Минск',
        'price' => 4200,
        'distance' => 700,
        'from' => 'Москва',
        'to' => 'Минск',
        'coords' => ['from' => [55.7558, 37.6176], 'to' => [53.9006, 27.5590]]
    ],
    'spb-helsinki' => [
        'name' => 'Санкт-Петербург → Хельсинки',
        'price' => 3200,
        'distance' => 388,
        'from' => 'Санкт-Петербург',
        'to' => 'Хельсинки',
        'coords' => ['from' => [59.9311, 30.3609], 'to' => [60.1699, 24.9384]]
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

// Восстановление данных формы из сессии
$form_data = $_SESSION['form_data'] ?? [];
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
?>

<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание заказа - Cargo Transport</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
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
    <style>
        /* Стили для валидации */
        .field-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 1px #ef4444 !important;
            animation: shake 0.5s ease-in-out;
        }
        
        .validation-error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 6px;
            backdrop-filter: blur(10px);
        }
        
        .validation-error i {
            color: #ef4444;
            animation: pulse 2s infinite;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        /* Улучшенные стили для полей формы */
        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #58a6ff;
            box-shadow: 0 0 0 2px rgba(88, 166, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        /* Стили для успешной валидации */
        .field-success {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 1px #10b981 !important;
        }
        
        .validation-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 6px;
            color: #10b981;
        }
    </style>
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
                    <a href="../index.html" class="hover:text-code-blue transition-colors font-mono">
                        <i class="fas fa-arrow-left mr-2"></i>return home()
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['form_errors']) && !empty($_SESSION['form_errors'])): ?>
        <div id="bill_1" class="pt-20 pb-4">
            <div class="container mx-auto px-6">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                            <h3 class="text-red-400 font-semibold font-mono">Validation Error:</h3>
                        </div>
                        <ul class="list-disc list-inside text-red-300 font-mono text-sm">
                            <?php foreach ($_SESSION['form_errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            unset($_SESSION['form_errors']);
        endif; ?>

        <?php if (isset($_SESSION['bill_error'])): ?>
        <div class="pt-4 pb-4">
            <div class="container mx-auto px-6">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-orange-500/20 border border-orange-500/50 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-orange-400 mr-2"></i>
                            <span class="text-orange-400 font-mono"><?php echo htmlspecialchars($_SESSION['bill_error']); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php unset($_SESSION['bill_error']); endif; ?>

    <!-- Main Content -->
    <main class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-code-blue/20 rounded-full mb-6 animate-pulse">
                        <i class="fas fa-plus text-3xl text-code-blue"></i>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-bold mb-4 text-transparent bg-clip-text bg-gradient-to-r from-code-blue to-code-purple font-mono">
                        createOrder()
                    </h1>
                    <p class="text-gray-400 font-mono">// Создание нового заказа на грузовые перевозки</p>
                </div>

                <!-- Order Form -->
                <form id="orderForm" action="bill_1.php#bill_1" method="POST" class="space-y-8">
                    <!-- Route Selection -->
                    <div class="terminal-window p-6">
                        <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">route.select</span>
                        </div>
                        <h3 class="text-2xl font-bold text-code-blue mb-6 font-mono">
                            <i class="fas fa-route mr-3"></i>Выбор маршрута
                        </h3>
                        
                        <!-- Поиск маршрутов -->
                        <div class="mb-6">
                            <div class="relative">
                                <input type="text" id="routeSearch" placeholder="Поиск маршрутов..." class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 pl-10 text-white font-mono focus:border-code-blue focus:outline-none">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <div class="mt-2 text-sm text-gray-400 font-mono">
                                Найдено: <span id="routeCount"><?= count($routes) ?></span> маршрутов
                            </div>
                        </div>
                        
                        <!-- Сетка маршрутов -->
                        <div id="routesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            <?php foreach ($routes as $key => $route): ?>
                            <label class="cursor-pointer block">
                                <input type="radio" name="route" value="<?= $key ?>" class="sr-only" required>
                                <div class="route-card p-4 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 relative h-full">
                                    <div class="absolute top-2 right-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green text-xl"></i>
                                    </div>
                                    <div class="absolute top-2 left-2 opacity-0 transition-opacity duration-300">
                                        <span class="bg-terminal-green text-black text-xs font-bold px-2 py-1 rounded font-mono">ВЫБРАНО</span>
                                    </div>
                                    <div class="pt-2">
                                        <div class="font-mono text-white font-semibold text-sm mb-2"><?= $route['name'] ?></div>
                                        <div class="text-xs text-gray-500 font-mono mb-1">От: <?= $route['from'] ?? 'N/A' ?></div>
                                        <div class="text-xs text-gray-500 font-mono mb-2">До: <?= $route['to'] ?? 'N/A' ?></div>
                                        <div class="text-xs text-code-blue font-mono mb-3">Расстояние: <?= $route['distance'] ?? 'N/A' ?> км</div>
                                        <div class="flex justify-between items-center">
                                            <div class="text-terminal-green font-mono text-lg font-bold"><?= $route['price'] ?>₽</div>
                                            <div class="text-xs text-gray-400 font-mono">/10т</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Карта маршрута (скрыта по умолчанию) -->
                        <div id="routeMapContainer" class="hidden bg-gray-900/50 border border-gray-700 rounded-lg p-4 mt-6">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-lg font-mono text-code-blue">
                                    <i class="fas fa-map-marked-alt mr-2"></i>Карта маршрута
                                </h4>
                                <div class="flex items-center space-x-4">
                                    <div class="text-sm text-gray-400 font-mono" id="mapRouteInfo">
                                        <span id="selectedRouteName">Выберите маршрут</span>
                                    </div>
                                    <button type="button" id="closeMapBtn" class="text-gray-400 hover:text-white transition-colors">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="routeMap" class="w-full h-80 bg-gray-800 rounded border border-gray-600 relative overflow-hidden">
                                <!-- Leaflet карта будет инициализирована здесь -->
                            </div>
                            <div id="routeInfo" class="mt-4 p-3 bg-gray-800/50 rounded border border-gray-700">
                                <div class="text-sm text-gray-400 font-mono">Информация о маршруте появится после выбора</div>
                            </div>
                        </div>
                    </div>

                    <!-- Container Details -->
                    <div class="terminal-window p-6">
                        <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">container.config</span>
                        </div>
                        <h3 class="text-2xl font-bold text-code-purple mb-6 font-mono">
                            <i class="fas fa-boxes mr-3"></i>Параметры контейнеров
                        </h3>
                        
                        <!-- Container Count Section -->
                        <div class="mb-8">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-gray-400 text-sm font-mono">containerCount:</label>
                                <div class="flex items-center space-x-3">
                                    <button type="button" id="decreaseCount" class="w-8 h-8 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center transition-colors">
                                        <i class="fas fa-minus text-white text-xs"></i>
                                    </button>
                                    <span id="containerCountDisplay" class="text-2xl font-bold text-terminal-green font-mono min-w-[3rem] text-center">1</span>
                                    <button type="button" id="increaseCount" class="w-8 h-8 bg-gray-700 hover:bg-gray-600 rounded-full flex items-center justify-center transition-colors">
                                        <i class="fas fa-plus text-white text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="container_count" id="containerCountInput" value="1" required>
                            
                            <!-- Progress Bar for Container Count -->
                            <div class="relative">
                                <div class="w-full bg-gray-800 rounded-full h-3 mb-2">
                                    <div id="containerCountProgress" class="bg-gradient-to-r from-code-blue to-terminal-green h-3 rounded-full transition-all duration-300" style="width: 10%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-500 font-mono">
                                    <span>1</span>
                                    <span class="text-gray-400">Контейнеров: <span id="containerCountText">1</span>/10</span>
                                    <span>10</span>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Container Configuration -->
                        <div id="containersConfig" class="mb-8">
                            <!-- Containers will be dynamically generated here -->
                        </div>
                        
                        <!-- Total Weight Summary -->
                        <div class="bg-gray-900/50 rounded-lg p-4 mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-gray-400 text-sm font-mono">Общий вес всех контейнеров:</span>
                                <span id="totalWeight" class="text-terminal-green font-mono font-bold">0т</span>
                            </div>
                            <div class="w-full bg-gray-800 rounded-full h-2">
                                <div id="weightProgress" class="bg-gradient-to-r from-terminal-green to-yellow-400 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 font-mono mt-1">
                                <span>0т</span>
                                <span>Максимум: 100т</span>
                            </div>
                        </div>

                        <!-- Global Weekend Loading Toggle -->
                        <div class="mb-6">
                            <div class="bg-gray-900/30 rounded-lg p-4 border border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-weekend text-yellow-400 text-xl mr-3"></i>
                                        <div>
                                            <div class="font-mono text-white font-semibold">weekendLoading (для всех контейнеров)</div>
                                            <div class="text-xs text-gray-400 font-mono">Погрузка в выходные дни</div>
                                        </div>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="weekend_loading" value="1" class="sr-only weekend-toggle">
                                        <div class="w-11 h-6 bg-gray-700 rounded-full peer peer-checked:bg-terminal-green transition-colors duration-300">
                                            <div class="w-5 h-5 bg-white rounded-full shadow transform transition-transform duration-300 translate-x-0.5 peer-checked:translate-x-5"></div>
                                        </div>
                                        <span class="ml-3 text-yellow-400 font-mono text-sm">(×2 цена)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Live Price Calculator -->
                        <div class="bg-gradient-to-r from-gray-900/50 to-gray-800/50 rounded-lg p-4 border border-code-blue/30">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-calculator text-code-blue text-xl mr-3"></i>
                                    <div>
                                        <div class="font-mono text-white font-semibold">Стоимость погрузки</div>
                                        <div class="text-xs text-gray-400 font-mono">Динамический расчет</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div id="loadingPrice" class="text-2xl font-bold text-terminal-green font-mono">0₽</div>
                                    <div class="text-xs text-gray-400 font-mono">за погрузку</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Services -->
                    <div class="terminal-window p-6">
                        <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">services.additional</span>
                        </div>
                        <h3 class="text-2xl font-bold text-yellow-400 mb-6 font-mono">
                            <i class="fas fa-plus-circle mr-3"></i>Дополнительные услуги
                        </h3>
                        
                        <div class="space-y-4">
                            <?php foreach ($additional_services as $key => $service): ?>
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="services[]" value="<?= $key ?>" class="sr-only">
                                <div class="checkbox-custom w-5 h-5 border border-gray-700 rounded mr-3 flex items-center justify-center">
                                    <i class="fas fa-check text-terminal-green hidden"></i>
                                </div>
                                <span class="font-mono text-white flex-1"><?= $service['name'] ?></span>
                                <span class="text-terminal-green font-mono">+<?= $service['price'] ?>₽</span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="terminal-window p-6">
                        <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-sm text-gray-400 ml-4 font-mono">customer.info</span>
                        </div>
                        <h3 class="text-2xl font-bold text-red-400 mb-6 font-mono">
                            <i class="fas fa-user mr-3"></i>Информация о заказчике
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2">customerName:</label>
                                <input type="text" name="customer_name" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono focus:border-code-blue focus:outline-none" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2">customerEmail:</label>
                                <input type="email" name="customer_email" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono focus:border-code-blue focus:outline-none" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2">customerPhone:</label>
                                <input type="tel" name="customer_phone" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono focus:border-code-blue focus:outline-none" required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-400 text-sm font-mono mb-2">companyName:</label>
                                <input type="text" name="company_name" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono focus:border-code-blue focus:outline-none">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label class="block text-gray-400 text-sm font-mono mb-2">cargoDescription:</label>
                            <textarea name="cargo_description" rows="4" class="w-full bg-gray-900/50 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono focus:border-code-blue focus:outline-none" placeholder="Описание груза..."></textarea>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="inline-flex items-center space-x-3 bg-gradient-to-r from-code-blue to-code-purple text-white px-12 py-4 rounded-lg font-semibold hover:shadow-lg hover:shadow-code-blue/25 transition-all duration-300 transform hover:scale-105 font-mono text-lg">
                            <i class="fas fa-calculator"></i>
                            <span>calculatePrice()</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Проверка авторизации
        if (localStorage.getItem('cargo_auth') !== 'true') {
            window.location.href = '../index.html';
        }

        // Функции для валидации
        function clearValidationErrors() {
            // Удаляем все существующие сообщения об ошибках и успехах
            const messages = document.querySelectorAll('.validation-error, .validation-success');
            messages.forEach(message => message.remove());
            
            // Убираем классы с полей
            const fields = document.querySelectorAll('.field-error, .field-success');
            fields.forEach(field => {
                field.classList.remove('field-error', 'field-success');
            });
        }
        
        function showFieldSuccess(fieldId, message) {
            let field = getFieldById(fieldId);
            if (!field) return;
            
            // Добавляем класс успеха к полю
            field.classList.remove('field-error');
            field.classList.add('field-success');
            
            // Создаем элемент сообщения об успехе
            const successDiv = document.createElement('div');
            successDiv.className = 'validation-success text-emerald-400 text-sm mt-2 flex items-center';
            successDiv.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
            
            // Вставляем сообщение после поля
            const container = field.closest('.mb-4') || field.closest('.mb-6') || field.closest('.container-block') || field.parentElement;
            if (container) {
                container.appendChild(successDiv);
            }
        }
        
        function getFieldById(fieldId) {
            if (fieldId === 'routesList') {
                return document.getElementById('routesList');
            } else if (fieldId === 'containerCountInput') {
                return document.getElementById('containerCountInput');
            } else if (fieldId === 'customerName') {
                return document.querySelector('input[name="customer_name"]');
            } else if (fieldId === 'customerEmail') {
                return document.querySelector('input[name="customer_email"]');
            } else if (fieldId === 'customerPhone') {
                return document.querySelector('input[name="customer_phone"]');
            } else if (fieldId === 'companyName') {
                return document.querySelector('input[name="company_name"]');
            } else if (fieldId === 'cargoDescription') {
                return document.querySelector('textarea[name="cargo_description"]');
            } else if (fieldId.startsWith('container_')) {
                const containerNum = fieldId.split('_')[1];
                return document.querySelector(`#container-${containerNum}`);
            }
            return null;
        }
        
        function showFieldError(fieldId, message) {
             let field = getFieldById(fieldId);
             if (!field) {
                 console.warn('Field not found:', fieldId);
                 return;
             }
            
            // Добавляем класс ошибки к полю
            field.classList.add('field-error');
            
            // Создаем элемент сообщения об ошибке
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error text-red-400 text-sm mt-2 flex items-center animate-pulse';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
            
            // Вставляем сообщение после поля
            const container = field.closest('.mb-4') || field.closest('.mb-6') || field.closest('.container-block') || field.parentElement;
            if (container) {
                container.appendChild(errorDiv);
            } else {
                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
            
            // Прокручиваем к первой ошибке
            if (document.querySelectorAll('.validation-error').length === 1) {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Инициализация после загрузки DOM
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
        });
        
        function initializeForm() {

        // Данные маршрутов
        const routesData = <?= json_encode($routes) ?>;

        // Переменная для хранения карты
        let routeMap = null;
        let currentRoute = null;

        // Функция отображения карты маршрута
        function showRouteMap(routeKey) {
            const route = routesData[routeKey];
            if (!route) return;

            const mapContainer = document.getElementById('routeMapContainer');
            const routeName = document.getElementById('selectedRouteName');
            const mapRouteInfo = document.getElementById('mapRouteInfo');
            const routeInfo = document.getElementById('routeInfo');
            const mapDiv = document.getElementById('routeMap');

            // Проверяем существование элементов перед обновлением
            if (!mapContainer || !mapRouteInfo || !routeInfo || !mapDiv) {
                console.error('Не найдены необходимые элементы для отображения карты');
                return;
            }

            // Обновляем информацию о маршруте
            if (routeName) {
                routeName.textContent = route.name;
            }
            mapRouteInfo.innerHTML = `<span id="selectedRouteName">${route.name}</span>`;
            
            // Добавляем информацию о расстоянии
            const distanceInfo = document.createElement('div');
            distanceInfo.className = 'text-xs text-gray-500 mt-1';
            distanceInfo.innerHTML = `<i class="fas fa-road mr-1"></i>${route.distance} км`;
            mapRouteInfo.appendChild(distanceInfo);
            
            // Очищаем плейсхолдер
            const placeholder = mapDiv.querySelector('.absolute');
            if (placeholder) {
                placeholder.remove();
            }
            
            // Информация о маршруте
            routeInfo.innerHTML = `
                <div class="grid grid-cols-2 gap-4 text-sm font-mono">
                    <div><span class="text-code-blue">Откуда:</span> <span class="text-white">${route.from}</span></div>
                    <div><span class="text-code-blue">Куда:</span> <span class="text-white">${route.to}</span></div>
                    <div><span class="text-code-blue">Расстояние:</span> <span class="text-terminal-green">${route.distance} км</span></div>
                    <div><span class="text-code-blue">Базовая цена:</span> <span class="text-terminal-green">${route.price}₽/10т</span></div>
                </div>
            `;

            // Инициализируем карту, если еще не создана
            if (!routeMap) {
                routeMap = L.map('routeMap', {
                    zoomControl: true,
                    scrollWheelZoom: true,
                    doubleClickZoom: true,
                    boxZoom: true,
                    keyboard: true,
                    dragging: true,
                    touchZoom: true
                });

                // Добавляем тайлы OpenStreetMap
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 18
                }).addTo(routeMap);
            }

            // Очищаем предыдущий маршрут
            if (currentRoute) {
                routeMap.removeLayer(currentRoute);
            }

            // Координаты начальной и конечной точек
            const startCoords = route.coords.from;
            const endCoords = route.coords.to;

            // Создаем маркеры
            const startMarker = L.marker(startCoords, {
                icon: L.divIcon({
                    className: 'custom-marker start-marker',
                    html: '<div style="background: #3b82f6; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">A</div>',
                    iconSize: [20, 20]
                })
            }).bindPopup(`<b>${route.from}</b><br>Точка отправления`);

            const endMarker = L.marker(endCoords, {
                icon: L.divIcon({
                    className: 'custom-marker end-marker',
                    html: '<div style="background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">B</div>',
                    iconSize: [20, 20]
                })
            }).bindPopup(`<b>${route.to}</b><br>Точка назначения`);

            // Создаем линию маршрута
            const routeLine = L.polyline([startCoords, endCoords], {
                color: '#10b981',
                weight: 4,
                opacity: 0.8,
                dashArray: '10, 5'
            }).bindPopup(`<b>Маршрут: ${route.name}</b><br>Расстояние: ${route.distance} км`);

            // Группируем все элементы маршрута
            currentRoute = L.layerGroup([startMarker, endMarker, routeLine]);
            currentRoute.addTo(routeMap);

            // Подгоняем карту под маршрут
            const bounds = L.latLngBounds([startCoords, endCoords]);
            routeMap.fitBounds(bounds, { padding: [20, 20] });

            // Добавляем анимацию появления
            setTimeout(() => {
                routeMap.invalidateSize();
            }, 100);
        }

        // Стилизация выбранных элементов
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Убираем выделение с других элементов того же типа
                document.querySelectorAll(`input[name="${this.name}"]`).forEach(r => {
                    const parentCard = r.closest('.route-card, .loading-card');
                    if (parentCard) {
                        parentCard.classList.remove('border-code-blue', 'bg-code-blue/10', 'border-terminal-green', 'bg-terminal-green/10');
                        // Скрываем все индикаторы выбора для маршрутов
                        const routeIndicators = parentCard.querySelectorAll('.absolute > .fa-check-circle, .absolute > span');
                        routeIndicators.forEach(indicator => {
                            indicator.parentElement.style.opacity = '0';
                        });
                        // Скрываем индикаторы для карточек погрузки
                        const loadingIndicators = parentCard.querySelectorAll('div[style*="opacity"] i.fa-check-circle');
                        loadingIndicators.forEach(indicator => {
                            indicator.parentElement.style.opacity = '0';
                        });
                    }
                });
                
                // Выделяем выбранный элемент
                const selectedCard = this.closest('.route-card, .loading-card');
                if (selectedCard) {
                    selectedCard.classList.add('border-terminal-green', 'bg-terminal-green/10');
                    // Показываем индикаторы выбора для маршрутов
                    const routeIndicators = selectedCard.querySelectorAll('.absolute > .fa-check-circle, .absolute > span');
                    routeIndicators.forEach(indicator => {
                        indicator.parentElement.style.opacity = '1';
                    });
                    // Показываем индикаторы для карточек погрузки
                    const loadingIndicators = selectedCard.querySelectorAll('div[style*="opacity"] i.fa-check-circle');
                    loadingIndicators.forEach(indicator => {
                        indicator.parentElement.style.opacity = '1';
                    });
                }

                // Если выбран маршрут, показываем карту
                if (this.name === 'route') {
                    // Показываем контейнер карты
                    const mapContainer = document.getElementById('routeMapContainer');
                    mapContainer.classList.remove('hidden');
                    
                    // Плавная прокрутка к карте
                    mapContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    
                    showRouteMap(this.value);
                }
             });
         });

         // Обработчик закрытия карты
         document.getElementById('closeMapBtn').addEventListener('click', function() {
             const mapContainer = document.getElementById('routeMapContainer');
             mapContainer.classList.add('hidden');
             
             // Сбрасываем выбор маршрута
             document.querySelectorAll('input[name="route"]').forEach(input => {
                 input.checked = false;
             });
             
             // Сбрасываем визуальное состояние карточек
             document.querySelectorAll('.route-card').forEach(card => {
                 card.classList.remove('border-terminal-green', 'bg-terminal-green/10');
                 const routeIndicators = card.querySelectorAll('.absolute > .fa-check-circle, .absolute > span');
                 routeIndicators.forEach(indicator => {
                     indicator.parentElement.style.opacity = '0';
                 });
             });
         });

         // Дополнительные обработчики для кликов по карточкам погрузки
        document.querySelectorAll('.loading-card').forEach(card => {
            card.addEventListener('click', function(e) {
                // Предотвращаем двойное срабатывание если клик был по радиокнопке
                if (e.target.type === 'radio') return;
                
                const radio = this.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }
            });
        });

        // Container interactive controls
        const containerCountDisplay = document.getElementById('containerCountDisplay');
        const containerCountInput = document.getElementById('containerCountInput');
        const containerCountProgress = document.getElementById('containerCountProgress');
        const containerCountText = document.getElementById('containerCountText');
        const decreaseBtn = document.getElementById('decreaseCount');
        const increaseBtn = document.getElementById('increaseCount');
        
        function updateContainerCount(count) {
            containerCountDisplay.textContent = count;
            containerCountInput.value = count;
            containerCountText.textContent = count;
            const percentage = (count / 10) * 100;
            containerCountProgress.style.width = percentage + '%';
            
            // Generate individual container cards
            generateContainerCards(count);
            
            // Update total weight and price
            updateTotalWeight();
            updateLoadingPrice();
        }
        
        function generateContainerCards(count) {
            const containersConfig = document.getElementById('containersConfig');
            containersConfig.innerHTML = '';
            
            for (let i = 1; i <= count; i++) {
                const containerCard = document.createElement('div');
                containerCard.className = 'bg-gray-900/30 border border-gray-700 rounded-lg p-4 mb-4';
                containerCard.innerHTML = `
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-mono text-code-blue">
                            <i class="fas fa-box mr-2"></i>Контейнер #${i}
                        </h4>
                        <div class="text-xs text-gray-400 font-mono">
                            ID: container_${i}
                        </div>
                    </div>
                    
                    <!-- Weight Selection for Container ${i} -->
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-mono mb-3">Вес контейнера:</label>
                        <div class="grid grid-cols-3 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="container_${i}_weight" value="2.5" class="sr-only container-weight-radio" data-container="${i}" required>
                                <div class="weight-card p-3 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 text-center">
                                    <div class="text-xl font-bold text-white font-mono mb-1">2.5</div>
                                    <div class="text-xs text-gray-400 font-mono">тонн</div>
                                    <div class="mt-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green"></i>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="container_${i}_weight" value="5" class="sr-only container-weight-radio" data-container="${i}">
                                <div class="weight-card p-3 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 text-center">
                                    <div class="text-xl font-bold text-white font-mono mb-1">5</div>
                                    <div class="text-xs text-gray-400 font-mono">тонн</div>
                                    <div class="mt-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green"></i>
                                    </div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="container_${i}_weight" value="10" class="sr-only container-weight-radio" data-container="${i}">
                                <div class="weight-card p-3 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 text-center">
                                    <div class="text-xl font-bold text-white font-mono mb-1">10</div>
                                    <div class="text-xs text-gray-400 font-mono">тонн</div>
                                    <div class="mt-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Loading Type Selection for Container ${i} -->
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-mono mb-3">Тип погрузки:</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="container_${i}_loading" value="standard" class="sr-only container-loading-radio" data-container="${i}" required>
                                <div class="loading-card p-3 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 relative">
                                    <div class="absolute top-2 right-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green text-lg"></i>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-truck-loading text-code-blue text-lg mr-2"></i>
                                        <div>
                                            <div class="font-mono text-white font-semibold text-sm">Стандартная</div>
                                            <div class="text-xs text-gray-400 font-mono">Обычная погрузка</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer">
                                <input type="radio" name="container_${i}_loading" value="no_tilt" class="sr-only container-loading-radio" data-container="${i}">
                                <div class="loading-card p-3 border-2 border-gray-700 rounded-lg hover:border-code-blue transition-all duration-300 relative">
                                    <div class="absolute top-2 right-2 opacity-0 transition-opacity duration-300">
                                        <i class="fas fa-check-circle text-terminal-green text-lg"></i>
                                    </div>
                                    <div class="flex items-center mb-2">
                                        <i class="fas fa-hand-paper text-yellow-400 text-lg mr-2"></i>
                                        <div>
                                            <div class="font-mono text-white font-semibold text-sm">Не кантовать</div>
                                            <div class="text-xs text-gray-400 font-mono">Осторожная погрузка</div>
                                        </div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Container Summary -->
                    <div class="bg-gray-800/50 rounded p-3">
                        <div class="flex justify-between items-center text-sm font-mono">
                            <span class="text-gray-400">Вес:</span>
                            <span class="container-weight-display text-terminal-green" data-container="${i}">не выбран</span>
                        </div>
                        <div class="flex justify-between items-center text-sm font-mono mt-1">
                            <span class="text-gray-400">Тип:</span>
                            <span class="container-loading-display text-code-blue" data-container="${i}">не выбран</span>
                        </div>
                    </div>
                `;
                
                containersConfig.appendChild(containerCard);
            }
            
            // Add event listeners for new container controls
            addContainerEventListeners();
        }
        
        function addContainerEventListeners() {
            // Weight selection handlers for individual containers
            document.querySelectorAll('.container-weight-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const containerId = this.dataset.container;
                    const containerCard = this.closest('[class*="bg-gray-900/30"]');
                    
                    // Remove active state from all weight cards in this container
                    containerCard.querySelectorAll('.weight-card').forEach(card => {
                        card.classList.remove('border-code-blue', 'bg-code-blue/10');
                        card.classList.add('border-gray-700');
                        const checkIcon = card.querySelector('.opacity-0');
                        if (checkIcon) checkIcon.classList.add('opacity-0');
                    });
                    
                    // Add active state to selected card
                    const selectedCard = this.closest('label').querySelector('.weight-card');
                    selectedCard.classList.remove('border-gray-700');
                    selectedCard.classList.add('border-code-blue', 'bg-code-blue/10');
                    const checkIcon = selectedCard.querySelector('.opacity-0');
                    if (checkIcon) checkIcon.classList.remove('opacity-0');
                    
                    // Update container display
                    const weightDisplay = containerCard.querySelector('.container-weight-display');
                    if (weightDisplay) {
                        weightDisplay.textContent = this.value + 'т';
                    }
                    
                    updateTotalWeight();
                    updateLoadingPrice();
                });
            });
            
            // Loading type selection handlers for individual containers
            document.querySelectorAll('.container-loading-radio').forEach(radio => {
                radio.addEventListener('change', function() {
                    const containerId = this.dataset.container;
                    const containerCard = this.closest('[class*="bg-gray-900/30"]');
                    
                    // Remove active state from all loading cards in this container
                    containerCard.querySelectorAll('.loading-card').forEach(card => {
                        card.classList.remove('border-code-blue', 'bg-code-blue/10');
                        card.classList.add('border-gray-700');
                        const checkIcon = card.querySelector('.absolute .opacity-0');
                        if (checkIcon) checkIcon.classList.add('opacity-0');
                    });
                    
                    // Add active state to selected card
                    const selectedCard = this.closest('label').querySelector('.loading-card');
                    selectedCard.classList.remove('border-gray-700');
                    selectedCard.classList.add('border-code-blue', 'bg-code-blue/10');
                    const checkIcon = selectedCard.querySelector('.absolute .opacity-0');
                    if (checkIcon) checkIcon.classList.remove('opacity-0');
                    
                    // Update container display
                    const loadingDisplay = containerCard.querySelector('.container-loading-display');
                    if (loadingDisplay) {
                        const loadingText = this.value === 'standard' ? 'Стандартная' : 'Не кантовать';
                        loadingDisplay.textContent = loadingText;
                    }
                    
                    updateLoadingPrice();
                });
            });
        }
        
        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', function() {
                const currentCount = parseInt(containerCountInput.value);
                if (currentCount > 1) {
                    updateContainerCount(currentCount - 1);
                }
            });
        }
        
        if (increaseBtn) {
            increaseBtn.addEventListener('click', function() {
                const currentCount = parseInt(containerCountInput.value);
                if (currentCount < 10) {
                    updateContainerCount(currentCount + 1);
                }
            });
        }
        
        // Initialize with first container
        generateContainerCards(1);
        
        // Weight selection handlers (legacy - kept for compatibility)
        document.querySelectorAll('.weight-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove active state from all weight cards
                document.querySelectorAll('.weight-card').forEach(card => {
                    card.classList.remove('border-code-blue', 'bg-code-blue/10');
                    card.classList.add('border-gray-700');
                    const checkIcon = card.querySelector('.opacity-0');
                    if (checkIcon) checkIcon.classList.add('opacity-0');
                });
                
                // Add active state to selected card
                const selectedCard = this.closest('label').querySelector('.weight-card');
                selectedCard.classList.remove('border-gray-700');
                selectedCard.classList.add('border-code-blue', 'bg-code-blue/10');
                const checkIcon = selectedCard.querySelector('.opacity-0');
                if (checkIcon) checkIcon.classList.remove('opacity-0');
                
                updateTotalWeight();
                updateLoadingPrice();
            });
        });
        
        // Loading type selection handlers
        document.querySelectorAll('.loading-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove active state from all loading cards
                document.querySelectorAll('.loading-card').forEach(card => {
                    card.classList.remove('border-code-blue', 'bg-code-blue/10');
                    card.classList.add('border-gray-700');
                    const checkIcon = card.querySelector('.absolute .opacity-0');
                    if (checkIcon) checkIcon.classList.add('opacity-0');
                });
                
                // Add active state to selected card
                const selectedCard = this.closest('label').querySelector('.loading-card');
                selectedCard.classList.remove('border-gray-700');
                selectedCard.classList.add('border-code-blue', 'bg-code-blue/10');
                const checkIcon = selectedCard.querySelector('.absolute .opacity-0');
                if (checkIcon) checkIcon.classList.remove('opacity-0');
                
                updateLoadingPrice();
            });
        });
        
        // Weekend toggle handler
        const weekendToggle = document.querySelector('.weekend-toggle');
        if (weekendToggle) {
            weekendToggle.addEventListener('change', function() {
                const toggle = this.closest('label').querySelector('div');
                const slider = toggle.querySelector('div');
                
                if (this.checked) {
                    toggle.classList.remove('bg-gray-700');
                    toggle.classList.add('bg-terminal-green');
                    slider.classList.remove('translate-x-0.5');
                    slider.classList.add('translate-x-5');
                } else {
                    toggle.classList.remove('bg-terminal-green');
                    toggle.classList.add('bg-gray-700');
                    slider.classList.remove('translate-x-5');
                    slider.classList.add('translate-x-0.5');
                }
                
                updateLoadingPrice();
            });
        }
        
        // Update total weight function
        function updateTotalWeight() {
            const containerCount = parseInt(containerCountInput?.value) || 0;
            let totalWeight = 0;
            
            // Calculate total weight from all individual containers
            for (let i = 1; i <= containerCount; i++) {
                const selectedWeight = document.querySelector(`input[name="container_${i}_weight"]:checked`);
                if (selectedWeight) {
                    totalWeight += parseFloat(selectedWeight.value);
                }
            }
            
            const totalWeightEl = document.getElementById('totalWeight');
            if (totalWeightEl) {
                totalWeightEl.textContent = totalWeight + 'т';
            }
            
            // Update weight progress bar (max 100 tons)
            const weightPercentage = Math.min((totalWeight / 100) * 100, 100);
            const weightProgress = document.getElementById('weightProgress');
            if (weightProgress) {
                weightProgress.style.width = weightPercentage + '%';
                
                // Change color based on weight
                if (weightPercentage > 80) {
                    weightProgress.className = 'bg-gradient-to-r from-red-500 to-red-400 h-2 rounded-full transition-all duration-300';
                } else if (weightPercentage > 60) {
                    weightProgress.className = 'bg-gradient-to-r from-yellow-500 to-orange-400 h-2 rounded-full transition-all duration-300';
                } else {
                    weightProgress.className = 'bg-gradient-to-r from-terminal-green to-yellow-400 h-2 rounded-full transition-all duration-300';
                }
            }
        }
        
        // Update loading price function
        function updateLoadingPrice() {
            const containerCount = parseInt(containerCountInput?.value) || 0;
            const weekendLoading = document.querySelector('.weekend-toggle')?.checked || false;
            
            let basePrice = 0;
            
            // Get price from PHP variables
            const prices = {
                '2.5': { 'standard': <?= $loading_prices['2.5']['standard'] ?>, 'no_tilt': <?= $loading_prices['2.5']['no_tilt'] ?> },
                '5': { 'standard': <?= $loading_prices['5']['standard'] ?>, 'no_tilt': <?= $loading_prices['5']['no_tilt'] ?> },
                '10': { 'standard': <?= $loading_prices['10']['standard'] ?>, 'no_tilt': <?= $loading_prices['10']['no_tilt'] ?> }
            };
            
            // Calculate total price from all individual containers
            for (let i = 1; i <= containerCount; i++) {
                const selectedWeight = document.querySelector(`input[name="container_${i}_weight"]:checked`);
                const selectedLoadingType = document.querySelector(`input[name="container_${i}_loading"]:checked`);
                
                if (selectedWeight && selectedLoadingType) {
                    const weight = selectedWeight.value;
                    const loadingType = selectedLoadingType.value;
                    basePrice += prices[weight][loadingType];
                }
            }
            
            if (weekendLoading) {
                basePrice *= 2;
            }
            
            const loadingPriceEl = document.getElementById('loadingPrice');
            if (loadingPriceEl) {
                loadingPriceEl.textContent = basePrice.toLocaleString('ru-RU') + '₽';
            }
        }
        
        // Initialize container controls
        if (containerCountInput) {
            updateContainerCount(1);
            updateTotalWeight();
            updateLoadingPrice();
        }

        // Обработчик для скрытия карты при сбросе формы
        document.addEventListener('reset', function() {
            const mapContainer = document.getElementById('routeMapContainer');
            if (mapContainer) {
                mapContainer.classList.add('hidden');
            }
        });

        // Стилизация чекбоксов
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const nextElement = this.nextElementSibling;
                if (nextElement) {
                    const icon = nextElement.querySelector('i');
                    if (this.checked) {
                        if (icon) icon.classList.remove('hidden');
                        nextElement.classList.add('border-terminal-green');
                    } else {
                        if (icon) icon.classList.add('hidden');
                        nextElement.classList.remove('border-terminal-green');
                    }
                }
            });
        });

            // Валидация формы
            const orderForm = document.getElementById('orderForm');
            if (orderForm) {
                orderForm.addEventListener('submit', function(e) {
            // Очистка предыдущих ошибок
            clearValidationErrors();
            let hasErrors = false;
            
            // Проверка маршрута
            const route = document.querySelector('input[name="route"]:checked');
            if (!route) {
                showFieldError('routesList', 'Выберите маршрут');
                hasErrors = true;
            }
            
            // Проверка количества контейнеров
            const containerCountInput = document.querySelector('input[name="container_count"]');
            const containerCount = containerCountInput?.value;
            if (!containerCount || containerCount < 1 || containerCount > 10) {
                showFieldError('containerCountInput', 'Количество контейнеров должно быть от 1 до 10');
                hasErrors = true;
            }
            
            // Проверка веса и типа погрузки для каждого контейнера
            const currentContainerCount = parseInt(containerCount) || 0;
            for (let i = 1; i <= currentContainerCount; i++) {
                const weight = document.querySelector(`input[name="container_${i}_weight"]:checked`);
                if (!weight) {
                    showFieldError(`container${i}WeightOptions`, `Выберите вес для контейнера ${i}`);
                    hasErrors = true;
                }
                
                const loadingType = document.querySelector(`input[name="container_${i}_loading"]:checked`);
                if (!loadingType) {
                    showFieldError(`container${i}LoadingOptions`, `Выберите тип погрузки для контейнера ${i}`);
                    hasErrors = true;
                }
            }
            
            // Проверка данных клиента
            const customerNameInput = document.querySelector('input[name="customer_name"]');
            const customerName = customerNameInput?.value.trim();
            if (!customerName || customerName.length < 2) {
                showFieldError('customerName', 'Введите корректное имя клиента (минимум 2 символа)');
                hasErrors = true;
            } else if (!/^[a-zA-Zа-яА-ЯёЁ\s\-\.]+$/.test(customerName)) {
                showFieldError('customerName', 'Имя клиента может содержать только буквы, пробелы, дефисы и точки');
                hasErrors = true;
            }
            
            const customerEmailInput = document.querySelector('input[name="customer_email"]');
            const customerEmail = customerEmailInput?.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!customerEmail || !emailRegex.test(customerEmail)) {
                showFieldError('customerEmail', 'Введите корректный email адрес');
                hasErrors = true;
            }
            
            const customerPhoneInput = document.querySelector('input[name="customer_phone"]');
            const customerPhone = customerPhoneInput?.value.trim();
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            if (!customerPhone || !phoneRegex.test(customerPhone)) {
                showFieldError('customerPhone', 'Введите корректный номер телефона (минимум 10 цифр)');
                hasErrors = true;
            }
            
            // Проверка названия компании
            const companyNameInput = document.querySelector('input[name="company_name"]');
            const companyName = companyNameInput?.value.trim();
            if (companyName && companyName.length > 100) {
                showFieldError('companyName', 'Название компании не должно превышать 100 символов');
                hasErrors = true;
            }
            
            // Проверка описания груза
            const cargoDescriptionInput = document.querySelector('textarea[name="cargo_description"]');
            const cargoDescription = cargoDescriptionInput?.value.trim();
            if (cargoDescription && cargoDescription.length > 500) {
                showFieldError('cargoDescription', 'Описание груза не должно превышать 500 символов');
                hasErrors = true;
            }
            
            // Если есть ошибки, предотвращаем отправку
            if (hasErrors) {
                e.preventDefault();
                return false;
            }
            
            // Если ошибок нет, показываем индикатор загрузки и позволяем форме отправиться
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Обработка...';
            submitBtn.disabled = true;
            
            // Форма отправится автоматически, так как preventDefault не вызывается
                });
            }
        
        // Функциональность поиска маршрутов
        const routeSearch = document.getElementById('routeSearch');
        const routesList = document.getElementById('routesList');
        const routeCount = document.getElementById('routeCount');
        const allRoutes = Array.from(routesList.querySelectorAll('label'));
        
        routeSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            let visibleCount = 0;
            
            allRoutes.forEach(route => {
                const routeName = route.querySelector('.font-semibold').textContent.toLowerCase();
                const routeFrom = route.querySelector('.text-gray-500').textContent.toLowerCase();
                
                if (routeName.includes(searchTerm) || routeFrom.includes(searchTerm)) {
                    route.style.display = 'block';
                    visibleCount++;
                } else {
                    route.style.display = 'none';
                }
            });
            
            routeCount.textContent = visibleCount;
            
            // Показываем сообщение если ничего не найдено
            let noResultsMsg = document.getElementById('noResultsMessage');
            if (visibleCount === 0 && searchTerm !== '') {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.id = 'noResultsMessage';
                    noResultsMsg.className = 'col-span-full text-center py-8 text-gray-400 font-mono';
                    noResultsMsg.innerHTML = '<i class="fas fa-search mr-2"></i>Маршруты не найдены';
                    routesList.appendChild(noResultsMsg);
                }
                noResultsMsg.style.display = 'block';
            } else if (noResultsMsg) {
                noResultsMsg.style.display = 'none';
            }
        });
        
        // Живая валидация полей
        setupLiveValidation();
        
        } // Закрытие функции initializeForm
        
        // Функция настройки живой валидации
        function setupLiveValidation() {
            // Валидация имени клиента
            const customerNameInput = document.querySelector('input[name="customer_name"]');
            if (customerNameInput) {
                customerNameInput.addEventListener('blur', function() {
                    validateCustomerName(this.value.trim());
                });
                customerNameInput.addEventListener('input', function() {
                    if (this.classList.contains('field-error')) {
                        validateCustomerName(this.value.trim());
                    }
                });
            }
            
            // Валидация email
            const customerEmailInput = document.querySelector('input[name="customer_email"]');
            if (customerEmailInput) {
                customerEmailInput.addEventListener('blur', function() {
                    validateEmail(this.value.trim());
                });
                customerEmailInput.addEventListener('input', function() {
                    if (this.classList.contains('field-error')) {
                        validateEmail(this.value.trim());
                    }
                });
            }
            
            // Валидация телефона
            const customerPhoneInput = document.querySelector('input[name="customer_phone"]');
            if (customerPhoneInput) {
                customerPhoneInput.addEventListener('blur', function() {
                    validatePhone(this.value.trim());
                });
                customerPhoneInput.addEventListener('input', function() {
                    if (this.classList.contains('field-error')) {
                        validatePhone(this.value.trim());
                    }
                });
            }
            
            // Валидация названия компании
            const companyNameInput = document.querySelector('input[name="company_name"]');
            if (companyNameInput) {
                companyNameInput.addEventListener('input', function() {
                    validateCompanyName(this.value.trim());
                });
            }
            
            // Валидация описания груза
            const cargoDescriptionInput = document.querySelector('textarea[name="cargo_description"]');
            if (cargoDescriptionInput) {
                cargoDescriptionInput.addEventListener('input', function() {
                    validateCargoDescription(this.value.trim());
                });
            }
        }
        
        // Функции валидации отдельных полей
        function validateCustomerName(name) {
            // Удаляем предыдущие сообщения для этого поля
            const field = document.querySelector('input[name="customer_name"]');
            const existingError = field.parentElement.querySelector('.validation-error');
            const existingSuccess = field.parentElement.querySelector('.validation-success');
            if (existingError) existingError.remove();
            if (existingSuccess) existingSuccess.remove();
            field.classList.remove('field-error', 'field-success');
            
            if (!name || name.length < 2) {
                showFieldError('customerName', 'Введите корректное имя клиента (минимум 2 символа)');
                return false;
            } else if (!/^[a-zA-Zа-яА-ЯёЁ\s\-\.]+$/.test(name)) {
                showFieldError('customerName', 'Имя клиента может содержать только буквы, пробелы, дефисы и точки');
                return false;
            } else {
                showFieldSuccess('customerName', 'Имя корректно');
                return true;
            }
        }
        
        function validateEmail(email) {
            const field = document.querySelector('input[name="customer_email"]');
            const existingError = field.parentElement.querySelector('.validation-error');
            const existingSuccess = field.parentElement.querySelector('.validation-success');
            if (existingError) existingError.remove();
            if (existingSuccess) existingSuccess.remove();
            field.classList.remove('field-error', 'field-success');
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!email || !emailRegex.test(email)) {
                showFieldError('customerEmail', 'Введите корректный email адрес');
                return false;
            } else {
                showFieldSuccess('customerEmail', 'Email корректен');
                return true;
            }
        }
        
        function validatePhone(phone) {
            const field = document.querySelector('input[name="customer_phone"]');
            const existingError = field.parentElement.querySelector('.validation-error');
            const existingSuccess = field.parentElement.querySelector('.validation-success');
            if (existingError) existingError.remove();
            if (existingSuccess) existingSuccess.remove();
            field.classList.remove('field-error', 'field-success');
            
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
            if (!phone || !phoneRegex.test(phone)) {
                showFieldError('customerPhone', 'Введите корректный номер телефона (минимум 10 цифр)');
                return false;
            } else {
                showFieldSuccess('customerPhone', 'Телефон корректен');
                return true;
            }
        }
        
        function validateCompanyName(name) {
            const field = document.querySelector('input[name="company_name"]');
            const existingError = field.parentElement.querySelector('.validation-error');
            const existingSuccess = field.parentElement.querySelector('.validation-success');
            if (existingError) existingError.remove();
            if (existingSuccess) existingSuccess.remove();
            field.classList.remove('field-error', 'field-success');
            
            if (name && name.length > 100) {
                showFieldError('companyName', 'Название компании не должно превышать 100 символов');
                return false;
            } else if (name && name.length > 0) {
                showFieldSuccess('companyName', 'Название компании корректно');
                return true;
            }
            return true;
        }
        
        function validateCargoDescription(description) {
            const field = document.querySelector('textarea[name="cargo_description"]');
            const existingError = field.parentElement.querySelector('.validation-error');
            const existingSuccess = field.parentElement.querySelector('.validation-success');
            if (existingError) existingError.remove();
            if (existingSuccess) existingSuccess.remove();
            field.classList.remove('field-error', 'field-success');
            
            if (description && description.length > 500) {
                showFieldError('cargoDescription', 'Описание груза не должно превышать 500 символов');
                return false;
            } else if (description && description.length > 0) {
                showFieldSuccess('cargoDescription', 'Описание груза корректно');
                return true;
            }
            return true;
        }
    </script>
</body>
</html>