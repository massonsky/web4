<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-clipboard-check me-2"></i>
                    Управление заказами
                </h5>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" id="status-filter" onchange="filterOrders()">
                        <option value="">Все статусы</option>
                        <option value="Новый">Новые</option>
                        <option value="В работе">В работе</option>
                        <option value="Завершен">Завершенные</option>
                        <option value="Отменен">Отмененные</option>
                    </select>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadOrders()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Обновить
                    </button>
                </div>
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
    <div class="modal-dialog modal-xl">
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
                <button type="button" class="btn btn-primary" id="save-order-btn" onclick="saveOrderChanges()">
                    <i class="bi bi-check-circle me-1"></i>
                    Сохранить изменения
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let allOrders = [];
let currentOrderData = null;

// Загружаем заказы при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadOrders();
});

// Функция загрузки заказов
function loadOrders() {
    const ordersContent = document.getElementById('orders-content');
    
    fetch('backend/dashboard_api.php?action=get_all_orders')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            allOrders = data;
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

// Функция фильтрации заказов
function filterOrders() {
    const statusFilter = document.getElementById('status-filter').value;
    
    let filteredOrders = allOrders;
    if (statusFilter) {
        filteredOrders = allOrders.filter(order => order.status === statusFilter);
    }
    
    renderOrders(filteredOrders);
}

// Функция отображения заказов
function renderOrders(orders) {
    const ordersContent = document.getElementById('orders-content');
    
    if (orders.length === 0) {
        ordersContent.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 mb-3"></i>
                <h5>Заказы не найдены</h5>
                <p>Нет заказов для отображения</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>№ заказа</th>
                        <th>Клиент</th>
                        <th>Автомобиль</th>
                        <th>Дата заказа</th>
                        <th>Статус</th>
                        <th>Сумма</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    orders.forEach(order => {
        const statusClass = getStatusClass(order.status);
        const statusIcon = getStatusIcon(order.status);
        
        html += `
            <tr>
                <td>
                    <strong>#${order.id}</strong>
                </td>
                <td>
                    <div>
                        <strong>${escapeHtml(order.client_name)}</strong><br>
                        <small class="text-muted">${escapeHtml(order.client_phone)}</small>
                    </div>
                </td>
                <td>
                    <small>${escapeHtml(order.car_info || 'Не указан')}</small>
                </td>
                <td>
                    <small>${formatDate(order.order_date)}</small>
                </td>
                <td>
                    <span class="badge ${statusClass}">
                        <i class="bi ${statusIcon} me-1"></i>
                        ${escapeHtml(order.status)}
                    </span>
                </td>
                <td>
                    <strong class="text-primary">${formatPrice(order.total_amount)} ₽</strong>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="showOrderDetails(${order.id})"
                                title="Подробнее">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="quickStatusChange(${order.id}, 'В работе')"
                                title="В работу" ${order.status === 'В работе' || order.status === 'Завершен' ? 'disabled' : ''}>
                            <i class="bi bi-play"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="quickStatusChange(${order.id}, 'Завершен')"
                                title="Завершить" ${order.status === 'Завершен' || order.status === 'Отменен' ? 'disabled' : ''}>
                            <i class="bi bi-check"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <small class="text-muted">
                Всего заказов: <strong>${orders.length}</strong>
            </small>
        </div>
    `;
    
    ordersContent.innerHTML = html;
}

// Функция быстрого изменения статуса
function quickStatusChange(orderId, newStatus) {
    if (!confirm(`Изменить статус заказа #${orderId} на "${newStatus}"?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_order_status');
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        // Перезагружаем заказы
        loadOrders();
        showNotification(`Статус заказа #${orderId} изменен на "${newStatus}"`, 'success');
    })
    .catch(error => {
        console.error('Ошибка изменения статуса:', error);
        showNotification('Ошибка изменения статуса: ' + error.message, 'error');
    });
}

// Функция показа деталей заказа
function showOrderDetails(orderId) {
    const modalContent = document.getElementById('order-details-content');
    
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
        })
        .catch(error => {
            console.error('Ошибка загрузки деталей заказа:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки деталей заказа: ${error.message}
                </div>
            `;
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
            <!-- Информация о заказе -->
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
                            <select class="form-select form-select-sm" id="order-status" style="width: auto; display: inline-block;">
                                <option value="Новый" ${order.status === 'Новый' ? 'selected' : ''}>Новый</option>
                                <option value="В работе" ${order.status === 'В работе' ? 'selected' : ''}>В работе</option>
                                <option value="Завершен" ${order.status === 'Завершен' ? 'selected' : ''}>Завершен</option>
                                <option value="Отменен" ${order.status === 'Отменен' ? 'selected' : ''}>Отменен</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Общая сумма:</strong></td>
                        <td><strong class="text-primary">${formatPrice(order.total_amount)} ₽</strong></td>
                    </tr>
                </table>
            </div>
            
            <!-- Информация о клиенте -->
            <div class="col-md-6">
                <h6>Информация о клиенте</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Имя:</strong></td>
                        <td>${escapeHtml(order.client_name)}</td>
                    </tr>
                    <tr>
                        <td><strong>Телефон:</strong></td>
                        <td>
                            <a href="tel:${order.client_phone}" class="text-decoration-none">
                                ${escapeHtml(order.client_phone)}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>
                            <a href="mailto:${order.client_email}" class="text-decoration-none">
                                ${escapeHtml(order.client_email)}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Автомобиль:</strong></td>
                        <td>${escapeHtml(order.car_info || 'Не указан')}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <hr>
        
        <!-- Примечания -->
        <div class="row">
            <div class="col-12">
                <h6>Примечания к заказу</h6>
                <textarea class="form-control" id="order-notes" rows="3" placeholder="Добавьте примечания к заказу...">${escapeHtml(order.notes || '')}</textarea>
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

// Функция сохранения изменений заказа
function saveOrderChanges() {
    if (!currentOrderData) {
        return;
    }
    
    const newStatus = document.getElementById('order-status').value;
    const notes = document.getElementById('order-notes').value;
    
    const formData = new FormData();
    formData.append('action', 'update_order');
    formData.append('order_id', currentOrderData.order.id);
    formData.append('status', newStatus);
    formData.append('notes', notes);
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('orderDetailsModal'));
        modal.hide();
        
        // Перезагружаем заказы
        loadOrders();
        showNotification('Заказ успешно обновлен', 'success');
    })
    .catch(error => {
        console.error('Ошибка сохранения заказа:', error);
        showNotification('Ошибка сохранения заказа: ' + error.message, 'error');
    });
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
        month: 'short',
        day: 'numeric'
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