<?php include 'index.php'; ?>
<!DOCTYPE html>
<html lang="ru" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WorldTime.API - 93 Timezones</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'code-dark': '#0d1117',
                        'code-blue': '#58a6ff',
                        'code-green': '#7ee787',
                        'code-purple': '#a5a5ff',
                        'code-orange': '#ffab70'
                    },
                    animation: {
                        'codeRain': 'codeRain 20s linear infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'scroll-left': 'scroll-left 15s linear infinite'
                    },
                    keyframes: {
                        codeRain: {
                            '0%': { transform: 'translateY(-100vh)' },
                            '100%': { transform: 'translateY(100vh)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' }
                        },
                        'scroll-left': {
                            '0%': { transform: 'translateX(100%)' },
                            '100%': { transform: 'translateX(-100%)' }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .time-card {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        .time-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(88, 166, 255, 0.2);
            border-color: #58a6ff;
        }
        .main-city-card {
            background: linear-gradient(135deg, rgba(88, 166, 255, 0.1) 0%, rgba(165, 165, 255, 0.1) 100%);
            border: 1px solid rgba(88, 166, 255, 0.3);
        }
        .moscow-card {
            background: linear-gradient(135deg, rgba(255, 171, 112, 0.1) 0%, rgba(126, 231, 135, 0.1) 100%);
            border: 1px solid rgba(255, 171, 112, 0.3);
        }
        .digital-clock {
            font-family: 'Courier New', 'Monaco', 'Menlo', monospace;
            font-weight: bold;
            text-shadow: 0 0 10px currentColor;
        }
        .pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .code-bg {
            background: linear-gradient(45deg, #0d1117 25%, transparent 25%), 
                        linear-gradient(-45deg, #0d1117 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #0d1117 75%), 
                        linear-gradient(-45deg, transparent 75%, #0d1117 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
        }
        .terminal-window {
            background: rgba(13, 17, 23, 0.95);
            border: 1px solid rgba(88, 166, 255, 0.3);
            border-radius: 8px;
        }
        .terminal-header {
            background: rgba(88, 166, 255, 0.1);
            border-bottom: 1px solid rgba(88, 166, 255, 0.3);
        }
    </style>
</head>
<body class="bg-code-dark text-white min-h-screen relative overflow-x-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0 opacity-10">
        <div class="absolute inset-0 code-bg"></div>
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
            <div class="animate-scroll-left whitespace-nowrap text-code-blue font-mono text-xs py-2">
                GET /api/timezone?city=moscow → {"time":"12:34:56","timezone":"Europe/Moscow","offset":"+03:00"} → 
                POST /api/batch → {"cities":["kabul","denver","jakarta"]} → 
                WebSocket /ws/realtime → {"type":"time_update","data":{...}} → 
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="bg-gray-900/90 backdrop-blur-sm border-b border-gray-800 shadow-2xl relative z-10">
        <div class="container mx-auto px-6 py-8">
            <!-- Navigation Links -->
            <div class="flex justify-between items-center mb-6">
                <div class="flex space-x-4">
                    <a href="../../../index.html" class="text-gray-400 hover:text-code-blue transition-colors font-mono text-sm flex items-center">
                        <i class="fas fa-home mr-2"></i>
                        Main Portfolio
                    </a>
                    <span class="text-gray-600">|</span>
                    <a href="../index.html" class="text-gray-400 hover:text-code-green transition-colors font-mono text-sm flex items-center">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Project 2 Home
                    </a>
                </div>
                <div class="text-xs text-gray-500 font-mono">
                    <span class="text-code-purple">path:</span> /projects/project2/backend/world-time.php
                </div>
            </div>
            
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="bg-code-blue/20 p-3 rounded-lg border border-code-blue/30">
                        <i class="fas fa-terminal text-2xl text-code-blue"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-white font-mono">WorldTime.API</h1>
                        <p class="text-gray-400 text-sm font-mono">Real-time timezone data for 93 locations</p>
                    </div>
                </div>
                <div class="terminal-window p-4">
                    <div class="terminal-header px-3 py-2 flex items-center space-x-2 mb-3">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-xs text-gray-400 ml-4 font-mono">system.time</span>
                    </div>
                    <div class="flex items-center space-x-3 text-code-green">
                        <i class="fas fa-clock text-code-blue"></i>
                        <span class="text-xs text-gray-400 font-mono">local_time:</span>
                        <span id="current-time" class="digital-clock text-lg text-code-green"></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-12 relative z-10">
        <!-- Основные города -->
        <section class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-white mb-4 font-mono">
                    <i class="fas fa-star text-code-orange mr-3"></i>
                    Priority Endpoints
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    Основные города с высокоприоритетными API endpoints для мониторинга времени
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                <?php foreach ($mainCitiesData as $city => $data): ?>
                <div class="time-card <?php echo $city === 'Москва' ? 'moscow-card' : 'main-city-card'; ?> rounded-xl p-6 text-white shadow-2xl hover:shadow-code-blue/20">
                    <div class="terminal-window">
                        <div class="terminal-header px-3 py-2 flex items-center space-x-2 mb-4">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-400 ml-2 font-mono"><?php echo strtolower(str_replace(' ', '_', $city)); ?>.api</span>
                        </div>
                        <div class="text-center px-2">
                            <h3 class="text-lg font-bold mb-3 font-mono text-code-blue"><?php echo $city; ?></h3>
                            <div class="bg-gray-900/50 p-3 rounded-lg mb-3">
                                <div class="digital-clock text-2xl mb-1 pulse text-<?php echo $city === 'Москва' ? 'code-orange' : 'code-green'; ?>" data-timezone="<?php echo $data['timezone']; ?>">
                                    <?php echo $data['time']; ?>
                                </div>
                            </div>
                            <div class="text-xs text-gray-400 mb-2 font-mono"><?php echo $data['date']; ?></div>
                            <div class="text-xs text-gray-500 font-mono mb-3">
                                <span class="text-code-blue">UTC</span> <?php echo $data['offset']; ?>
                            </div>
                            <?php if ($city !== 'Москва'): ?>
                            <div class="bg-gray-900/30 rounded-lg p-2 border border-gray-700">
                                <div class="text-xs text-gray-400 font-mono">moscow_diff:</div>
                                <div class="font-bold text-code-green font-mono"><?php echo $data['diff_from_moscow']; ?></div>
                            </div>
                            <?php else: ?>
                            <div class="bg-gray-900/30 rounded-lg p-2 border border-gray-700">
                                <div class="text-xs text-code-orange font-mono">base_timezone</div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Все города мира -->
        <section>
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-white mb-4 font-mono">
                    <i class="fas fa-database text-code-blue mr-3"></i>
                    Global API Endpoints
                </h2>
                <p class="text-gray-400 max-w-2xl mx-auto">
                    Полная база данных из 93 локаций с real-time обновлениями через REST API
                </p>
            </div>
            
            <!-- Поиск -->
            <div class="mb-8">
                <div class="max-w-md mx-auto">
                    <div class="terminal-window p-4">
                        <div class="terminal-header px-3 py-2 flex items-center space-x-2 mb-3">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-400 ml-2 font-mono">search.query</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-code-blue font-mono text-sm">$</span>
                            <input type="text" id="search-input" placeholder="grep -i 'city_name' timezones.db" 
                                   class="w-full pl-8 pr-4 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-white font-mono focus:ring-2 focus:ring-code-blue focus:border-code-blue placeholder-gray-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="cities-grid">
                <?php foreach ($allCitiesData as $city => $data): ?>
                <div class="time-card bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-lg p-4 shadow-lg hover:shadow-code-blue/20 city-item" data-city="<?php echo strtolower($city); ?>">
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="font-semibold text-white text-sm font-mono"><?php echo $city; ?></h3>
                        <span class="text-xs text-gray-400 font-mono bg-gray-900/50 px-2 py-1 rounded">UTC <?php echo $data['offset']; ?></span>
                    </div>
                    <div class="bg-gray-900/50 p-2 rounded-lg mb-2">
                        <div class="digital-clock text-lg text-code-blue mb-1" data-timezone="<?php echo $data['timezone']; ?>">
                            <?php echo $data['time']; ?>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 mb-3 font-mono"><?php echo $data['date']; ?></div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500 font-mono truncate"><?php echo $data['timezone']; ?></span>
                        <span class="font-medium text-code-green font-mono bg-gray-900/30 px-2 py-1 rounded"><?php echo $data['diff_from_moscow']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Статистика -->
        <section class="mt-16">
            <div class="terminal-window p-6">
                <div class="terminal-header px-4 py-3 flex items-center space-x-2 mb-6">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-sm text-gray-400 ml-4 font-mono">api.stats</span>
                </div>
                <h3 class="text-2xl font-bold text-white mb-6 flex items-center font-mono">
                    <i class="fas fa-chart-line text-code-purple mr-3"></i>
                    System Statistics
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-code-blue font-mono mb-2"><?php echo count($allCitiesData); ?></div>
                        <div class="text-sm text-gray-400 font-mono">total_endpoints</div>
                        <div class="text-xs text-gray-500 mt-2 font-mono">active: <?php echo count($allCitiesData); ?></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-code-green font-mono mb-2"><?php echo count($mainCitiesData); ?></div>
                        <div class="text-sm text-gray-400 font-mono">priority_cities</div>
                        <div class="text-xs text-gray-500 mt-2 font-mono">high_priority: true</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-6 text-center">
                        <div class="text-3xl font-bold text-code-purple font-mono mb-2" id="timezone-count">-</div>
                        <div class="text-sm text-gray-400 font-mono">unique_timezones</div>
                        <div class="text-xs text-gray-500 mt-2 font-mono">coverage: global</div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="mt-20 bg-gray-900/80 backdrop-blur-sm border-t border-gray-700">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- API Info -->
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-terminal text-code-blue text-xl mr-3"></i>
                        <h3 class="text-xl font-bold text-white font-mono">WorldTime.API</h3>
                    </div>
                    <p class="text-gray-400 mb-4 font-mono text-sm">Real-time timezone data for developers worldwide</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-code-blue transition-colors">
                            <i class="fab fa-github text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-code-green transition-colors">
                            <i class="fas fa-book text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-code-purple transition-colors">
                            <i class="fas fa-terminal text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Tech Stack -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4 font-mono">Tech Stack</h4>
                    <ul class="space-y-2 text-sm text-gray-400 font-mono">
                        <li class="flex items-center"><span class="text-code-orange mr-2">•</span> PHP 8.x</li>
                        <li class="flex items-center"><span class="text-code-blue mr-2">•</span> Tailwind CSS</li>
                        <li class="flex items-center"><span class="text-code-green mr-2">•</span> JavaScript ES6+</li>
                        <li class="flex items-center"><span class="text-code-purple mr-2">•</span> REST API</li>
                    </ul>
                </div>
                
                <!-- API Features -->
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4 font-mono">API Features</h4>
                    <ul class="space-y-2 text-sm text-gray-400 font-mono">
                        <li class="flex items-center"><span class="text-code-green mr-2">✓</span> Real-time data</li>
                        <li class="flex items-center"><span class="text-code-green mr-2">✓</span> 93+ timezones</li>
                        <li class="flex items-center"><span class="text-code-green mr-2">✓</span> JSON response</li>
                        <li class="flex items-center"><span class="text-code-green mr-2">✓</span> Fast & reliable</li>
                    </ul>
                </div>
            </div>
            
            <!-- Bottom Bar -->
            <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm font-mono mb-4 md:mb-0">
                    <span class="text-code-green">status:</span> <span class="text-green-400">online</span> | 
                    <span class="text-code-blue">uptime:</span> <span class="text-blue-400">99.9%</span> | 
                    <span class="text-code-purple">version:</span> <span class="text-purple-400">v2.1.0</span>
                </div>
                <div class="text-gray-500 text-sm font-mono">
                    &copy; 2024 WorldTime.API - Built for developers
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Обновление времени каждую секунду
        function updateTimes() {
            fetch('?api=time')
                .then(response => response.json())
                .then(data => {
                    // Обновляем основные города
                    Object.keys(data.main_cities).forEach(city => {
                        const timeElement = document.querySelector(`[data-timezone="${data.main_cities[city].timezone}"]`);
                        if (timeElement) {
                            timeElement.textContent = data.main_cities[city].time;
                            
                            // Обновляем разность с Москвой для основных городов
                            const card = timeElement.closest('.time-card, .main-city-card, .moscow-card');
                            if (card) {
                                const diffElement = card.querySelector('.font-bold.text-code-green');
                                if (diffElement && diffElement.textContent !== data.main_cities[city].diff_from_moscow) {
                                    diffElement.textContent = data.main_cities[city].diff_from_moscow;
                                }
                            }
                        }
                    });
                    
                    // Обновляем все города
                    Object.keys(data.all_cities).forEach(city => {
                        const timeElement = document.querySelector(`[data-timezone="${data.all_cities[city].timezone}"]`);
                        if (timeElement) {
                            timeElement.textContent = data.all_cities[city].time;
                            
                            // Обновляем разность с Москвой для всех городов
                            const card = timeElement.closest('.city-item');
                            if (card) {
                                const diffElement = card.querySelector('.font-medium.text-code-green');
                                if (diffElement && diffElement.textContent !== data.all_cities[city].diff_from_moscow) {
                                    diffElement.textContent = data.all_cities[city].diff_from_moscow;
                                }
                            }
                        }
                    });
                })
                .catch(error => console.error('Ошибка обновления времени:', error));
        }

        // Обновление текущего времени в заголовке
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('ru-RU');
            document.getElementById('current-time').textContent = timeString;
        }

        // Поиск городов
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cityItems = document.querySelectorAll('.city-item');
            
            cityItems.forEach(item => {
                const cityName = item.getAttribute('data-city');
                if (cityName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Подсчет уникальных часовых поясов
        function countUniqueTimezones() {
            const timezones = new Set();
            document.querySelectorAll('[data-timezone]').forEach(el => {
                timezones.add(el.getAttribute('data-timezone'));
            });
            document.getElementById('timezone-count').textContent = timezones.size;
        }

        // Инициализация
        updateCurrentTime();
        updateTimes();
        countUniqueTimezones();
        
        // Обновление каждую секунду
        setInterval(updateCurrentTime, 1000);
        setInterval(updateTimes, 1000);
    </script>
</body>
</html>