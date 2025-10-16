<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo '<div class="alert alert-danger">Доступ запрещен</div>';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление заказами</h2>
                <button class="btn btn-success" onclick="showCreateOrderModal()">
                    <i class="fas fa-plus"></i> Создать заказ
                </button>
            </div>
        </div>
    </div>

    <!-- Фильтры -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Статус заказа:</label>
                            <select class="form-control" id="status-filter">
                                <option value="">Все статусы</option>
                                <option value="новый">Новый</option>
                                <option value="в работе">В работе</option>
                                <option value="выполнен">Выполнен</option>
                                <option value="отменен">Отменен</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Период:</label>
                            <select class="form-control" id="period-filter">
                                <option value="">Все время</option>
                                <option value="today">Сегодня</option>
                                <option value="week">Неделя</option>
                                <option value="month">Месяц</option>
                                <option value="custom">Выбрать период</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="date-range" style="display: none;">
                            <label>Дата от:</label>
                            <input type="date" class="form-control" id="date-from">
                            <label>Дата до:</label>
                            <input type="date" class="form-control" id="date-to">
                        </div>
                        <div class="col-md-3">
                            <label>Поиск:</label>
                            <input type="text" class="form-control" id="search-input" placeholder="Поиск по клиенту, ID...">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-primary" onclick="applyFilters()">Применить фильтры</button>
                            <button class="btn btn-secondary" onclick="resetFilters()">Сбросить</button>
                            <button class="btn btn-success" onclick="exportOrders()">
                                <i class="fas fa-download"></i> Экспорт
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица заказов -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Клиент</th>
                                    <th>Автомобиль</th>
                                    <th>Дата создания</th>
                                    <th>Статус</th>
                                    <th>Сумма</th>
                                    <th>Сотрудник</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody id="orders-table">
                                <tr>
                                    <td colspan="8" class="text-center">Загрузка...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Пагинация -->
                    <nav aria-label="Навигация по страницам">
                        <ul class="pagination justify-content-center" id="pagination">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра заказа -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали заказа #<span id="modal-order-id"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="order-details">
                Загрузка...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="editOrder()">Редактировать</button>
                <button type="button" class="btn btn-danger" onclick="deleteOrder()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования заказа -->
<div class="modal fade" id="editOrderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактирование заказа #<span id="edit-order-id"></span></h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="edit-order-form">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Статус:</label>
                                <select class="form-control" id="edit-status" required>
                                    <option value="новый">Новый</option>
                                    <option value="в работе">В работе</option>
                                    <option value="выполнен">Выполнен</option>
                                    <option value="отменен">Отменен</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Сотрудник:</label>
                                <select class="form-control" id="edit-employee">
                                    <option value="">Не назначен</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Примечания:</label>
                        <textarea class="form-control" id="edit-notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveOrderChanges()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentOrderId = null;

$(document).ready(function() {
    loadOrders();
    loadEmployees();
    
    // Обработчик изменения периода
    $('#period-filter').change(function() {
        if ($(this).val() === 'custom') {
            $('#date-range').show();
        } else {
            $('#date-range').hide();
        }
    });
    
    // Поиск в реальном времени
    $('#search-input').on('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
});

function loadOrders(page = 1) {
    currentPage = page;
    
    const filters = {
        action: 'get_admin_orders',
        page: page,
        status: $('#status-filter').val(),
        period: $('#period-filter').val(),
        date_from: $('#date-from').val(),
        date_to: $('#date-to').val(),
        search: $('#search-input').val()
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayOrders(response.data.orders);
                displayPagination(response.data.pagination);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки заказов', 'danger');
        }
    });
}

function displayOrders(orders) {
    let html = '';
    
    if (orders.length === 0) {
        html = '<tr><td colspan="8" class="text-center">Заказы не найдены</td></tr>';
    } else {
        orders.forEach(function(order) {
            let statusClass = getStatusClass(order.status);
            html += `
                <tr>
                    <td>#${order.id}</td>
                    <td>
                        <div>${order.client_name}</div>
                        <small class="text-muted">${order.client_phone}</small>
                    </td>
                    <td>${order.car_info || 'Не указан'}</td>
                    <td>${formatDate(order.order_date)}</td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                    <td>${order.total_amount} ₽</td>
                    <td>${order.employee_name || 'Не назначен'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails(${order.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editOrderModal(${order.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteOrder(${order.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#orders-table').html(html);
}

function displayPagination(pagination) {
    let html = '';
    
    if (pagination.total_pages > 1) {
        // Предыдущая страница
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${pagination.current_page - 1})">Предыдущая</a></li>`;
        }
        
        // Номера страниц
        for (let i = 1; i <= pagination.total_pages; i++) {
            let activeClass = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadOrders(${i})">${i}</a></li>`;
        }
        
        // Следующая страница
        if (pagination.current_page < pagination.total_pages) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadOrders(${pagination.current_page + 1})">Следующая</a></li>`;
        }
    }
    
    $('#pagination').html(html);
}

function viewOrderDetails(orderId) {
    currentOrderId = orderId;
    $('#modal-order-id').text(orderId);
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_order_details', order_id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayOrderDetails(response.data);
                $('#orderModal').modal('show');
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки деталей заказа', 'danger');
        }
    });
}

function displayOrderDetails(data) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Информация о клиенте:</h6>
                <p><strong>Имя:</strong> ${data.order.client_name}</p>
                <p><strong>Телефон:</strong> ${data.order.client_phone}</p>
                <p><strong>Email:</strong> ${data.order.client_email}</p>
            </div>
            <div class="col-md-6">
                <h6>Информация о заказе:</h6>
                <p><strong>Дата:</strong> ${formatDate(data.order.order_date)}</p>
                <p><strong>Статус:</strong> <span class="badge ${getStatusClass(data.order.status)}">${data.order.status}</span></p>
                <p><strong>Автомобиль:</strong> ${data.order.car_info || 'Не указан'}</p>
            </div>
        </div>
    `;
    
    if (data.services.length > 0) {
        html += '<h6>Услуги:</h6><ul>';
        data.services.forEach(function(service) {
            html += `<li>${service.name} - ${service.quantity} шт. × ${service.price} ₽ = ${service.total_price} ₽</li>`;
        });
        html += '</ul>';
    }
    
    if (data.parts.length > 0) {
        html += '<h6>Запчасти:</h6><ul>';
        data.parts.forEach(function(part) {
            html += `<li>${part.name} (${part.part_number}) - ${part.quantity} шт. × ${part.price} ₽ = ${part.total_price} ₽</li>`;
        });
        html += '</ul>';
    }
    
    html += `<p><strong>Общая сумма:</strong> ${data.order.total_amount} ₽</p>`;
    
    if (data.order.notes) {
        html += `<p><strong>Примечания:</strong> ${data.order.notes}</p>`;
    }
    
    $('#order-details').html(html);
}

function editOrderModal(orderId) {
    currentOrderId = orderId;
    $('#edit-order-id').text(orderId);
    
    // Загружаем данные заказа для редактирования
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_order_details', order_id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#edit-status').val(response.data.order.status);
                $('#edit-notes').val(response.data.order.notes || '');
                $('#editOrderModal').modal('show');
            }
        }
    });
}

function saveOrderChanges() {
    const data = {
        action: 'update_admin_order',
        order_id: currentOrderId,
        status: $('#edit-status').val(),
        employee_id: $('#edit-employee').val(),
        notes: $('#edit-notes').val()
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editOrderModal').modal('hide');
                showAlert('Заказ успешно обновлен', 'success');
                loadOrders(currentPage);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка обновления заказа', 'danger');
        }
    });
}

function confirmDeleteOrder(orderId) {
    if (confirm('Вы уверены, что хотите удалить этот заказ?')) {
        deleteOrderById(orderId);
    }
}

function deleteOrderById(orderId) {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'delete_admin_order', order_id: orderId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('Заказ успешно удален', 'success');
                loadOrders(currentPage);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка удаления заказа', 'danger');
        }
    });
}

function loadEmployees() {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_employees' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let html = '<option value="">Не назначен</option>';
                response.data.forEach(function(employee) {
                    html += `<option value="${employee.id}">${employee.name}</option>`;
                });
                $('#edit-employee').html(html);
            }
        }
    });
}

function applyFilters() {
    loadOrders(1);
}

function resetFilters() {
    $('#status-filter').val('');
    $('#period-filter').val('');
    $('#date-from').val('');
    $('#date-to').val('');
    $('#search-input').val('');
    $('#date-range').hide();
    loadOrders(1);
}

function exportOrders() {
    const filters = {
        status: $('#status-filter').val(),
        period: $('#period-filter').val(),
        date_from: $('#date-from').val(),
        date_to: $('#date-to').val(),
        search: $('#search-input').val()
    };
    
    const params = new URLSearchParams(filters);
    window.open(`backend/export_orders.php?${params.toString()}`, '_blank');
}

function getStatusClass(status) {
    switch(status) {
        case 'новый': return 'badge-primary';
        case 'в работе': return 'badge-warning';
        case 'выполнен': return 'badge-success';
        case 'отменен': return 'badge-danger';
        default: return 'badge-secondary';
    }
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
}
</script>