<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-cart me-2"></i>
                    Корзина
                </h5>
                <button class="btn btn-outline-danger btn-sm" onclick="clearCart()" id="clear-cart-btn" style="display: none;">
                    <i class="bi bi-trash me-1"></i>
                    Очистить корзину
                </button>
            </div>
            <div class="card-body">
                <div id="cart-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-3 text-muted">Загрузка корзины...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения заказа -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение заказа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите оформить заказ на сумму <strong id="order-total">0 ₽</strong>?</p>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    После оформления заказа с вами свяжется наш менеджер для уточнения деталей.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="confirmOrder()">
                    <i class="bi bi-check-circle me-1"></i>
                    Подтвердить заказ
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cartData = null;

// Загружаем корзину при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadCart();
});

// Функция загрузки корзины
function loadCart() {
    const cartContent = document.getElementById('cart-content');
    
    fetch('backend/dashboard_api.php?action=get_cart_items')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            cartData = data;
            renderCart(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки корзины:', error);
            cartContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки корзины: ${error.message}
                </div>
            `;
        });
}

// Функция отображения корзины
function renderCart(data) {
    const cartContent = document.getElementById('cart-content');
    const clearBtn = document.getElementById('clear-cart-btn');
    
    if (data.items.length === 0) {
        cartContent.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-cart-x fs-1 mb-3"></i>
                <h5>Корзина пуста</h5>
                <p>Добавьте услуги в корзину, чтобы оформить заказ</p>
                <a href="index.php#services" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Выбрать услуги
                </a>
            </div>
        `;
        clearBtn.style.display = 'none';
        return;
    }
    
    clearBtn.style.display = 'block';
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Услуга</th>
                        <th>Описание</th>
                        <th>Цена</th>
                        <th>Количество</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    data.items.forEach(item => {
        html += `
            <tr>
                <td>
                    <strong>${escapeHtml(item.name)}</strong>
                </td>
                <td>
                    <small class="text-muted">${escapeHtml(item.description || 'Без описания')}</small>
                </td>
                <td>
                    <strong>${formatPrice(item.price)} ₽</strong>
                </td>
                <td>
                    <div class="input-group" style="width: 120px;">
                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                onclick="updateQuantity(${item.service_id}, ${item.quantity - 1})">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control form-control-sm text-center" 
                               value="${item.quantity}" min="1" max="10"
                               onchange="updateQuantity(${item.service_id}, this.value)">
                        <button class="btn btn-outline-secondary btn-sm" type="button" 
                                onclick="updateQuantity(${item.service_id}, ${item.quantity + 1})">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </td>
                <td>
                    <strong class="text-primary">${formatPrice(item.total_price)} ₽</strong>
                </td>
                <td>
                    <button class="btn btn-outline-danger btn-sm" 
                            onclick="removeFromCart(${item.service_id})"
                            title="Удалить из корзины">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Обратите внимание:</strong> После оформления заказа наш менеджер свяжется с вами для уточнения деталей и согласования времени выполнения услуг.
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">Итого к оплате</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Услуги (${data.count} шт.):</span>
                            <span>${formatPrice(data.total_amount)} ₽</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Итого:</strong>
                            <strong class="text-primary fs-5">${formatPrice(data.total_amount)} ₽</strong>
                        </div>
                        <button class="btn btn-primary w-100" onclick="showOrderModal()">
                            <i class="bi bi-check-circle me-2"></i>
                            Оформить заказ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    cartContent.innerHTML = html;
}

// Функция обновления количества
function updateQuantity(serviceId, newQuantity) {
    newQuantity = parseInt(newQuantity);
    
    if (newQuantity < 1) {
        removeFromCart(serviceId);
        return;
    }
    
    if (newQuantity > 10) {
        alert('Максимальное количество одной услуги: 10');
        return;
    }
    
    const formData = new FormData();
    formData.append('service_id', serviceId);
    formData.append('quantity', newQuantity);
    
    fetch('backend/dashboard_api.php?action=update_cart_quantity', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Перезагружаем корзину
        loadCart();
        // Обновляем счетчик в меню
        updateCartCount();
    })
    .catch(error => {
        console.error('Ошибка обновления количества:', error);
        alert('Ошибка обновления количества: ' + error.message);
    });
}

// Функция удаления из корзины
function removeFromCart(serviceId) {
    if (!confirm('Удалить услугу из корзины?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('service_id', serviceId);
    
    fetch('backend/dashboard_api.php?action=remove_from_cart', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Перезагружаем корзину
        loadCart();
        // Обновляем счетчик в меню
        updateCartCount();
        
        // Показываем уведомление
        showNotification('Услуга удалена из корзины', 'success');
    })
    .catch(error => {
        console.error('Ошибка удаления из корзины:', error);
        alert('Ошибка удаления из корзины: ' + error.message);
    });
}

// Функция очистки корзины
function clearCart() {
    if (!confirm('Очистить всю корзину?')) {
        return;
    }
    
    if (!cartData || !cartData.items) {
        return;
    }
    
    // Удаляем все элементы по очереди
    const promises = cartData.items.map(item => {
        const formData = new FormData();
        formData.append('service_id', item.service_id);
        
        return fetch('backend/dashboard_api.php?action=remove_from_cart', {
            method: 'POST',
            body: formData
        });
    });
    
    Promise.all(promises)
        .then(() => {
            loadCart();
            updateCartCount();
            showNotification('Корзина очищена', 'success');
        })
        .catch(error => {
            console.error('Ошибка очистки корзины:', error);
            alert('Ошибка очистки корзины: ' + error.message);
        });
}

// Функция показа модального окна заказа
function showOrderModal() {
    if (!cartData || cartData.items.length === 0) {
        alert('Корзина пуста');
        return;
    }
    
    document.getElementById('order-total').textContent = formatPrice(cartData.total_amount) + ' ₽';
    
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    modal.show();
}

// Функция подтверждения заказа
function confirmOrder() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('orderModal'));
    modal.hide();
    
    // Показываем загрузку
    const cartContent = document.getElementById('cart-content');
    cartContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Оформление заказа...</span>
            </div>
            <p class="mt-3 text-muted">Оформление заказа...</p>
        </div>
    `;
    
    fetch('backend/dashboard_api.php?action=create_order_from_cart', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Показываем успешное сообщение
        cartContent.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <h4 class="text-success">Заказ успешно оформлен!</h4>
                <p class="text-muted mb-4">Номер заказа: <strong>#${data.order_id}</strong></p>
                <p>Наш менеджер свяжется с вами в ближайшее время для уточнения деталей.</p>
                <div class="mt-4">
                    <button class="btn btn-primary me-2" onclick="loadPage('orders')">
                        <i class="bi bi-list-ul me-2"></i>
                        Мои заказы
                    </button>
                    <a href="index.php#services" class="btn btn-outline-primary">
                        <i class="bi bi-plus-circle me-2"></i>
                        Заказать еще
                    </a>
                </div>
            </div>
        `;
        
        // Обновляем счетчики
        updateCartCount();
        updateNotificationCount();
        
        // Скрываем кнопку очистки корзины
        document.getElementById('clear-cart-btn').style.display = 'none';
        
    })
    .catch(error => {
        console.error('Ошибка оформления заказа:', error);
        
        cartContent.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Ошибка оформления заказа: ${error.message}
                <div class="mt-3">
                    <button class="btn btn-outline-danger" onclick="loadCart()">
                        <i class="bi bi-arrow-clockwise me-2"></i>
                        Попробовать снова
                    </button>
                </div>
            </div>
        `;
    });
}

// Вспомогательные функции
function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Простое уведомление (можно заменить на более красивое)
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем через 5 секунд
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>