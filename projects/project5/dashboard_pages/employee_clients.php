<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-people me-2"></i>
                    Управление клиентами
                </h5>
                <div class="d-flex gap-2">
                    <input type="text" class="form-control form-control-sm" id="search-input" 
                           placeholder="Поиск по имени, телефону или email..." style="width: 300px;"
                           onkeyup="searchClients()" />
                    <button class="btn btn-primary btn-sm" onclick="showAddClientModal()">
                        <i class="bi bi-plus-circle me-1"></i>
                        Добавить клиента
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="loadClients()">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Обновить
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="clients-content">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-3 text-muted">Загрузка клиентов...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления/редактирования клиента -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="client-modal-title">Добавить клиента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="client-form">
                    <input type="hidden" id="client-id" />
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client-name" class="form-label">Имя *</label>
                                <input type="text" class="form-control" id="client-name" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client-phone" class="form-label">Телефон *</label>
                                <input type="tel" class="form-control" id="client-phone" required />
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client-email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="client-email" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="client-city" class="form-label">Город</label>
                                <select class="form-select" id="client-city">
                                    <option value="">Выберите город</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="client-address" class="form-label">Адрес</label>
                        <textarea class="form-control" id="client-address" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="client-notes" class="form-label">Примечания</label>
                        <textarea class="form-control" id="client-notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveClient()">
                    <i class="bi bi-check-circle me-1"></i>
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно деталей клиента -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали клиента</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="client-details-content">
                <!-- Содержимое загружается динамически -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<script>
let allClients = [];
let cities = [];

// Загружаем данные при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadCities();
    loadClients();
});

// Функция загрузки городов
function loadCities() {
    fetch('backend/data.php?action=get_cities')
        .then(response => response.json())
        .then(data => {
            cities = data;
            const citySelect = document.getElementById('client-city');
            citySelect.innerHTML = '<option value="">Выберите город</option>';
            
            data.forEach(city => {
                citySelect.innerHTML += `<option value="${city.id}">${escapeHtml(city.name)}</option>`;
            });
        })
        .catch(error => {
            console.error('Ошибка загрузки городов:', error);
        });
}

// Функция загрузки клиентов
function loadClients() {
    const clientsContent = document.getElementById('clients-content');
    
    fetch('backend/dashboard_api.php?action=get_all_clients')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            allClients = data;
            renderClients(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки клиентов:', error);
            clientsContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки клиентов: ${error.message}
                </div>
            `;
        });
}

// Функция поиска клиентов
function searchClients() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    
    if (!searchTerm) {
        renderClients(allClients);
        return;
    }
    
    const filteredClients = allClients.filter(client => 
        client.name.toLowerCase().includes(searchTerm) ||
        client.phone.toLowerCase().includes(searchTerm) ||
        client.email.toLowerCase().includes(searchTerm)
    );
    
    renderClients(filteredClients);
}

// Функция отображения клиентов
function renderClients(clients) {
    const clientsContent = document.getElementById('clients-content');
    
    if (clients.length === 0) {
        clientsContent.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="bi bi-person-x fs-1 mb-3"></i>
                <h5>Клиенты не найдены</h5>
                <p>Нет клиентов для отображения</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Контакты</th>
                        <th>Город</th>
                        <th>Заказы</th>
                        <th>Регистрация</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    clients.forEach(client => {
        html += `
            <tr>
                <td><strong>#${client.id}</strong></td>
                <td>
                    <div>
                        <strong>${escapeHtml(client.name)}</strong>
                        ${client.notes ? `<br><small class="text-muted">${escapeHtml(client.notes.substring(0, 50))}${client.notes.length > 50 ? '...' : ''}</small>` : ''}
                    </div>
                </td>
                <td>
                    <div>
                        <a href="tel:${client.phone}" class="text-decoration-none">
                            <i class="bi bi-telephone me-1"></i>
                            ${escapeHtml(client.phone)}
                        </a><br>
                        <a href="mailto:${client.email}" class="text-decoration-none">
                            <i class="bi bi-envelope me-1"></i>
                            ${escapeHtml(client.email)}
                        </a>
                    </div>
                </td>
                <td>
                    <small>${escapeHtml(client.city_name || 'Не указан')}</small>
                </td>
                <td>
                    <span class="badge bg-primary">${client.orders_count || 0}</span>
                </td>
                <td>
                    <small>${formatDate(client.created_at)}</small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="showClientDetails(${client.id})"
                                title="Подробнее">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="editClient(${client.id})"
                                title="Редактировать">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteClient(${client.id})"
                                title="Удалить">
                            <i class="bi bi-trash"></i>
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
                Всего клиентов: <strong>${clients.length}</strong>
            </small>
        </div>
    `;
    
    clientsContent.innerHTML = html;
}

// Функция показа модального окна добавления клиента
function showAddClientModal() {
    document.getElementById('client-modal-title').textContent = 'Добавить клиента';
    document.getElementById('client-form').reset();
    document.getElementById('client-id').value = '';
    
    const modal = new bootstrap.Modal(document.getElementById('clientModal'));
    modal.show();
}

// Функция редактирования клиента
function editClient(clientId) {
    const client = allClients.find(c => c.id == clientId);
    if (!client) {
        showNotification('Клиент не найден', 'error');
        return;
    }
    
    document.getElementById('client-modal-title').textContent = 'Редактировать клиента';
    document.getElementById('client-id').value = client.id;
    document.getElementById('client-name').value = client.name;
    document.getElementById('client-phone').value = client.phone;
    document.getElementById('client-email').value = client.email;
    document.getElementById('client-city').value = client.city_id || '';
    document.getElementById('client-address').value = client.address || '';
    document.getElementById('client-notes').value = client.notes || '';
    
    const modal = new bootstrap.Modal(document.getElementById('clientModal'));
    modal.show();
}

// Функция сохранения клиента
function saveClient() {
    const clientId = document.getElementById('client-id').value;
    const name = document.getElementById('client-name').value.trim();
    const phone = document.getElementById('client-phone').value.trim();
    const email = document.getElementById('client-email').value.trim();
    const cityId = document.getElementById('client-city').value;
    const address = document.getElementById('client-address').value.trim();
    const notes = document.getElementById('client-notes').value.trim();
    
    if (!name || !phone || !email) {
        showNotification('Заполните все обязательные поля', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', clientId ? 'update_client' : 'add_client');
    if (clientId) formData.append('client_id', clientId);
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('email', email);
    formData.append('city_id', cityId);
    formData.append('address', address);
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
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('clientModal'));
        modal.hide();
        
        loadClients();
        showNotification(clientId ? 'Клиент успешно обновлен' : 'Клиент успешно добавлен', 'success');
    })
    .catch(error => {
        console.error('Ошибка сохранения клиента:', error);
        showNotification('Ошибка сохранения клиента: ' + error.message, 'error');
    });
}

// Функция удаления клиента
function deleteClient(clientId) {
    const client = allClients.find(c => c.id == clientId);
    if (!client) {
        showNotification('Клиент не найден', 'error');
        return;
    }
    
    if (!confirm(`Удалить клиента "${client.name}"?\n\nВнимание: это действие нельзя отменить!`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_client');
    formData.append('client_id', clientId);
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        loadClients();
        showNotification('Клиент успешно удален', 'success');
    })
    .catch(error => {
        console.error('Ошибка удаления клиента:', error);
        showNotification('Ошибка удаления клиента: ' + error.message, 'error');
    });
}

// Функция показа деталей клиента
function showClientDetails(clientId) {
    const modalContent = document.getElementById('client-details-content');
    
    // Показываем загрузку
    modalContent.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3 text-muted">Загрузка деталей клиента...</p>
        </div>
    `;
    
    // Показываем модальное окно
    const modal = new bootstrap.Modal(document.getElementById('clientDetailsModal'));
    modal.show();
    
    // Загружаем детали клиента
    fetch(`backend/dashboard_api.php?action=get_client_details&client_id=${clientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            renderClientDetails(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки деталей клиента:', error);
            modalContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки деталей клиента: ${error.message}
                </div>
            `;
        });
}

// Функция отображения деталей клиента
function renderClientDetails(data) {
    const modalContent = document.getElementById('client-details-content');
    const client = data.client;
    const orders = data.orders;
    const cars = data.cars;
    
    let html = `
        <div class="row">
            <!-- Информация о клиенте -->
            <div class="col-md-6">
                <h6>Информация о клиенте</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>ID:</strong></td>
                        <td>#${client.id}</td>
                    </tr>
                    <tr>
                        <td><strong>Имя:</strong></td>
                        <td>${escapeHtml(client.name)}</td>
                    </tr>
                    <tr>
                        <td><strong>Телефон:</strong></td>
                        <td>
                            <a href="tel:${client.phone}" class="text-decoration-none">
                                ${escapeHtml(client.phone)}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>
                            <a href="mailto:${client.email}" class="text-decoration-none">
                                ${escapeHtml(client.email)}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Город:</strong></td>
                        <td>${escapeHtml(client.city_name || 'Не указан')}</td>
                    </tr>
                    <tr>
                        <td><strong>Адрес:</strong></td>
                        <td>${escapeHtml(client.address || 'Не указан')}</td>
                    </tr>
                    <tr>
                        <td><strong>Регистрация:</strong></td>
                        <td>${formatDate(client.created_at)}</td>
                    </tr>
                </table>
                
                ${client.notes ? `
                    <h6>Примечания</h6>
                    <div class="alert alert-info">
                        ${escapeHtml(client.notes)}
                    </div>
                ` : ''}
            </div>
            
            <!-- Статистика -->
            <div class="col-md-6">
                <h6>Статистика</h6>
                <div class="row">
                    <div class="col-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary">${orders.length}</h4>
                                <small class="text-muted">Заказов</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success">${cars.length}</h4>
                                <small class="text-muted">Автомобилей</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <hr>
    `;
    
    // Автомобили клиента
    if (cars.length > 0) {
        html += `
            <h6>Автомобили клиента (${cars.length})</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Марка</th>
                            <th>Модель</th>
                            <th>Год</th>
                            <th>VIN</th>
                            <th>Гос. номер</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        cars.forEach(car => {
            html += `
                <tr>
                    <td><strong>${escapeHtml(car.brand_name)}</strong></td>
                    <td>${escapeHtml(car.model_name)}</td>
                    <td>${car.year}</td>
                    <td><small class="text-muted">${escapeHtml(car.vin || 'Не указан')}</small></td>
                    <td><strong>${escapeHtml(car.license_plate || 'Не указан')}</strong></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
            <hr>
        `;
    }
    
    // История заказов
    if (orders.length > 0) {
        html += `
            <h6>История заказов (${orders.length})</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>№ заказа</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        orders.forEach(order => {
            const statusClass = getStatusClass(order.status);
            
            html += `
                <tr>
                    <td><strong>#${order.id}</strong></td>
                    <td>${formatDate(order.order_date)}</td>
                    <td>
                        <span class="badge ${statusClass}">
                            ${escapeHtml(order.status)}
                        </span>
                    </td>
                    <td><strong class="text-primary">${formatPrice(order.total_amount)} ₽</strong></td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
    } else {
        html += `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-3 mb-2"></i>
                <p>У клиента пока нет заказов</p>
            </div>
        `;
    }
    
    modalContent.innerHTML = html;
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