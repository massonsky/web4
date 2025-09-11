<?php
session_start();


$order = $_SESSION['current_order'];

// Helper function to format dress type names
function formatDressType($type) {
    $types = [
        'evening' => 'Вечернее платье',
        'graduation' => 'Платье на выпускной',
        'business' => 'Деловой брючный костюм'
    ];
    return $types[$type] ?? $type;
}

// Helper function to format dress length names
function formatDressLength($length) {
    $lengths = [
        'maxi' => 'Макси',
        'maxi_train' => 'Макси со шлейфом',
        'midi' => 'Миди'
    ];
    return $lengths[$length] ?? $length;
}

// Helper function to format rental type names
function formatRentalType($type) {
    $types = [
        'regular' => 'Обычная аренда',
        'event' => 'Аренда для мероприятия'
    ];
    return $types[$type] ?? $type;
}

// Helper function to format service names
function formatServiceName($service) {
    $services = [
        'fitting' => 'Подгонка по фигуре',
        'steaming' => 'Отпаривание'
    ];
    return $services[$service] ?? $service;
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Счет на оплату - Аренда платьев</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
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
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .bg-code-dark { background: white !important; }
            .text-white { color: black !important; }
            .bg-gray-800 { background: white !important; border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body class="bg-code-dark text-white min-h-screen">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0 no-print">
        <div class="code-matrix"></div>
        <div class="floating-elements"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 w-full bg-black/20 backdrop-blur-md z-50 border-b border-code-blue/20 no-print">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <a href="order.php" class="text-2xl font-bold text-code-blue hover:text-white transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Новый заказ
                </a>
                <div class="text-pink-400">
                    <i class="fas fa-receipt mr-2"></i>
                    Счет на оплату
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-32 pb-20 relative z-10">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-flex items-center justify-center w-24 h-24 bg-green-500/20 rounded-full mb-8 animate-pulse">
                        <i class="fas fa-check-circle text-5xl text-green-400"></i>
                    </div>
                    <h1 class="text-5xl md:text-6xl font-bold mb-6 text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-blue-500">
                        Заказ оформлен!
                    </h1>
                    <p class="text-xl text-gray-400">
                        Номер заказа: <span class="text-code-blue font-mono"><?php echo htmlspecialchars($order['id']); ?></span>
                    </p>
                </div>

                <!-- Bill Details -->
                <div class="bg-gray-800/50 rounded-lg p-8 border border-pink-500/30 backdrop-blur-sm">
                    <!-- Order Info -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-pink-400 mb-4">
                            <i class="fas fa-info-circle mr-2"></i>
                            Информация о заказе
                        </h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Дата заказа:</span>
                                    <?php echo htmlspecialchars($order['timestamp']); ?>
                                </p>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Количество дней:</span>
                                    <?php echo htmlspecialchars($order['rental_details']['days']); ?>
                                </p>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Тип аренды:</span>
                                    <?php echo htmlspecialchars(formatRentalType($order['rental_details']['rental_type'])); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Длина платья:</span>
                                    <?php echo htmlspecialchars(formatDressLength($order['rental_details']['dress_length'])); ?>
                                </p>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Тип платья:</span>
                                    <?php echo htmlspecialchars(formatDressType($order['rental_details']['dress_type'])); ?>
                                </p>
                                <?php if (!empty($order['rental_details']['services'])): ?>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Доп. услуги:</span>
                                    <?php 
                                    $serviceNames = array_map('formatServiceName', $order['rental_details']['services']);
                                    echo htmlspecialchars(implode(', ', $serviceNames));
                                    ?>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-pink-400 mb-4">
                            <i class="fas fa-user mr-2"></i>
                            Информация о клиенте
                        </h2>
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Имя:</span>
                                    <?php echo htmlspecialchars($order['customer']['name']); ?>
                                </p>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Email:</span>
                                    <?php echo htmlspecialchars($order['customer']['email']); ?>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-300 mb-2">
                                    <span class="font-bold text-white">Адрес:</span>
                                    <?php echo htmlspecialchars($order['customer']['address']); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-pink-400 mb-4">
                            <i class="fas fa-calculator mr-2"></i>
                            Расчет стоимости
                        </h2>
                        <div class="bg-gray-700/50 rounded-lg p-6">
                            <?php foreach ($order['pricing']['breakdown'] as $item): ?>
                            <div class="flex justify-between items-center py-2 border-b border-gray-600 last:border-b-0">
                                <span class="text-gray-300"><?php echo htmlspecialchars($item); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Total Price -->
                    <div class="bg-gradient-to-r from-green-500/20 to-blue-500/20 rounded-lg p-6 border border-green-500/30 text-center">
                        <h3 class="text-2xl font-bold text-green-400 mb-2">Итого к оплате</h3>
                        <div class="text-5xl font-bold text-white"><?php echo htmlspecialchars($order['pricing']['total']); ?>₽</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mt-8 no-print">
                        <button onclick="window.print()" class="flex-1 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-bold py-4 px-6 rounded-lg transition-all duration-300">
                            <i class="fas fa-print mr-2"></i>
                            Распечатать счет
                        </button>
                        <a href="../index.html" class="flex-1 bg-gradient-to-r from-pink-500 to-rose-500 hover:from-pink-600 hover:to-rose-600 text-white font-bold py-4 px-6 rounded-lg transition-all duration-300 text-center">
                            <i class="fas fa-plus mr-2"></i>
                            Новый заказ
                        </a>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="mt-8 text-center text-gray-400">
                    <p class="mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Сохраните этот счет для ваших записей
                    </p>
                    <p class="text-sm">
                        По вопросам обращайтесь по email: info@dressrental.com
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Clear session after displaying (optional)
        // This would require an AJAX call to a PHP script
        
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Animate the success icon
            const successIcon = document.querySelector('.fa-check-circle');
            if (successIcon) {
                setTimeout(() => {
                    successIcon.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        successIcon.style.transform = 'scale(1)';
                    }, 300);
                }, 500);
            }
        });
    </script>
</body>
</html>

<?php
// Clear the order from session after displaying
// unset($_SESSION['current_order']);
?>