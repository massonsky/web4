/**
 * Основной JavaScript файл для Project 5
 * Обработка авторизации, AJAX запросов и интерфейса
 */

class AutoServiceApp {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkAuthStatus();
        this.initAnimations();
        this.loadInitialData();
    }

    /**
     * Привязка событий
     */
    bindEvents() {
        // Авторизация и регистрация
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'loginForm') {
                e.preventDefault();
                this.handleLogin(e.target);
            } else if (e.target.id === 'registerForm') {
                e.preventDefault();
                this.handleRegister(e.target);
            }
        });

        // Модальные окна
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-close') || e.target.classList.contains('modal-overlay')) {
                this.closeModal(e.target.closest('.modal'));
            }
            
            // Кнопки действий
            if (e.target.classList.contains('btn-login')) {
                this.openModal('loginModal');
            } else if (e.target.classList.contains('btn-register')) {
                this.openModal('registerModal');
            } else if (e.target.classList.contains('btn-logout')) {
                this.handleLogout();
            } else if (e.target.classList.contains('btn-admin')) {
                this.loadAdminPanel();
            } else if (e.target.classList.contains('btn-orders')) {
                this.loadUserOrders();
            }
        });

        // Навигация
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('nav-link')) {
                e.preventDefault();
                const section = e.target.getAttribute('href').substring(1);
                this.scrollToSection(section);
            }
        });

        // Формы заказов
        document.addEventListener('submit', (e) => {
            if (e.target.classList.contains('order-form')) {
                e.preventDefault();
                this.handleOrderSubmit(e.target);
            }
        });
    }

    /**
     * Проверка статуса авторизации
     */
    async checkAuthStatus() {
        try {
            const response = await fetch('backend/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=check_auth'
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.user) {
                    this.currentUser = data.user;
                    this.updateUIForLoggedInUser();
                }
            }
        } catch (error) {
            console.error('Ошибка проверки авторизации:', error);
        }
    }

    /**
     * Обработка входа
     */
    async handleLogin(form) {
        const formData = new FormData(form);
        formData.append('action', 'login');

        try {
            this.showLoader();
            const response = await fetch('backend/auth.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentUser = data.user;
                this.updateUIForLoggedInUser();
                this.closeModal(document.getElementById('loginModal'));
                this.showNotification('Добро пожаловать!', 'success');
                form.reset();
            } else {
                this.showNotification(data.error || 'Ошибка авторизации', 'error');
            }
        } catch (error) {
            console.error('Ошибка входа:', error);
            this.showNotification('Ошибка соединения', 'error');
        } finally {
            this.hideLoader();
        }
    }

    /**
     * Обработка регистрации
     */
    async handleRegister(form) {
        const formData = new FormData(form);
        formData.append('action', 'register');

        try {
            this.showLoader();
            const response = await fetch('backend/auth.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                this.closeModal(document.getElementById('registerModal'));
                this.showNotification('Регистрация успешна! Добро пожаловать!', 'success');
                form.reset();
                // Обновляем статус авторизации
                await this.checkAuthStatus();
            } else {
                if (data.errors) {
                    this.displayFormErrors(form, data.errors);
                } else {
                    this.showNotification(data.error || 'Ошибка регистрации', 'error');
                }
            }
        } catch (error) {
            console.error('Ошибка регистрации:', error);
            this.showNotification('Ошибка соединения', 'error');
        } finally {
            this.hideLoader();
        }
    }

    /**
     * Обработка выхода
     */
    async handleLogout() {
        try {
            const response = await fetch('backend/auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=logout'
            });

            const data = await response.json();
            
            if (data.success) {
                this.currentUser = null;
                this.updateUIForLoggedOutUser();
                this.showNotification('Вы успешно вышли из системы', 'success');
            }
        } catch (error) {
            console.error('Ошибка выхода:', error);
        }
    }

    /**
     * Обновление интерфейса для авторизованного пользователя
     */
    updateUIForLoggedInUser() {
        const authButtons = document.querySelector('.auth-buttons');
        const userMenu = document.querySelector('.user-menu');
        
        if (authButtons) authButtons.style.display = 'none';
        if (userMenu) {
            userMenu.style.display = 'flex';
            const userName = userMenu.querySelector('.user-name');
            if (userName && this.currentUser) {
                userName.textContent = `${this.currentUser.first_name} ${this.currentUser.last_name}`;
            }
        }

        // Показываем разделы для авторизованных пользователей
        const protectedSections = document.querySelectorAll('.protected-section');
        protectedSections.forEach(section => {
            section.style.display = 'block';
        });

        // Показываем админ панель для администраторов
        if (this.currentUser.type === 'employee' && this.currentUser.role_id >= 4) {
            const adminSection = document.querySelector('.admin-section');
            if (adminSection) adminSection.style.display = 'block';
        }
    }

    /**
     * Обновление интерфейса для неавторизованного пользователя
     */
    updateUIForLoggedOutUser() {
        const authButtons = document.querySelector('.auth-buttons');
        const userMenu = document.querySelector('.user-menu');
        
        if (authButtons) authButtons.style.display = 'flex';
        if (userMenu) userMenu.style.display = 'none';

        // Скрываем защищенные разделы
        const protectedSections = document.querySelectorAll('.protected-section, .admin-section');
        protectedSections.forEach(section => {
            section.style.display = 'none';
        });
    }

    /**
     * Загрузка административной панели
     */
    async loadAdminPanel() {
        try {
            this.showLoader();
            
            // Загружаем статистику
            const statsResponse = await fetch('backend/admin.php?action=get_statistics', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const statsData = await statsResponse.json();
            
            if (statsData.success) {
                this.renderAdminDashboard(statsData.data);
            }
            
        } catch (error) {
            console.error('Ошибка загрузки админ панели:', error);
            this.showNotification('Ошибка загрузки данных', 'error');
        } finally {
            this.hideLoader();
        }
    }

    /**
     * Отображение админ панели
     */
    renderAdminDashboard(data) {
        const adminContent = document.getElementById('adminContent');
        if (!adminContent) return;

        adminContent.innerHTML = `
            <div class="admin-dashboard">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">${data.general.total_clients}</div>
                            <div class="stat-label">Клиентов</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">${data.general.total_orders}</div>
                            <div class="stat-label">Заказов</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-ruble-sign"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">${this.formatCurrency(data.general.total_revenue)}</div>
                            <div class="stat-label">Выручка</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                        <div class="stat-info">
                            <div class="stat-number">${data.general.total_employees}</div>
                            <div class="stat-label">Сотрудников</div>
                        </div>
                    </div>
                </div>
                
                <div class="charts-grid">
                    <div class="chart-container">
                        <h3>Выручка по месяцам</h3>
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="chart-container">
                        <h3>Статусы заказов</h3>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                
                <div class="admin-tabs">
                    <div class="tab-buttons">
                        <button class="tab-btn active" data-tab="Clients">Клиенты</button>
                        <button class="tab-btn" data-tab="Employees">Сотрудники</button>
                        <button class="tab-btn" data-tab="orders">Заказы</button>
                    </div>
                    <div class="tab-content">
                        <div id="ClientsTab" class="tab-pane active">
                            <div id="ClientsTable"></div>
                        </div>
                        <div id="EmployeesTab" class="tab-pane">
                            <div id="EmployeesTable"></div>
                        </div>
                        <div id="ordersTab" class="tab-pane">
                            <div id="ordersTable"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Инициализируем графики
        this.initCharts(data);
        
        // Привязываем события табов
        this.bindTabEvents();
        
        // Загружаем данные для первой вкладки
        this.loadClientsData();
    }

    /**
     * Инициализация графиков
     */
    initCharts(data) {
        // График выручки по месяцам
        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: data.monthly.map(item => item.month),
                    datasets: [{
                        label: 'Выручка',
                        data: data.monthly.map(item => item.revenue),
                        borderColor: '#00d4ff',
                        backgroundColor: 'rgba(0, 212, 255, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                color: '#ffffff'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#ffffff'
                            }
                        }
                    }
                }
            });
        }

        // График статусов заказов
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: data.statuses.map(item => item.status_name),
                    datasets: [{
                        data: data.statuses.map(item => item.count),
                        backgroundColor: [
                            '#ff6b6b',
                            '#4ecdc4',
                            '#45b7d1',
                            '#96ceb4',
                            '#feca57'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#ffffff'
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * Привязка событий табов
     */
    bindTabEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('tab-btn')) {
                const tabName = e.target.getAttribute('data-tab');
                this.switchTab(tabName);
            }
        });
    }

    /**
     * Переключение табов
     */
     switchTab(tabName) {
         // Обновляем кнопки
         document.querySelectorAll('.tab-btn').forEach(btn => {
             btn.classList.remove('active');
         });
         document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    
         // Обновляем контент
         document.querySelectorAll('.tab-pane').forEach(pane => {
             pane.classList.remove('active');
         });
         document.getElementById(`${tabName}Tab`).classList.add('active');
    
         // Загружаем данные для вкладки
         switch (tabName) {
             case 'Clients':
                 this.loadClientsData();
                 break;
             case 'Employees':
                 this.loadEmployeesData();
                 break;
             case 'orders':
                 this.loadOrdersData();
                 break;
         }
     }

    /**
     * Загрузка данных клиентов
     */
    async loadClientsData() {
        try {
            const response = await fetch('backend/admin.php?action=get_clients', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderClientsTable(data.data);
            }
        } catch (error) {
            console.error('Ошибка загрузки клиентов:', error);
        }
    }

    /**
     * Загрузка данных сотрудников
     */
    async loadEmployeesData() {
        try {
            const response = await fetch('backend/admin.php?action=get_employees', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderEmployeesTable(data.data);
            }
        } catch (error) {
            console.error('Ошибка загрузки сотрудников:', error);
        }
    }

    /**
     * Загрузка данных заказов
     */
    async loadOrdersData() {
        try {
            const response = await fetch('backend/admin.php?action=get_orders', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.renderOrdersTable(data.data);
            }
        } catch (error) {
            console.error('Ошибка загрузки заказов:', error);
        }
    }

    /**
     * Отображение таблицы клиентов
     */
    renderClientsTable(clients) {
        const container = document.getElementById('ClientsTable');
        if (!container) return;

        container.innerHTML = `
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Email</th>
                            <th>Телефон</th>
                            <th>Город</th>
                            <th>Заказов</th>
                            <th>Потрачено</th>
                            <th>Дата регистрации</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${clients.map(client => `
                            <tr>
                                <td>${client.client_id}</td>
                                <td>${client.first_name} ${client.last_name}</td>
                                <td>${client.email}</td>
                                <td>${client.phone || '-'}</td>
                                <td>${client.city_name || '-'}</td>
                                <td>${client.total_orders}</td>
                                <td>${this.formatCurrency(client.total_spent)}</td>
                                <td>${this.formatDate(client.created_at)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    /**
     * Отображение таблицы сотрудников
     */
    renderEmployeesTable(employees) {
        const container = document.getElementById('EmployeesTable');
        if (!container) return;

        container.innerHTML = `
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Телефон</th>
                            <th>Роль</th>
                            <th>Дата найма</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${employees.map(employee => `
                            <tr>
                                <td>${employee.employee_id}</td>
                                <td>${employee.first_name} ${employee.last_name}</td>
                                <td>${employee.phone}</td>
                                <td>${employee.role_name}</td>
                                <td>${this.formatDate(employee.hire_date)}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="editEmployee(${employee.employee_id})">
                                        Редактировать
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${employee.employee_id})">
                                        Удалить
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    /**
     * Отображение таблицы заказов
     */
    renderOrdersTable(orders) {
        const container = document.getElementById('ordersTable');
        if (!container) return;

        container.innerHTML = `
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Клиент</th>
                            <th>Услуга</th>
                            <th>Автомобиль</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Дата создания</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${orders.map(order => `
                            <tr>
                                <td>#${order.order_id}</td>
                                <td>${order.client_name}</td>
                                <td>${order.service_name}</td>
                                <td>${order.car_model || 'Не указано'}</td>
                                <td><span class="status-badge status-${order.status_name.toLowerCase()}">${order.status_name}</span></td>
                                <td>${this.formatCurrency(order.total_cost)}</td>
                                <td>${this.formatDate(order.created_at)}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(${order.order_id})">
                                        Подробнее
                                    </button>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    /**
     * Инициализация анимаций
     */
    initAnimations() {
        // Анимация печатающегося текста
        const typingElements = document.querySelectorAll('.typing-text');
        typingElements.forEach(element => {
            this.typeWriter(element);
        });

        // Анимация появления элементов при скролле
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Эффект печатающегося текста
     */
    typeWriter(element) {
        const text = element.textContent;
        element.textContent = '';
        element.style.opacity = '1';
        
        let i = 0;
        const timer = setInterval(() => {
            if (i < text.length) {
                element.textContent += text.charAt(i);
                i++;
            } else {
                clearInterval(timer);
            }
        }, 100);
    }

    /**
     * Загрузка начальных данных
     */
    async loadInitialData() {
        try {
            // Загружаем города для формы регистрации
            const citiesResponse = await fetch('backend/data.php?action=get_cities', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (citiesResponse.ok) {
                const citiesData = await citiesResponse.json();
                if (citiesData.success) {
                    this.populateCitiesSelect(citiesData.data);
                }
            }

            // Загружаем услуги
            const servicesResponse = await fetch('backend/data.php?action=get_services', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (servicesResponse.ok) {
                const servicesData = await servicesResponse.json();
                if (servicesData.success) {
                    this.renderServices(servicesData.data);
                }
            }
            
        } catch (error) {
            console.error('Ошибка загрузки начальных данных:', error);
        }
    }

    /**
     * Заполнение селекта городов
     */
    populateCitiesSelect(cities) {
        const citySelects = document.querySelectorAll('select[name="city_id"]');
        citySelects.forEach(select => {
            select.innerHTML = '<option value="">Выберите город</option>' +
                cities.map(city => `<option value="${city.city_id}">${city.city_name}</option>`).join('');
        });
    }

    /**
     * Отображение услуг
     */
    renderServices(services) {
        const servicesContainer = document.getElementById('services-grid');
        if (!servicesContainer) return;

        const servicesByCategory = services.reduce((acc, service) => {
            if (!acc[service.category_name]) {
                acc[service.category_name] = [];
            }
            acc[service.category_name].push(service);
            return acc;
        }, {});

        servicesContainer.innerHTML = Object.entries(servicesByCategory).map(([category, categoryServices]) => `
            <div class="mb-12">
                <h3 class="text-2xl font-bold text-code-blue mb-6 text-center">${category}</h3>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    ${categoryServices.map(service => `
                        <div class="service-card bg-black/20 backdrop-blur-md rounded-lg p-6 border border-code-blue/20 hover:border-code-blue/50 transition-all duration-300 animate-on-scroll">
                            <div class="text-center mb-4">
                                <i class="fas ${this.getServiceIcon(service.category_name)} text-4xl text-code-blue mb-4"></i>
                            </div>
                            <h4 class="text-xl font-bold mb-3 text-center">${service.service_name}</h4>
                            <p class="text-gray-300 text-sm mb-4 text-center">${service.description || 'Профессиональное обслуживание'}</p>
                            <div class="text-center mb-4">
                                <div class="text-2xl font-bold text-auto-orange">${this.formatCurrency(service.base_price)}</div>
                                <div class="text-sm text-gray-400">~${service.duration_minutes} мин</div>
                            </div>
                            ${this.currentUser ? `
                                <button class="btn btn-primary w-full btn-order" data-service-id="${service.service_id}">
                                    <i class="fas fa-plus mr-2"></i>Заказать
                                </button>
                            ` : `
                                <button class="btn btn-auto w-full" onclick="document.querySelector('.btn-login').click()">
                                    <i class="fas fa-sign-in-alt mr-2"></i>Войти для заказа
                                </button>
                            `}
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }

    /**
     * Получение иконки для категории услуг
     */
    getServiceIcon(category) {
        const icons = {
            'Ремонт автомобилей': 'fa-wrench',
            'IT услуги': 'fa-laptop-code',
            'Диагностика': 'fa-search',
            'Установка ПО': 'fa-download',
            'default': 'fa-cog'
        };
        return icons[category] || icons.default;
    }

    /**
     * Утилиты
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB'
        }).format(amount);
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('ru-RU');
    }

    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    showLoader() {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'flex';
    }

    hideLoader() {
        const loader = document.getElementById('loader');
        if (loader) loader.style.display = 'none';
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check' : type === 'error' ? 'fa-times' : 'fa-info'}"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    displayFormErrors(form, errors) {
        // Очищаем предыдущие ошибки
        form.querySelectorAll('.error-message').forEach(el => el.remove());
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        // Отображаем новые ошибки
        Object.entries(errors).forEach(([field, message]) => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('error');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
            }
        });
    }

    /**
     * Загрузка заказов пользователя
     */
    async loadUserOrders() {
        try {
            const response = await fetch('backend/data.php?action=get_user_orders', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    this.renderUserOrders(data.data);
                }
            }
        } catch (error) {
            console.error('Ошибка загрузки заказов:', error);
        }
    }

    /**
     * Отображение заказов пользователя
     */
    renderUserOrders(orders) {
        const ordersTable = document.querySelector('#ordersTable tbody');
        if (!ordersTable) return;

        if (orders.length === 0) {
            ordersTable.innerHTML = '<tr><td colspan="6" class="text-center">У вас пока нет заказов</td></tr>';
            return;
        }

        ordersTable.innerHTML = orders.map(order => `
            <tr>
                <td>#${order.order_id}</td>
                <td>${order.service_name}</td>
                <td>${order.car_model || 'Не указано'}</td>
                <td><span class="status-badge status-${order.status_name.toLowerCase()}">${order.status_name}</span></td>
                <td>${this.formatCurrency(order.total_amount)}</td>
                <td>${this.formatDate(order.created_at)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewOrderDetails(${order.order_id})">
                        Подробнее
                    </button>
                </td>
            </tr>
        `).join('');
    }
}

// Создание экземпляра приложения и глобальных функций
document.addEventListener('DOMContentLoaded', () => {
    window.app = new AutoServiceApp();
    
    // Глобальные функции для совместимости с HTML
    window.showLoginModal = function() {
        if (window.app) {
            window.app.openModal('loginModal');
        }
    };
    
    window.showRegisterModal = function() {
        if (window.app) {
            window.app.openModal('registerModal');
        }
    };
    
    window.scrollToSection = function(sectionId) {
        if (window.app) {
            window.app.scrollToSection(sectionId);
        }
    };
    
    window.showNewOrderModal = function() {
        if (window.app) {
            window.app.openModal('newOrderModal');
        }
    };
    
    window.manageClients = function() {
        if (window.app) {
            window.app.switchTab('Clients');
        }
    };
    
    window.manageCars = function() {
        if (window.app) {
            window.app.switchTab('cars');
        }
    };
    
    window.manageServices = function() {
        if (window.app) {
            window.app.switchTab('services');
        }
    };
    
    window.showReports = function() {
        if (window.app) {
            window.app.switchTab('reports');
        }
    };
    
    window.logout = function() {
        if (window.app) {
            window.app.handleLogout();
        }
    };
});