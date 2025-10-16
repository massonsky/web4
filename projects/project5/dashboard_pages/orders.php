<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-list-ul me-2"></i>
                    История заказов
                </h5>
            </div>
            <div class="card-body">
                <div id="orders-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-3 text-muted">Загрузка заказов...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно деталей заказа -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали заказа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="order-details-content">
                <!-- Содержимое загружается динамически -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="reorder-btn" style="display: none;" onclick="reorderItems()">
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Заказать еще раз
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderData = null;

// Загружаем заказы при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
});

// Функция загрузки заказов
function loadOrders() {
    const ordersContent = document.getElementById('orders-content');
    
    fetch('backend/dashboard_api.php?action=get_client_orders')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            renderOrders(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки заказов:', error);
            ordersContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки заказов: ${error.message}
                </div>
            `;
        });
}

// Функция отображения заказов
function renderOrders(orders) {
    const ordersContent = document.getElementById('orders-content');
    
    if (orders.length === 0) {
        ordersContent.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 mb-3"></i>
                <h5>У вас пока нет заказов</h5>
                <p>Оформите первый заказ, чтобы увидеть его здесь</p>
                <a href="index.php#services" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>
                    Выбрать услуги
                </a>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="row">
    `;
    
    orders.forEach(order => {
        const statusClass = getStatusClass(order.status);
        const statusIcon = getStatusIcon(order.status);
        
        html += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 order-card" data-order-id="${order.id}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Заказ #${order.id}</h6>
                        <span class="badge ${statusClass}">
                            <i class="bi ${statusIcon} me-1"></i>
                            ${escapeHtml(order.status)}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Дата заказа:</small><br>
                            <strong>${formatDate(order.order_date)}</strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Автомобиль:</small><br>
                            <strong>${escapeHtml(order.car_info || 'Не указан')}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Сумма заказа:</small><br>
                            <strong class="text-primary fs-5">${formatPrice(order.total_amount)} ₽</strong>
                        </div>
                        
                        <div class="mb-3">
                            <small class="text-muted">Услуги (${order.services_count}):</small><br>
                            <small>${escapeHtml(order.services_preview)}</small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-fill" 
                                    onclick="showOrderDetails(${order.id})">
                                <i class="bi bi-eye me-1"></i>
                                Подробнее
                            </button>
                            ${order.status === 'Завершен' ? `
                                <button class="btn btn-primary btn-sm" 
                                        onclick="quickReorder(${order.id})"
                                        title="Заказать еще раз">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += `
        </div>
    `;
    
    ordersContent.innerHTML = html;
}

// Функция показа деталей заказа
function showOrderDetails(orderId) {
    const modalContent = document.getElementById('order-details-content');
    const reorderBtn = document.getElementById('reorder-btn');
    
    // Показываем загрузку
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3 text-muted">Загрузка деталей заказа...</p>
        </div>
    `;
    
    // Показываем модальное окно
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    // Загружаем детали заказа
    fetch(`backend/dashboard_api.php?action=get_order_details&order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            currentOrderData = data;
            renderOrderDetails(data);
            
            // Показываем кнопку повторного заказа для завершенных заказов
            if (data.order.status === 'Завершен') {
                reorderBtn.style.display = 'block';
            } else {
                reorderBtn.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Ошибка загрузки деталей заказа:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки деталей заказа: ${error.message}
                </div>
            `;
            reorderBtn.style.display = 'none';
        });
}

// Функция отображения деталей заказа
function renderOrderDetails(data) {
    const modalContent = document.getElementById('order-details-content');
    const order = data.order;
    const services = data.services;
    const parts = data.parts;
    
    const statusClass = getStatusClass(order.status);
    const statusIcon = getStatusIcon(order.status);
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Информация о заказе</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Номер заказа:</strong></td>
                        <td>#${order.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Дата заказа:</strong></td>
                        <td>${formatDate(order.order_date)}</td>
                    </tr>
                    <tr>
                        <td><strong>Статус:</strong></td>
                        <td>
                            <span class="badge ${statusClass}">
                                <i class="bi ${statusIcon} me-1"></i>
                                ${escapeHtml(order.status)}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Автомобиль:</strong></td>
                        <td>${escapeHtml(order.car_info || 'Не указан')}</td>
                    </tr>
                    <tr>
                        <td><strong>Общая сумма:</strong></td>
                        <td><strong class="text-primary">${formatPrice(order.total_amount)} ₽</strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Дополнительная информация</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Дата создания:</strong></td>
                        <td>${formatDateTime(order.created_at)}</td>
                    </tr>
                    ${order.updated_at ? `
                        <tr>
                            <td><strong>Последнее обновление:</strong></td>
                            <td>${formatDateTime(order.updated_at)}</td>
                        </tr>
                    ` : ''}
                    ${order.notes ? `
                        <tr>
                            <td><strong>Примечания:</strong></td>
                            <td>${escapeHtml(order.notes)}</td>
                        </tr>
                    ` : ''}
                </table>
            </div>
        </div>
        
        <hr>
    `;
    
    // Услуги
    if (services.length > 0) {
        html += `
            <h6>Услуги (${services.length})</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Услуга</th>
                            <th>Описание</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        services.forEach(service => {
            html += `
                <tr>
                    <td><strong>${escapeHtml(service.name)}</strong></td>
                    <td><small class="text-muted">${escapeHtml(service.description || 'Без описания')}</small></td>
                    <td>${formatPrice(service.price)} ₽</td>
                    <td>${service.quantity}</td>
                    <td><strong>${formatPrice(service.total_price)} ₽</strong></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    }
    
    // Запчасти
    if (parts.length > 0) {
        html += `
            <hr>
            <h6>Запчасти (${parts.length})</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Запчасть</th>
                            <th>Артикул</th>
                            <th>Цена</th>
                            <th>Количество</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        parts.forEach(part => {
            html += `
                <tr>
                    <td><strong>${escapeHtml(part.name)}</strong></td>
                    <td><small class="text-muted">${escapeHtml(part.part_number || 'Не указан')}</small></td>
                    <td>${formatPrice(part.price)} ₽</td>
                    <td>${part.quantity}</td>
                    <td><strong>${formatPrice(part.total_price)} ₽</strong></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    }
    
    modalContent.innerHTML = html;
}

// Функция быстрого повторного заказа
function quickReorder(orderId) {
    if (!confirm('Добавить услуги из этого заказа в корзину?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    
    fetch('backend/dashboard_api.php?action=reorder', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Обновляем счетчик корзины
        updateCartCount();
        
        // Показываем уведомление
        showNotification(`Услуги добавлены в корзину (${data.added_count} шт.)`, 'success');
        
        // Предлагаем перейти в корзину
        if (confirm('Услуги добавлены в корзину. Перейти в корзину?')) {
            loadPage('cart');
        }
    })
    .catch(error => {
        console.error('Ошибка повторного заказа:', error);
        alert('Ошибка повторного заказа: ' + error.message);
    });
}

// Функция повторного заказа из модального окна
function reorderItems() {
    if (!currentOrderData) {
        return;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('orderDetailsModal'));
    modal.hide();
    
    quickReorder(currentOrderData.order.id);
}

// Вспомогательные функции
function getStatusClass(status) {
    switch (status) {
        case 'Новый':
            return 'bg-primary';
        case 'В работе':
            return 'bg-warning text-dark';
        case 'Завершен':
            return 'bg-success';
        case 'Отменен':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}

function getStatusIcon(status) {
    switch (status) {
        case 'Новый':
            return 'bi-clock';
        case 'В работе':
            return 'bi-gear';
        case 'Завершен':
            return 'bi-check-circle';
        case 'Отменен':
            return 'bi-x-circle';
        default:
            return 'bi-question-circle';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
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
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
}
</script>

<style>
.order-card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>