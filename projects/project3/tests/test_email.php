<?php
/**
 * Тестовый файл для проверки email конфигурации
 * Запустите этот файл для проверки настроек SMTP
 */

require_once 'config.php';
require_once 'EmailService.php';

// Проверяем, запущен ли файл из командной строки или браузера
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    echo "<html><head><meta charset='UTF-8'><title>Email Test</title></head><body>";
    echo "<h1>🧪 Тест Email конфигурации</h1>";
}

function output($message, $isError = false) {
    global $isCLI;
    
    if ($isCLI) {
        echo ($isError ? "❌ " : "✅ ") . $message . "\n";
    } else {
        $color = $isError ? 'red' : 'green';
        $icon = $isError ? '❌' : '✅';
        echo "<p style='color: $color; font-family: monospace;'>$icon $message</p>";
    }
}

try {
    output("Начинаем тестирование email конфигурации...");
    
    // Проверяем загрузку конфигурации
    output("Проверка загрузки .env файла...");
    
    $requiredVars = ['MAIL_HOST', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'ADMIN_EMAIL'];
    $missingVars = [];
    
    foreach ($requiredVars as $var) {
        $value = Config::get($var);
        if (empty($value)) {
            $missingVars[] = $var;
        } else {
            output("$var: " . (strlen($value) > 20 ? substr($value, 0, 20) . '...' : $value));
        }
    }
    
    if (!empty($missingVars)) {
        output("Отсутствуют обязательные переменные: " . implode(', ', $missingVars), true);
        throw new Exception("Не все обязательные переменные настроены");
    }
    
    output("Конфигурация загружена успешно!");
    
    // Создаем экземпляр EmailService
    output("Создание EmailService...");
    $emailService = new EmailService();
    output("EmailService создан успешно!");
    
    // Тестируем SMTP соединение
    output("Тестирование SMTP соединения...");
    $connectionTest = $emailService->testConnection();
    
    if ($connectionTest) {
        output("SMTP соединение установлено успешно!");
    } else {
        output("Ошибка SMTP соединения. Проверьте настройки.", true);
    }
    
    // Отправляем тестовое письмо
    output("Отправка тестового письма...");
    
    $testOrderData = [
        'order_number' => 'TEST-' . date('YmdHis'),
        'from' => 'Москва',
        'to' => 'Санкт-Петербург',
        'distance' => 635,
        'weight' => 1000,
        'cargo_type' => 'Тестовый груз',
        'loading' => true,
        'additional_services' => 'Тестирование email',
        'base_cost' => 15000,
        'loading_cost' => 2000,
        'additional_cost' => 1000,
        'total_cost' => 18000,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $emailSent = $emailService->sendOrderConfirmation($testOrderData);
    
    if ($emailSent) {
        output("✉️ Тестовое письмо отправлено успешно!");
        output("Проверьте почтовый ящик: " . Config::get('ADMIN_EMAIL'));
    } else {
        output("Ошибка отправки тестового письма", true);
    }
    
    output("\n🎉 Тестирование завершено!");
    
} catch (Exception $e) {
    output("Критическая ошибка: " . $e->getMessage(), true);
    output("Стек вызовов: " . $e->getTraceAsString(), true);
}

// Показываем информацию о логах
output("\n📋 Информация о логах:");
output("Email лог: backend/logs/email_log.txt");
output("Заказы: backend/logs/orders.txt");

if (!$isCLI) {
    echo "</body></html>";
}
?>