<?php
// Order processing page
session_start();

// Function to load base prices from file
function loadBasePrices() {
    $prices = [];
    $file = '../assets/txt/base_prices.txt';
    
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            // Skip comments
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $prices[trim($key)] = is_numeric($value) ? (float)$value : trim($value);
            }
        }
    }
    
    return $prices;
}

// Function to calculate total price
function calculateTotalPrice($orderData, $prices) {
    $total = $prices['base_rental_price'] + $prices['base_service_price'];
    $breakdown = [];
    
    // Add base prices
    $breakdown[] = "Базовая аренда: {$prices['base_rental_price']}₽";
    $breakdown[] = "Базовые услуги: {$prices['base_service_price']}₽";
    
    // Length surcharge
    $lengthSurcharges = [
        'maxi' => $prices['maxi_surcharge'],
        'maxi_train' => $prices['maxi_with_train_surcharge'],
        'midi' => $prices['midi_surcharge']
    ];
    
    if (isset($lengthSurcharges[$orderData['dress_length']])) {
        $surcharge = $lengthSurcharges[$orderData['dress_length']];
        $total += $surcharge;
        
        $lengthNames = [
            'maxi' => 'Макси',
            'maxi_train' => 'Макси со шлейфом',
            'midi' => 'Миди'
        ];
        $breakdown[] = "{$lengthNames[$orderData['dress_length']]}: +{$surcharge}₽";
    }
    
    // Type surcharge
    $typeSurcharges = [
        'evening' => $prices['evening_surcharge'],
        'graduation' => $prices['graduation_surcharge'],
        'business' => $prices['business_suit_surcharge']
    ];
    
    if (isset($typeSurcharges[$orderData['dress_type']])) {
        $surcharge = $typeSurcharges[$orderData['dress_type']];
        $total += $surcharge;
        
        $typeNames = [
            'evening' => 'Вечернее платье',
            'graduation' => 'На выпускной',
            'business' => 'Деловой костюм'
        ];
        $breakdown[] = "{$typeNames[$orderData['dress_type']]}: +{$surcharge}₽";
    }
    
    // Additional services
    $services = json_decode($orderData['services'], true) ?: [];
    $serviceSurcharges = [
        'fitting' => $prices['fitting_surcharge'],
        'steaming' => $prices['steaming_surcharge']
    ];
    
    foreach ($services as $service) {
        if (isset($serviceSurcharges[$service])) {
            $surcharge = $serviceSurcharges[$service];
            $total += $surcharge;
            
            $serviceNames = [
                'fitting' => 'Подгонка по фигуре',
                'steaming' => 'Отпаривание'
            ];
            $breakdown[] = "{$serviceNames[$service]}: +{$surcharge}₽";
        }
    }
    
    // Event multiplier
    if ($orderData['rental_type'] === 'event') {
        $beforeMultiplier = $total;
        $total = round($total * $prices['event_multiplier']);
        $breakdown[] = "Мероприятие (×{$prices['event_multiplier']}): {$beforeMultiplier}₽ → {$total}₽";
    }
    
    // Days multiplier
    $days = (int)$orderData['days'];
    if ($days > 1) {
        $pricePerDay = $total;
        $total *= $days;
        $breakdown[] = "Количество дней (×{$days}): {$pricePerDay}₽ → {$total}₽";
    }
    
    return [
        'total' => $total,
        'breakdown' => $breakdown
    ];
}

// Handle POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['name', 'email', 'address', 'days', 'dress_length', 'dress_type'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Поле '$field' обязательно для заполнения";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: order.php');
        exit;
    }
    
    // Get form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $days = (int)$_POST['days'];
    $dress_length = $_POST['dress_length'];
    $dress_type = $_POST['dress_type'];
    $rental_type = $_POST['rental_type'] ?? 'regular';
    $services = $_POST['services'] ?? [];
    
    // Load prices and calculate total
    $prices = loadBasePrices();
    
    // Create order data array
    $orderData = [
        'name' => $name,
        'email' => $email,
        'address' => $address,
        'days' => $days,
        'dress_length' => $dress_length,
        'dress_type' => $dress_type,
        'rental_type' => $rental_type,
        'services' => json_encode($services)
    ];
    
    $price_calculation = calculateTotalPrice($orderData, $prices);
    
    // Create order array
    $order = [
        'id' => uniqid('ORDER_'),
        'timestamp' => date('Y-m-d H:i:s'),
        'customer' => [
            'name' => $name,
            'email' => $email,
            'address' => $address
        ],
        'rental_details' => [
            'days' => $days,
            'dress_length' => $dress_length,
            'dress_type' => $dress_type,
            'rental_type' => $rental_type,
            'services' => $services
        ],
        'pricing' => [
            'total' => $price_calculation['total'],
            'breakdown' => $price_calculation['breakdown']
        ]
    ];
    
    // Store order in session for bill page
    $_SESSION['current_order'] = $order;
    
    // Redirect to bill page
    header('Location: bill.php');
    exit;
}

// Display form (GET request)
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аренда платьев - Элегантность на любой случай</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles/styles.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'code-dark': '#0d1117',
                        'code-blue': '#58a6ff',
                        'code-green': '#7c3aed',
                        'terminal-green': '#00ff00'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-code-dark text-white min-h-screen">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0">
        <div class="code-matrix"></div>
        <div class="floating-elements"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black/20 backdrop-blur-md z-50 border-b border-code-blue/20">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="../../../index.html" class="text-2xl font-bold text-code-blue hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Назад к портфолио
                </a>
                <div class="text-pink-400">
                    <i class="fas fa-tshirt mr-2"></i>
                    Аренда платьев
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-pink-500/20 rounded-full mb-8 animate-pulse">
                        <i class="fas fa-tshirt text-5xl text-pink-400"></i>
                    </div>
                    <h1 class="text-5xl md:text-7xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-pink-400 to-rose-500">
                        Аренда платьев
                    </h1>
                    <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                        Элегантность на любой случай - выберите идеальное платье для вашего мероприятия
                    </p>
                </div>

                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="bg-red-500/20 border border-red-500 rounded-lg p-4 mb-6">
                        <h3 class="text-red-400 font-bold mb-2">Ошибки валидации:</h3>
                        <ul class="text-red-300">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li>• <?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <!-- Rental Form -->
                <div class="bg-gray-800/50 rounded-lg p-8 border border-pink-500/30 backdrop-blur-sm">
                    <form id="rentalForm" method="POST" action="" class="space-y-6">
                        <!-- Personal Information -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-pink-400 font-bold mb-2">
                                    <i class="fas fa-user mr-2"></i>Имя <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="name" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-pink-400 font-bold mb-2">
                                    <i class="fas fa-envelope mr-2"></i>Email <span class="text-red-400">*</span>
                                </label>
                                <input type="email" name="email" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>Адрес <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="address" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-calendar-alt mr-2"></i>Количество дней аренды <span class="text-red-400">*</span>
                            </label>
                            <input type="number" name="days" min="1" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none" onchange="calculatePrice()">
                        </div>

                        <!-- Dress Length -->
                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-ruler-vertical mr-2"></i>Длина платья <span class="text-red-400">*</span>
                            </label>
                            <select name="dress_length" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none" onchange="calculatePrice()">
                                <option value="">Выберите длину</option>
                                <option value="maxi">Макси (+100₽)</option>
                                <option value="maxi_train">Макси со шлейфом (+150₽)</option>
                                <option value="midi">Миди (+50₽)</option>
                            </select>
                        </div>

                        <!-- Dress Type -->
                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-tags mr-2"></i>Тип платья <span class="text-red-400">*</span>
                            </label>
                            <select name="dress_type" required class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-pink-400 focus:outline-none" onchange="calculatePrice(); updateDescription()">
                                <option value="">Выберите тип</option>
                                <option value="evening">Вечернее (+100₽)</option>
                                <option value="graduation">На выпускной (+10₽)</option>
                                <option value="business">Деловой брючный костюм (+50₽)</option>
                            </select>
                            <div id="dressTypeDescription" class="mt-2 text-gray-400 text-sm"></div>
                        </div>

                        <!-- Rental Type -->
                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-star mr-2"></i>Вид аренды
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rental_type" value="regular" class="mr-3 text-pink-400" onchange="calculatePrice(); updateRentalDescription()" checked>
                                    <span>Обычная аренда</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="rental_type" value="event" class="mr-3 text-pink-400" onchange="calculatePrice(); updateRentalDescription()">
                                    <span>Для мероприятия (×1.5 к стоимости)</span>
                                </label>
                            </div>
                            <div id="rentalTypeDescription" class="mt-2 text-gray-400 text-sm"></div>
                        </div>

                        <!-- Additional Services -->
                        <div>
                            <label class="block text-pink-400 font-bold mb-2">
                                <i class="fas fa-plus-circle mr-2"></i>Дополнительные услуги
                            </label>
                            <div class="space-y-3">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="services[]" value="fitting" class="mr-3 text-pink-400" onchange="calculatePrice()">
                                    <span>Подгонка по фигуре (+80₽)</span>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="services[]" value="steaming" class="mr-3 text-pink-400" onchange="calculatePrice()">
                                    <span>Отпаривание (+30₽)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Price Display -->
                        <div class="bg-gradient-to-r from-pink-500/20 to-rose-500/20 rounded-lg p-6 border border-pink-500/30">
                            <div class="text-center">
                                <h3 class="text-2xl font-bold text-pink-400 mb-2">Итоговая стоимость</h3>
                                <div id="totalPrice" class="text-4xl font-bold text-white">0₽</div>
                                <div id="priceBreakdown" class="mt-4 text-gray-300 text-sm"></div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white font-bold py-4 px-8 rounded-lg transition-all duration-300 transform hover:scale-105">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Оформить заказ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="../src/script.js"></script>
</body>
</html>