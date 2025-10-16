// Управление модальными окнами и основные функции

// Функции для показа модальных окон
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function showRegisterModal() {
    const modal = document.getElementById('registerModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadCities(); // Загружаем города при открытии формы регистрации
    }
}

function showNewOrderModal() {
    const modal = document.getElementById('newOrderModal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        loadServices(); // Загружаем услуги при открытии формы заказа
    }
}

// Функция для закрытия модального окна
function closeModal(modal) {
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
        
        // Очищаем формы
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => {
            form.reset();
            clearFormErrors(form);
        });
    }
}

// Функция для плавной прокрутки к секции
function scrollToSection(sectionId) {
    const element = document.getElementById(sectionId);
    if (element) {
        const headerHeight = document.querySelector('.header')?.offsetHeight || 80;
        const elementPosition = element.offsetTop - headerHeight;
        
        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });
    }
}

// Функция для показа/скрытия лоадера
function showLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'flex';
    }
}

function hideLoader() {
    const loader = document.getElementById('loader');
    if (loader) {
        loader.style.display = 'none';
    }
}

// Функция для загрузки городов
async function loadCities() {
    try {
        const response = await fetch('backend/data.php?action=get_cities', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const cities = await response.json();
        
        const citySelect = document.getElementById('registerCity');
        if (citySelect && cities.success) {
            citySelect.innerHTML = '<option value="">Выберите город</option>';
            cities.data.forEach(city => {
                const option = document.createElement('option');
                option.value = city.city_id;
                option.textContent = city.city_name;
                citySelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Ошибка загрузки городов:', error);
    }
}

// Функция для загрузки услуг
async function loadServices() {
    try {
        const response = await fetch('backend/data.php?action=get_services', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Неверный формат ответа: ${text.slice(0, 200)}`);
        }
        const services = await response.json();
        
        const serviceSelect = document.getElementById('orderService');
        if (serviceSelect && services.success) {
            serviceSelect.innerHTML = '<option value="">Выберите услугу</option>';
            services.data.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.textContent = `${service.name} - ${service.price}₽`;
                serviceSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Ошибка загрузки услуг:', error);
    }
}

// Функция для очистки ошибок формы
function clearFormErrors(form) {
    const errorElements = form.querySelectorAll('.error-message');
    errorElements.forEach(el => el.remove());
    
    const errorInputs = form.querySelectorAll('.error');
    errorInputs.forEach(input => input.classList.remove('error'));
}

// Функция для показа ошибки поля
function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('error');
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    field.parentNode.appendChild(errorDiv);
}

// Функция для очистки ошибки поля
function clearFieldError(field) {
    field.classList.remove('error');
    
    const existingError = field.parentNode.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
}

// Функция для показа уведомления
function showNotification(message, type = 'success') {
    // Создаем элемент уведомления
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Добавляем стили для уведомлений
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 8px;
                color: white;
                z-index: 3000;
                animation: slideIn 0.3s ease;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            }
            
            .notification-success {
                background: linear-gradient(135deg, #4CAF50, #45a049);
                border-left: 4px solid #2E7D32;
            }
            
            .notification-error {
                background: linear-gradient(135deg, #f44336, #d32f2f);
                border-left: 4px solid #c62828;
            }
            
            .notification-content {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем уведомление через 5 секунд
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    // Обработчики для закрытия модальных окон
    document.addEventListener('click', function(e) {
        // Закрытие по клику на overlay
        if (e.target.classList.contains('modal-overlay')) {
            const modal = e.target.closest('.modal');
            closeModal(modal);
        }
        
        // Закрытие по кнопке закрытия
        if (e.target.classList.contains('modal-close')) {
            const modal = e.target.closest('.modal');
            closeModal(modal);
        }
    });
    
    // Закрытие модальных окон по Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                closeModal(activeModal);
            }
        }
    });
    
    // Обработчик формы входа
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                showLoader();
                
                const response = await fetch('backend/auth.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Вход выполнен успешно!', 'success');
                    closeModal(document.getElementById('loginModal'));
                    
                    // Перезагружаем страницу для обновления интерфейса
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(result.message || 'Ошибка входа', 'error');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showNotification('Произошла ошибка при входе', 'error');
            } finally {
                hideLoader();
            }
        });
    }
    
    // Обработчик формы регистрации
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Проверяем совпадение паролей
            const password = document.getElementById('registerPassword').value;
            const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
            
            if (password !== passwordConfirm) {
                showFieldError(document.getElementById('registerPasswordConfirm'), 'Пароли не совпадают');
                return;
            }
            
            const formData = new FormData(this);
            formData.append('action', 'register');
            
            try {
                showLoader();
                
                const response = await fetch('backend/auth.php', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Регистрация прошла успешно!', 'success');
                    closeModal(document.getElementById('registerModal'));
                    showLoginModal();
                } else {
                    showNotification(result.message || 'Ошибка регистрации', 'error');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showNotification('Произошла ошибка при регистрации', 'error');
            } finally {
                hideLoader();
            }
        });
    }
    
    // Обработчик формы нового заказа
    const newOrderForm = document.getElementById('newOrderForm');
    if (newOrderForm) {
        newOrderForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                showLoader();
                
                const response = await fetch('api/orders.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Заказ создан успешно!', 'success');
                    closeModal(document.getElementById('newOrderModal'));
                    
                    // Обновляем список заказов если мы на странице заказов
                    if (typeof loadOrders === 'function') {
                        loadOrders();
                    }
                } else {
                    showNotification(result.message || 'Ошибка создания заказа', 'error');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                showNotification('Произошла ошибка при создании заказа', 'error');
            } finally {
                hideLoader();
            }
        });
    }
});

// Экспортируем функции для глобального использования
window.showLoginModal = showLoginModal;
window.showRegisterModal = showRegisterModal;
window.showNewOrderModal = showNewOrderModal;
window.closeModal = closeModal;
window.scrollToSection = scrollToSection;
window.showLoader = showLoader;
window.hideLoader = hideLoader;
window.showNotification = showNotification;