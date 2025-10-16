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
                <h2>Управление клиентами</h2>
                <button class="btn btn-success" onclick="showAddClientModal()">
                    <i class="fas fa-plus"></i> Добавить клиента
                </button>
            </div>
        </div>
    </div>

    <!-- Фильтры и поиск -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label>Поиск:</label>
                            <input type="text" class="form-control" id="search-input" placeholder="Поиск по имени, телефону, email...">
                        </div>
                        <div class="col-md-3">
                            <label>Город:</label>
                            <select class="form-control" id="city-filter">
                                <option value="">Все города</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Период регистрации:</label>
                            <select class="form-control" id="period-filter">
                                <option value="">Все время</option>
                                <option value="today">Сегодня</option>
                                <option value="week">Неделя</option>
                                <option value="month">Месяц</option>
                                <option value="year">Год</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-primary btn-block" onclick="applyFilters()">Применить</button>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button class="btn btn-secondary" onclick="resetFilters()">Сбросить фильтры</button>
                            <button class="btn btn-success" onclick="exportClients()">
                                <i class="fas fa-download"></i> Экспорт
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица клиентов -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Телефон</th>
                                    <th>Email</th>
                                    <th>Город</th>
                                    <th>Заказов</th>
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody id="clients-table">
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

<!-- Модальное окно просмотра клиента -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Информация о клиенте</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="client-details">
                Загрузка...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="editClientModal()">Редактировать</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteClient()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления/редактирования клиента -->
<div class="modal fade" id="editClientModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit-modal-title">Добавить клиента</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="client-form">
                    <input type="hidden" id="client-id" value="">
                    <div class="form-group">
                        <label>Имя *:</label>
                        <input type="text" class="form-control" id="client-name" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон *:</label>
                        <input type="tel" class="form-control" id="client-phone" required>
                    </div>
                    <div class="form-group">
                        <label>Email *:</label>
                        <input type="email" class="form-control" id="client-email" required>
                    </div>
                    <div class="form-group">
                        <label>Город:</label>
                        <select class="form-control" id="client-city">
                            <option value="">Выберите город</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Адрес:</label>
                        <textarea class="form-control" id="client-address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Примечания:</label>
                        <textarea class="form-control" id="client-notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveClient()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let currentClientId = null;

$(document).ready(function() {
    loadClients();
    loadCities();
    
    // Поиск в реальном времени
    $('#search-input').on('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
});

function loadClients(page = 1) {
    currentPage = page;
    
    const filters = {
        action: 'get_admin_clients',
        page: page,
        search: $('#search-input').val(),
        city_id: $('#city-filter').val(),
        period: $('#period-filter').val()
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayClients(response.data.clients);
                displayPagination(response.data.pagination);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки клиентов', 'danger');
        }
    });
}

function displayClients(clients) {
    let html = '';
    
    if (clients.length === 0) {
        html = '<tr><td colspan="8" class="text-center">Клиенты не найдены</td></tr>';
    } else {
        clients.forEach(function(client) {
            html += `
                <tr>
                    <td>#${client.id}</td>
                    <td>${client.name}</td>
                    <td>${client.phone}</td>
                    <td>${client.email}</td>
                    <td>${client.city_name || 'Не указан'}</td>
                    <td>
                        <span class="badge badge-info">${client.orders_count}</span>
                    </td>
                    <td>${formatDate(client.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewClientDetails(${client.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editClientModal(${client.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteClient(${client.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#clients-table').html(html);
}

function displayPagination(pagination) {
    let html = '';
    
    if (pagination.total_pages > 1) {
        // Предыдущая страница
        if (pagination.current_page > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadClients(${pagination.current_page - 1})">Предыдущая</a></li>`;
        }
        
        // Номера страниц
        for (let i = 1; i <= pagination.total_pages; i++) {
            let activeClass = i === pagination.current_page ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="loadClients(${i})">${i}</a></li>`;
        }
        
        // Следующая страница
        if (pagination.current_page < pagination.total_pages) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="loadClients(${pagination.current_page + 1})">Следующая</a></li>`;
        }
    }
    
    $('#pagination').html(html);
}

function viewClientDetails(clientId) {
    currentClientId = clientId;
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_client_details', client_id: clientId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayClientDetails(response.data);
                $('#clientModal').modal('show');
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки данных клиента', 'danger');
        }
    });
}

function displayClientDetails(data) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Личная информация:</h6>
                <p><strong>Имя:</strong> ${data.client.name}</p>
                <p><strong>Телефон:</strong> ${data.client.phone}</p>
                <p><strong>Email:</strong> ${data.client.email}</p>
                <p><strong>Город:</strong> ${data.client.city_name || 'Не указан'}</p>
                <p><strong>Адрес:</strong> ${data.client.address || 'Не указан'}</p>
                <p><strong>Дата регистрации:</strong> ${formatDate(data.client.created_at)}</p>
            </div>
            <div class="col-md-6">
                <h6>Статистика:</h6>
                <p><strong>Всего заказов:</strong> ${data.orders.length}</p>
                <p><strong>Автомобилей:</strong> ${data.cars.length}</p>
            </div>
        </div>
    `;
    
    if (data.client.notes) {
        html += `<p><strong>Примечания:</strong> ${data.client.notes}</p>`;
    }
    
    if (data.cars.length > 0) {
        html += '<h6>Автомобили:</h6><ul>';
        data.cars.forEach(function(car) {
            html += `<li>${car.brand_name} ${car.model_name} (${car.year}) - ${car.license_plate}</li>`;
        });
        html += '</ul>';
    }
    
    if (data.orders.length > 0) {
        html += '<h6>Последние заказы:</h6>';
        html += '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>ID</th><th>Дата</th><th>Статус</th><th>Сумма</th></tr></thead><tbody>';
        data.orders.slice(0, 5).forEach(function(order) {
            let statusClass = getStatusClass(order.status);
            html += `
                <tr>
                    <td>#${order.id}</td>
                    <td>${formatDate(order.order_date)}</td>
                    <td><span class="badge ${statusClass}">${order.status}</span></td>
                    <td>${order.total_amount} ₽</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
    }
    
    $('#client-details').html(html);
}

function showAddClientModal() {
    currentClientId = null;
    $('#edit-modal-title').text('Добавить клиента');
    $('#client-form')[0].reset();
    $('#client-id').val('');
    $('#editClientModal').modal('show');
}

function editClientModal(clientId = null) {
    if (clientId) {
        currentClientId = clientId;
        $('#edit-modal-title').text('Редактировать клиента');
        
        // Загружаем данные клиента
        $.ajax({
            url: 'backend/dashboard_api.php',
            method: 'POST',
            data: { action: 'get_client_details', client_id: clientId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const client = response.data.client;
                    $('#client-id').val(client.id);
                    $('#client-name').val(client.name);
                    $('#client-phone').val(client.phone);
                    $('#client-email').val(client.email);
                    $('#client-city').val(client.city_id);
                    $('#client-address').val(client.address);
                    $('#client-notes').val(client.notes);
                    
                    $('#clientModal').modal('hide');
                    $('#editClientModal').modal('show');
                }
            }
        });
    } else {
        showAddClientModal();
    }
}

function saveClient() {
    const clientId = $('#client-id').val();
    const isEdit = clientId !== '';
    
    const data = {
        action: isEdit ? 'update_client' : 'add_client',
        client_id: clientId,
        name: $('#client-name').val(),
        phone: $('#client-phone').val(),
        email: $('#client-email').val(),
        city_id: $('#client-city').val(),
        address: $('#client-address').val(),
        notes: $('#client-notes').val()
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editClientModal').modal('hide');
                showAlert(isEdit ? 'Клиент успешно обновлен' : 'Клиент успешно добавлен', 'success');
                loadClients(currentPage);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка сохранения клиента', 'danger');
        }
    });
}

function confirmDeleteClient(clientId = null) {
    const id = clientId || currentClientId;
    if (confirm('Вы уверены, что хотите удалить этого клиента? Это действие нельзя отменить.')) {
        deleteClient(id);
    }
}

function deleteClient(clientId) {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'delete_client', client_id: clientId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#clientModal').modal('hide');
                showAlert('Клиент успешно удален', 'success');
                loadClients(currentPage);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка удаления клиента', 'danger');
        }
    });
}

function loadCities() {
    $.ajax({
        url: 'backend/data.php',
        method: 'POST',
        data: { action: 'get_cities' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let filterHtml = '<option value="">Все города</option>';
                let modalHtml = '<option value="">Выберите город</option>';
                
                response.data.forEach(function(city) {
                    filterHtml += `<option value="${city.id}">${city.name}</option>`;
                    modalHtml += `<option value="${city.id}">${city.name}</option>`;
                });
                
                $('#city-filter').html(filterHtml);
                $('#client-city').html(modalHtml);
            }
        }
    });
}

function applyFilters() {
    loadClients(1);
}

function resetFilters() {
    $('#search-input').val('');
    $('#city-filter').val('');
    $('#period-filter').val('');
    loadClients(1);
}

function exportClients() {
    const filters = {
        search: $('#search-input').val(),
        city_id: $('#city-filter').val(),
        period: $('#period-filter').val()
    };
    
    const params = new URLSearchParams(filters);
    window.open(`backend/export_clients.php?${params.toString()}`, '_blank');
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