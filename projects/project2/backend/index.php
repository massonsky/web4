<?php
// Устанавливаем кодировку
mb_internal_encoding('UTF-8');

// Проверяем, если это API запрос
if (isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
} else {
    header('Content-Type: text/html; charset=utf-8');
}
date_default_timezone_set('Europe/Moscow');

// Список основных городов с их часовыми поясами
$mainCities = [
    'Москва' => 'Europe/Moscow',
    'Кабул' => 'Asia/Kabul',
    'Денвер' => 'America/Denver',
    'Джакарта' => 'Asia/Jakarta',
    'Благовещенск' => 'Asia/Vladivostok'
];

// Полный список 93 стран/городов с часовыми поясами
$allTimezones = [
    // Европа
    'Лондон' => 'Europe/London',
    'Париж' => 'Europe/Paris',
    'Берлин' => 'Europe/Berlin',
    'Рим' => 'Europe/Rome',
    'Мадрид' => 'Europe/Madrid',
    'Амстердам' => 'Europe/Amsterdam',
    'Стокгольм' => 'Europe/Stockholm',
    'Хельсинки' => 'Europe/Helsinki',
    'Варшава' => 'Europe/Warsaw',
    'Прага' => 'Europe/Prague',
    'Будапешт' => 'Europe/Budapest',
    'Вена' => 'Europe/Vienna',
    'Цюрих' => 'Europe/Zurich',
    'Брюссель' => 'Europe/Brussels',
    'Копенгаген' => 'Europe/Copenhagen',
    'Осло' => 'Europe/Oslo',
    'Дублин' => 'Europe/Dublin',
    'Лиссабон' => 'Europe/Lisbon',
    'Афины' => 'Europe/Athens',
    'Киев' => 'Europe/Kiev',
    
    // Азия
    'Токио' => 'Asia/Tokyo',
    'Пекин' => 'Asia/Shanghai',
    'Сеул' => 'Asia/Seoul',
    'Бангкок' => 'Asia/Bangkok',
    'Сингапур' => 'Asia/Singapore',
    'Гонконг' => 'Asia/Hong_Kong',
    'Мумбаи' => 'Asia/Kolkata',
    'Дели' => 'Asia/Kolkata',
    'Дубай' => 'Asia/Dubai',
    'Тегеран' => 'Asia/Tehran',
    'Стамбул' => 'Europe/Istanbul',
    'Тель-Авив' => 'Asia/Jerusalem',
    'Рияд' => 'Asia/Riyadh',
    'Доха' => 'Asia/Qatar',
    'Куала-Лумпур' => 'Asia/Kuala_Lumpur',
    'Манила' => 'Asia/Manila',
    'Ханой' => 'Asia/Ho_Chi_Minh',
    'Янгон' => 'Asia/Yangon',
    'Дакка' => 'Asia/Dhaka',
    'Карачи' => 'Asia/Karachi',
    'Ташкент' => 'Asia/Tashkent',
    'Алматы' => 'Asia/Almaty',
    'Новосибирск' => 'Asia/Novosibirsk',
    'Екатеринбург' => 'Asia/Yekaterinburg',
    'Омск' => 'Asia/Omsk',
    'Красноярск' => 'Asia/Krasnoyarsk',
    'Иркутск' => 'Asia/Irkutsk',
    'Якутск' => 'Asia/Yakutsk',
    'Владивосток' => 'Asia/Vladivostok',
    'Магадан' => 'Asia/Magadan',
    'Петропавловск-Камчатский' => 'Asia/Kamchatka',
    
    // Северная Америка
    'Нью-Йорк' => 'America/New_York',
    'Лос-Анджелес' => 'America/Los_Angeles',
    'Чикаго' => 'America/Chicago',
    'Торонто' => 'America/Toronto',
    'Ванкувер' => 'America/Vancouver',
    'Мехико' => 'America/Mexico_City',
    'Гавана' => 'America/Havana',
    'Панама' => 'America/Panama',
    'Гватемала' => 'America/Guatemala',
    'Сан-Хосе' => 'America/Costa_Rica',
    'Тегусигальпа' => 'America/Tegucigalpa',
    'Манагуа' => 'America/Managua',
    
    // Южная Америка
    'Сан-Паулу' => 'America/Sao_Paulo',
    'Буэнос-Айрес' => 'America/Argentina/Buenos_Aires',
    'Лима' => 'America/Lima',
    'Богота' => 'America/Bogota',
    'Каракас' => 'America/Caracas',
    'Сантьяго' => 'America/Santiago',
    'Ла-Пас' => 'America/La_Paz',
    'Асунсьон' => 'America/Asuncion',
    'Монтевидео' => 'America/Montevideo',
    'Кито' => 'America/Guayaquil',
    'Джорджтаун' => 'America/Guyana',
    'Парамарибо' => 'America/Paramaribo',
    
    // Африка
    'Каир' => 'Africa/Cairo',
    'Лагос' => 'Africa/Lagos',
    'Найроби' => 'Africa/Nairobi',
    'Кейптаун' => 'Africa/Johannesburg',
    'Касабланка' => 'Africa/Casablanca',
    'Алжир' => 'Africa/Algiers',
    'Тунис' => 'Africa/Tunis',
    'Триполи' => 'Africa/Tripoli',
    'Хартум' => 'Africa/Khartoum',
    'Аддис-Абеба' => 'Africa/Addis_Ababa',
    'Дар-эс-Салам' => 'Africa/Dar_es_Salaam',
    'Кампала' => 'Africa/Kampala',
    'Киншаса' => 'Africa/Kinshasa',
    'Луанда' => 'Africa/Luanda',
    'Мапуту' => 'Africa/Maputo',
    'Антананариву' => 'Indian/Antananarivo',
    'Порт-Луи' => 'Indian/Mauritius',
    
    // Океания
    'Сидней' => 'Australia/Sydney',
    'Мельбурн' => 'Australia/Melbourne',
    'Перт' => 'Australia/Perth',
    'Брисбен' => 'Australia/Brisbane',
    'Аделаида' => 'Australia/Adelaide',
    'Дарвин' => 'Australia/Darwin',
    'Окленд' => 'Pacific/Auckland',
    'Веллингтон' => 'Pacific/Auckland',
    'Фиджи' => 'Pacific/Fiji',
    'Порт-Морсби' => 'Pacific/Port_Moresby',
    'Нумеа' => 'Pacific/Noumea',
    'Хониара' => 'Pacific/Guadalcanal',
    'Порт-Вила' => 'Pacific/Efate',
    'Апиа' => 'Pacific/Apia',
    'Нукуалофа' => 'Pacific/Tongatapu',
    'Тарава' => 'Pacific/Tarawa',
    'Маджуро' => 'Pacific/Majuro',
    'Палау' => 'Pacific/Palau',
    'Понпеи' => 'Pacific/Ponape'
];

// Функция для получения времени в определенном часовом поясе
function getTimeInTimezone($timezone) {
    try {
        $dt = new DateTime('now', new DateTimeZone($timezone));
        return [
            'time' => $dt->format('H:i:s'),
            'date' => $dt->format('d.m.Y'),
            'full' => $dt->format('d.m.Y H:i:s'),
            'offset' => $dt->format('P')
        ];
    } catch (Exception $e) {
        return [
            'time' => 'N/A',
            'date' => 'N/A',
            'full' => 'N/A',
            'offset' => 'N/A'
        ];
    }
}

// Функция для расчета разности времени с Москвой
function getTimeDifferenceFromMoscow($timezone) {
    try {
        $moscowTime = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $cityTime = new DateTime('now', new DateTimeZone($timezone));
        
        // Получаем смещение в секундах
        $moscowOffset = $moscowTime->getOffset();
        $cityOffset = $cityTime->getOffset();
        
        // Вычисляем разность в часах
        $diffSeconds = $cityOffset - $moscowOffset;
        $diffHours = $diffSeconds / 3600;
        
        if ($diffHours > 0) {
            return "+" . number_format($diffHours, 1) . " ч";
        } elseif ($diffHours < 0) {
            return number_format($diffHours, 1) . " ч";
        } else {
            return "0 ч";
        }
    } catch (Exception $e) {
        return 'N/A';
    }
}

// API endpoint для получения времени
if (isset($_GET['api']) && $_GET['api'] === 'time') {
    header('Content-Type: application/json');
    
    $response = [];
    
    // Основные города
    $response['main_cities'] = [];
    foreach ($mainCities as $city => $timezone) {
        $timeData = getTimeInTimezone($timezone);
        $response['main_cities'][$city] = [
            'timezone' => $timezone,
            'time' => $timeData['time'],
            'date' => $timeData['date'],
            'full' => $timeData['full'],
            'offset' => $timeData['offset'],
            'diff_from_moscow' => getTimeDifferenceFromMoscow($timezone)
        ];
    }
    
    // Все города
    $response['all_cities'] = [];
    foreach ($allTimezones as $city => $timezone) {
        $timeData = getTimeInTimezone($timezone);
        $response['all_cities'][$city] = [
            'timezone' => $timezone,
            'time' => $timeData['time'],
            'date' => $timeData['date'],
            'full' => $timeData['full'],
            'offset' => $timeData['offset'],
            'diff_from_moscow' => getTimeDifferenceFromMoscow($timezone)
        ];
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Получаем данные для отображения
$mainCitiesData = [];
foreach ($mainCities as $city => $timezone) {
    $timeData = getTimeInTimezone($timezone);
    $mainCitiesData[$city] = [
        'timezone' => $timezone,
        'time' => $timeData['time'],
        'date' => $timeData['date'],
        'full' => $timeData['full'],
        'offset' => $timeData['offset'],
        'diff_from_moscow' => getTimeDifferenceFromMoscow($timezone)
    ];
}

$allCitiesData = [];
foreach ($allTimezones as $city => $timezone) {
    $timeData = getTimeInTimezone($timezone);
    $allCitiesData[$city] = [
        'timezone' => $timezone,
        'time' => $timeData['time'],
        'date' => $timeData['date'],
        'full' => $timeData['full'],
        'offset' => $timeData['offset'],
        'diff_from_moscow' => getTimeDifferenceFromMoscow($timezone)
    ];
}
?>