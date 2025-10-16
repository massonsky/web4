<?php
require_once 'backend/config.php';

echo "🛠️ Добавляем дополнительные услуги в таблицу Services...\n\n";

try {
    // Подключение к базе данных
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Получаем существующие категории услуг
    $stmt = $pdo->query("SELECT category_id, category_name FROM ServiceCategories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📋 Найдено категорий услуг: " . count($categories) . "\n";
    foreach ($categories as $category) {
        echo "  - {$category['category_name']} (ID: {$category['category_id']})\n";
    }
    echo "\n";
    
    // Дополнительные услуги для каждой категории
    $additional_services = [
        // Диагностика (category_id = 1)
        1 => [
            ['name' => 'Компьютерная диагностика двигателя', 'description' => 'Полная диагностика системы управления двигателем с помощью профессионального оборудования', 'price' => 2500, 'duration' => 60],
            ['name' => 'Диагностика подвески', 'description' => 'Проверка состояния амортизаторов, пружин, стоек и других элементов подвески', 'price' => 1800, 'duration' => 45],
            ['name' => 'Диагностика тормозной системы', 'description' => 'Комплексная проверка тормозных колодок, дисков, суппортов и тормозной жидкости', 'price' => 1500, 'duration' => 30],
            ['name' => 'Диагностика кондиционера', 'description' => 'Проверка работоспособности системы кондиционирования, заправка фреоном', 'price' => 2000, 'duration' => 40],
            ['name' => 'Диагностика электрики', 'description' => 'Проверка электрических систем автомобиля, поиск неисправностей в проводке', 'price' => 2200, 'duration' => 50]
        ],
        // Ремонт двигателя (category_id = 2)
        2 => [
            ['name' => 'Капитальный ремонт двигателя', 'description' => 'Полная разборка и восстановление двигателя с заменой изношенных деталей', 'price' => 85000, 'duration' => 1440],
            ['name' => 'Замена поршневых колец', 'description' => 'Замена поршневых колец для восстановления компрессии двигателя', 'price' => 25000, 'duration' => 480],
            ['name' => 'Ремонт головки блока цилиндров', 'description' => 'Восстановление ГБЦ, замена клапанов, направляющих втулок', 'price' => 35000, 'duration' => 600],
            ['name' => 'Замена цепи ГРМ', 'description' => 'Замена цепи газораспределительного механизма и натяжителей', 'price' => 15000, 'duration' => 300],
            ['name' => 'Ремонт турбины', 'description' => 'Восстановление турбокомпрессора, замена картриджа', 'price' => 45000, 'duration' => 360]
        ],
        // Техническое обслуживание (category_id = 3)
        3 => [
            ['name' => 'ТО-1 (15000 км)', 'description' => 'Первое техническое обслуживание: замена масла, фильтров, проверка основных систем', 'price' => 8500, 'duration' => 120],
            ['name' => 'ТО-2 (30000 км)', 'description' => 'Второе ТО: расширенная проверка, замена свечей, воздушного фильтра', 'price' => 12000, 'duration' => 180],
            ['name' => 'ТО-3 (45000 км)', 'description' => 'Третье ТО: замена тормозной жидкости, антифриза, комплексная диагностика', 'price' => 15500, 'duration' => 240],
            ['name' => 'Предпродажная подготовка', 'description' => 'Комплексная подготовка автомобиля к продаже', 'price' => 18000, 'duration' => 300],
            ['name' => 'Сезонное ТО', 'description' => 'Подготовка автомобиля к зимнему или летнему сезону', 'price' => 6500, 'duration' => 90]
        ],
        // Кузовной ремонт (category_id = 4)
        4 => [
            ['name' => 'Покраска автомобиля полная', 'description' => 'Полная покраска кузова автомобиля в камере с гарантией', 'price' => 120000, 'duration' => 2880],
            ['name' => 'Покраска элемента кузова', 'description' => 'Покраска отдельного элемента (дверь, крыло, капот)', 'price' => 15000, 'duration' => 480],
            ['name' => 'Рихтовка после ДТП', 'description' => 'Восстановление геометрии кузова после аварии', 'price' => 45000, 'duration' => 720],
            ['name' => 'Замена лобового стекла', 'description' => 'Замена лобового стекла с калибровкой систем безопасности', 'price' => 25000, 'duration' => 180],
            ['name' => 'Антикоррозийная обработка', 'description' => 'Защитная обработка кузова от коррозии', 'price' => 8500, 'duration' => 240]
        ],
        // Шиномонтаж (category_id = 5)
        5 => [
            ['name' => 'Шиномонтаж с балансировкой (4 колеса)', 'description' => 'Монтаж шин на диски с компьютерной балансировкой', 'price' => 2400, 'duration' => 60],
            ['name' => 'Ремонт проколов', 'description' => 'Ремонт проколов шин методом холодной вулканизации', 'price' => 800, 'duration' => 30],
            ['name' => 'Правка литых дисков', 'description' => 'Восстановление геометрии литых дисков после повреждений', 'price' => 3500, 'duration' => 120],
            ['name' => 'Установка датчиков давления TPMS', 'description' => 'Установка и программирование датчиков давления в шинах', 'price' => 4500, 'duration' => 90],
            ['name' => 'Хранение шин (сезон)', 'description' => 'Сезонное хранение комплекта шин в специальных условиях', 'price' => 3000, 'duration' => 30]
        ]
    ];
    
    $total_added = 0;
    
    foreach ($additional_services as $category_id => $services) {
        $category_name = '';
        foreach ($categories as $cat) {
            if ($cat['category_id'] == $category_id) {
                $category_name = $cat['category_name'];
                break;
            }
        }
        
        echo "🔧 Добавляем услуги для категории: {$category_name}\n";
        
        foreach ($services as $service) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO Services (service_name, description, base_price, duration_minutes, category_id) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $service['name'],
                $service['description'],
                $service['price'],
                $service['duration'],
                $category_id
            ]);
            
            if ($result && $stmt->rowCount() > 0) {
                echo "  ✓ {$service['name']} - {$service['price']} руб.\n";
                $total_added++;
            } else {
                echo "  - {$service['name']} (уже существует)\n";
            }
        }
        echo "\n";
    }
    
    // Статистика
    echo "📊 Статистика услуг:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM Services");
    $total_services = $stmt->fetchColumn();
    echo "  📋 Всего услуг в базе: {$total_services}\n";
    echo "  ➕ Добавлено новых услуг: {$total_added}\n\n";
    
    // Показываем услуги по категориям
    echo "📋 Услуги по категориям:\n";
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Services WHERE category_id = ?");
        $stmt->execute([$category['category_id']]);
        $count = $stmt->fetchColumn();
        echo "  - {$category['category_name']}: {$count} услуг\n";
    }
    
    echo "\n✅ Таблица Services успешно заполнена дополнительными услугами!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Файл: " . $e->getFile() . "\n";
    echo "Строка: " . $e->getLine() . "\n";
}
?>