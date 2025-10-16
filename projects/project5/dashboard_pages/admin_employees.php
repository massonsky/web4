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
                <h2>Управление сотрудниками</h2>
                <button class="btn btn-success" onclick="showAddEmployeeModal()">
                    <i class="fas fa-plus"></i> Добавить сотрудника
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
                        <div class="col-md-4">
                            <label>Поиск:</label>
                            <input type="text" class="form-control" id="search-input" placeholder="Поиск по имени, email...">
                        </div>
                        <div class="col-md-3">
                            <label>Роль:</label>
                            <select class="form-control" id="role-filter">
                                <option value="">Все роли</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Статус:</label>
                            <select class="form-control" id="status-filter">
                                <option value="">Все</option>
                                <option value="active">Активные</option>
                                <option value="inactive">Неактивные</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <div>
                                <button class="btn btn-primary btn-block" onclick="applyFilters()">Применить</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Таблица сотрудников -->
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
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Роль</th>
                                    <th>Статус</th>
                                    <th>Дата найма</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody id="employees-table">
                                <tr>
                                    <td colspan="8" class="text-center">Загрузка...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно просмотра сотрудника -->
<div class="modal fade" id="employeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Информация о сотруднике</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="employee-details">
                Загрузка...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" onclick="editEmployeeModal()">Редактировать</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteEmployee()">Удалить</button>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления/редактирования сотрудника -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit-modal-title">Добавить сотрудника</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="employee-form">
                    <input type="hidden" id="employee-id" value="">
                    <div class="form-group">
                        <label>Имя *:</label>
                        <input type="text" class="form-control" id="employee-name" required>
                    </div>
                    <div class="form-group">
                        <label>Email *:</label>
                        <input type="email" class="form-control" id="employee-email" required>
                    </div>
                    <div class="form-group">
                        <label>Телефон:</label>
                        <input type="tel" class="form-control" id="employee-phone">
                    </div>
                    <div class="form-group">
                        <label>Роль *:</label>
                        <select class="form-control" id="employee-role" required>
                            <option value="">Выберите роль</option>
                        </select>
                    </div>
                    <div class="form-group" id="password-group">
                        <label>Пароль *:</label>
                        <input type="password" class="form-control" id="employee-password">
                        <small class="form-text text-muted">Оставьте пустым для сохранения текущего пароля</small>
                    </div>
                    <div class="form-group">
                        <label>Зарплата:</label>
                        <input type="number" class="form-control" id="employee-salary" step="0.01">
                    </div>
                    <div class="form-group">
                        <label>Дата найма:</label>
                        <input type="date" class="form-control" id="employee-hire-date">
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="employee-active" checked>
                            <label class="form-check-label" for="employee-active">Активный</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveEmployee()">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentEmployeeId = null;

$(document).ready(function() {
    loadEmployees();
    loadRoles();
    
    // Поиск в реальном времени
    $('#search-input').on('input', function() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            applyFilters();
        }, 500);
    });
});

function loadEmployees() {
    const filters = {
        action: 'get_admin_employees',
        search: $('#search-input').val(),
        role_id: $('#role-filter').val(),
        status: $('#status-filter').val()
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: filters,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayEmployees(response.data);
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки сотрудников', 'danger');
        }
    });
}

function displayEmployees(employees) {
    let html = '';
    
    if (employees.length === 0) {
        html = '<tr><td colspan="8" class="text-center">Сотрудники не найдены</td></tr>';
    } else {
        employees.forEach(function(employee) {
            let statusBadge = employee.is_active ? 
                '<span class="badge badge-success">Активный</span>' : 
                '<span class="badge badge-secondary">Неактивный</span>';
                
            html += `
                <tr>
                    <td>#${employee.id}</td>
                    <td>${employee.name}</td>
                    <td>${employee.email}</td>
                    <td>${employee.phone || 'Не указан'}</td>
                    <td>${employee.role_name}</td>
                    <td>${statusBadge}</td>
                    <td>${formatDate(employee.hire_date)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="viewEmployeeDetails(${employee.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="editEmployeeModal(${employee.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteEmployee(${employee.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }
    
    $('#employees-table').html(html);
}

function viewEmployeeDetails(employeeId) {
    currentEmployeeId = employeeId;
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_employee_details', employee_id: employeeId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayEmployeeDetails(response.data);
                $('#employeeModal').modal('show');
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки данных сотрудника', 'danger');
        }
    });
}

function displayEmployeeDetails(employee) {
    let statusBadge = employee.is_active ? 
        '<span class="badge badge-success">Активный</span>' : 
        '<span class="badge badge-secondary">Неактивный</span>';
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6>Личная информация:</h6>
                <p><strong>Имя:</strong> ${employee.name}</p>
                <p><strong>Email:</strong> ${employee.email}</p>
                <p><strong>Телефон:</strong> ${employee.phone || 'Не указан'}</p>
                <p><strong>Роль:</strong> ${employee.role_name}</p>
                <p><strong>Статус:</strong> ${statusBadge}</p>
            </div>
            <div class="col-md-6">
                <h6>Рабочая информация:</h6>
                <p><strong>Дата найма:</strong> ${formatDate(employee.hire_date)}</p>
                <p><strong>Зарплата:</strong> ${employee.salary ? employee.salary + ' ₽' : 'Не указана'}</p>
                <p><strong>Дата создания:</strong> ${formatDate(employee.created_at)}</p>
            </div>
        </div>
    `;
    
    $('#employee-details').html(html);
}

function showAddEmployeeModal() {
    currentEmployeeId = null;
    $('#edit-modal-title').text('Добавить сотрудника');
    $('#employee-form')[0].reset();
    $('#employee-id').val('');
    $('#employee-active').prop('checked', true);
    $('#password-group small').hide();
    $('#employee-password').prop('required', true);
    $('#editEmployeeModal').modal('show');
}

function editEmployeeModal(employeeId = null) {
    if (employeeId) {
        currentEmployeeId = employeeId;
        $('#edit-modal-title').text('Редактировать сотрудника');
        
        // Загружаем данные сотрудника
        $.ajax({
            url: 'backend/dashboard_api.php',
            method: 'POST',
            data: { action: 'get_employee_details', employee_id: employeeId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const employee = response.data;
                    $('#employee-id').val(employee.id);
                    $('#employee-name').val(employee.name);
                    $('#employee-email').val(employee.email);
                    $('#employee-phone').val(employee.phone);
                    $('#employee-role').val(employee.role_id);
                    $('#employee-salary').val(employee.salary);
                    $('#employee-hire-date').val(employee.hire_date);
                    $('#employee-active').prop('checked', employee.is_active);
                    $('#employee-password').val('');
                    $('#password-group small').show();
                    $('#employee-password').prop('required', false);
                    
                    $('#employeeModal').modal('hide');
                    $('#editEmployeeModal').modal('show');
                }
            }
        });
    } else {
        showAddEmployeeModal();
    }
}

function saveEmployee() {
    const employeeId = $('#employee-id').val();
    const isEdit = employeeId !== '';
    
    const data = {
        action: isEdit ? 'update_employee' : 'add_employee',
        employee_id: employeeId,
        name: $('#employee-name').val(),
        email: $('#employee-email').val(),
        phone: $('#employee-phone').val(),
        role_id: $('#employee-role').val(),
        password: $('#employee-password').val(),
        salary: $('#employee-salary').val(),
        hire_date: $('#employee-hire-date').val(),
        is_active: $('#employee-active').is(':checked') ? 1 : 0
    };
    
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#editEmployeeModal').modal('hide');
                showAlert(isEdit ? 'Сотрудник успешно обновлен' : 'Сотрудник успешно добавлен', 'success');
                loadEmployees();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка сохранения сотрудника', 'danger');
        }
    });
}

function confirmDeleteEmployee(employeeId = null) {
    const id = employeeId || currentEmployeeId;
    if (confirm('Вы уверены, что хотите удалить этого сотрудника? Это действие нельзя отменить.')) {
        deleteEmployee(id);
    }
}

function deleteEmployee(employeeId) {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'delete_employee', employee_id: employeeId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#employeeModal').modal('hide');
                showAlert('Сотрудник успешно удален', 'success');
                loadEmployees();
            } else {
                showAlert(response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка удаления сотрудника', 'danger');
        }
    });
}

function loadRoles() {
    $.ajax({
        url: 'backend/data.php',
        method: 'POST',
        data: { action: 'get_employee_roles' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                let filterHtml = '<option value="">Все роли</option>';
                let modalHtml = '<option value="">Выберите роль</option>';
                
                response.data.forEach(function(role) {
                    filterHtml += `<option value="${role.id}">${role.name}</option>`;
                    modalHtml += `<option value="${role.id}">${role.name}</option>`;
                });
                
                $('#role-filter').html(filterHtml);
                $('#employee-role').html(modalHtml);
            }
        }
    });
}

function applyFilters() {
    loadEmployees();
}

function formatDate(dateString) {
    if (!dateString) return 'Не указана';
    const date = new Date(dateString);
    return date.toLocaleDateString('ru-RU');
}
</script>