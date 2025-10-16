<div class="row">
    <!-- Личная информация -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-person me-2"></i>
                    Личная информация
                </h5>
            </div>
            <div class="card-body">
                <form id="profile-form">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">Имя *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Фамилия *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Телефон *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Адрес</label>
                        <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i>
                        Сохранить изменения
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Смена пароля -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>
                    Смена пароля
                </h5>
            </div>
            <div class="card-body">
                <form id="password-form">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Текущий пароль *</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                        <div class="form-text">Минимум 6 символов</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Подтвердите пароль *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-2"></i>
                        Изменить пароль
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Мои автомобили -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-car-front me-2"></i>
                    Мои автомобили
                </h5>
                <button class="btn btn-primary btn-sm" onclick="showAddCarModal()">
                    <i class="bi bi-plus-circle me-1"></i>
                    Добавить автомобиль
                </button>
            </div>
            <div class="card-body">
                <div id="cars-content">
                    <div class="text-center py-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                        <p class="mt-2 text-muted">Загрузка автомобилей...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно добавления/редактирования автомобиля -->
<div class="modal fade" id="carModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="car-modal-title">Добавить автомобиль</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="car-form">
                    <input type="hidden" id="car_id" name="car_id">
                    
                    <div class="mb-3">
                        <label for="car_brand" class="form-label">Марка *</label>
                        <select class="form-select" id="car_brand" name="brand_id" required>
                            <option value="">Выберите марку</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_model" class="form-label">Модель *</label>
                        <select class="form-select" id="car_model" name="model_id" required disabled>
                            <option value="">Сначала выберите марку</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_year" class="form-label">Год выпуска *</label>
                        <input type="number" class="form-control" id="car_year" name="year" 
                               min="1950" max="2024" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_license_plate" class="form-label">Номер автомобиля</label>
                        <input type="text" class="form-control" id="car_license_plate" 
                               name="license_plate" placeholder="А123БВ123">
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_vin" class="form-label">VIN номер</label>
                        <input type="text" class="form-control" id="car_vin" name="vin" 
                               maxlength="17" placeholder="17 символов">
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_color" class="form-label">Цвет</label>
                        <input type="text" class="form-control" id="car_color" name="color">
                    </div>
                    
                    <div class="mb-3">
                        <label for="car_mileage" class="form-label">Пробег (км)</label>
                        <input type="number" class="form-control" id="car_mileage" name="mileage" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" onclick="saveCar()">
                    <i class="bi bi-check-circle me-1"></i>
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let brandsData = [];
let modelsData = [];
let currentCarId = null;

// Загружаем данные при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    loadProfile();
    loadCars();
    loadBrands();
    
    // Обработчики форм
    document.getElementById('profile-form').addEventListener('submit', saveProfile);
    document.getElementById('password-form').addEventListener('submit', changePassword);
    
    // Обработчик изменения марки
    document.getElementById('car_brand').addEventListener('change', loadModels);
});

// Функция загрузки профиля
function loadProfile() {
    fetch('backend/dashboard_api.php?action=get_profile')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Заполняем форму
            document.getElementById('first_name').value = data.first_name || '';
            document.getElementById('last_name').value = data.last_name || '';
            document.getElementById('email').value = data.email || '';
            document.getElementById('phone').value = data.phone || '';
            document.getElementById('address').value = data.address || '';
        })
        .catch(error => {
            console.error('Ошибка загрузки профиля:', error);
            showNotification('Ошибка загрузки профиля: ' + error.message, 'error');
        });
}

// Функция сохранения профиля
function saveProfile(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    formData.append('action', 'update_profile');
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        showNotification('Профиль успешно обновлен', 'success');
    })
    .catch(error => {
        console.error('Ошибка сохранения профиля:', error);
        showNotification('Ошибка сохранения профиля: ' + error.message, 'error');
    });
}

// Функция смены пароля
function changePassword(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        showNotification('Пароли не совпадают', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    formData.append('action', 'change_password');
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        showNotification('Пароль успешно изменен', 'success');
        e.target.reset();
    })
    .catch(error => {
        console.error('Ошибка смены пароля:', error);
        showNotification('Ошибка смены пароля: ' + error.message, 'error');
    });
}

// Функция загрузки автомобилей
function loadCars() {
    const carsContent = document.getElementById('cars-content');
    
    fetch('backend/dashboard_api.php?action=get_client_cars')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            renderCars(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки автомобилей:', error);
            carsContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки автомобилей: ${error.message}
                </div>
            `;
        });
}

// Функция отображения автомобилей
function renderCars(cars) {
    const carsContent = document.getElementById('cars-content');
    
    if (cars.length === 0) {
        carsContent.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="bi bi-car-front fs-1 mb-3"></i>
                <h6>У вас пока нет добавленных автомобилей</h6>
                <p>Добавьте свой автомобиль для удобства оформления заказов</p>
            </div>
        `;
        return;
    }
    
    let html = '<div class="row">';
    
    cars.forEach(car => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-car-front me-2"></i>
                            ${escapeHtml(car.brand)} ${escapeHtml(car.model)}
                        </h6>
                        
                        <div class="mb-2">
                            <small class="text-muted">Год:</small>
                            <strong>${car.year}</strong>
                        </div>
                        
                        ${car.license_plate ? `
                            <div class="mb-2">
                                <small class="text-muted">Номер:</small>
                                <strong>${escapeHtml(car.license_plate)}</strong>
                            </div>
                        ` : ''}
                        
                        ${car.color ? `
                            <div class="mb-2">
                                <small class="text-muted">Цвет:</small>
                                <strong>${escapeHtml(car.color)}</strong>
                            </div>
                        ` : ''}
                        
                        ${car.mileage ? `
                            <div class="mb-2">
                                <small class="text-muted">Пробег:</small>
                                <strong>${formatNumber(car.mileage)} км</strong>
                            </div>
                        ` : ''}
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm flex-fill" 
                                    onclick="editCar(${car.id})">
                                <i class="bi bi-pencil me-1"></i>
                                Редактировать
                            </button>
                            <button class="btn btn-outline-danger btn-sm" 
                                    onclick="deleteCar(${car.id})"
                                    title="Удалить автомобиль">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    carsContent.innerHTML = html;
}

// Функция загрузки марок
function loadBrands() {
    fetch('backend/data.php?action=get_brands')
        .then(response => response.json())
        .then(data => {
            brandsData = data;
            
            const brandSelect = document.getElementById('car_brand');
            brandSelect.innerHTML = '<option value="">Выберите марку</option>';
            
            data.forEach(brand => {
                brandSelect.innerHTML += `<option value="${brand.id}">${escapeHtml(brand.name)}</option>`;
            });
        })
        .catch(error => {
            console.error('Ошибка загрузки марок:', error);
        });
}

// Функция загрузки моделей
function loadModels() {
    const brandId = document.getElementById('car_brand').value;
    const modelSelect = document.getElementById('car_model');
    
    if (!brandId) {
        modelSelect.innerHTML = '<option value="">Сначала выберите марку</option>';
        modelSelect.disabled = true;
        return;
    }
    
    fetch(`backend/data.php?action=get_models&brand_id=${brandId}`)
        .then(response => response.json())
        .then(data => {
            modelsData = data;
            
            modelSelect.innerHTML = '<option value="">Выберите модель</option>';
            data.forEach(model => {
                modelSelect.innerHTML += `<option value="${model.id}">${escapeHtml(model.name)}</option>`;
            });
            
            modelSelect.disabled = false;
        })
        .catch(error => {
            console.error('Ошибка загрузки моделей:', error);
            modelSelect.innerHTML = '<option value="">Ошибка загрузки</option>';
        });
}

// Функция показа модального окна добавления автомобиля
function showAddCarModal() {
    currentCarId = null;
    document.getElementById('car-modal-title').textContent = 'Добавить автомобиль';
    document.getElementById('car-form').reset();
    document.getElementById('car_model').disabled = true;
    
    const modal = new bootstrap.Modal(document.getElementById('carModal'));
    modal.show();
}

// Функция редактирования автомобиля
function editCar(carId) {
    currentCarId = carId;
    document.getElementById('car-modal-title').textContent = 'Редактировать автомобиль';
    
    // Загружаем данные автомобиля
    fetch(`backend/dashboard_api.php?action=get_car_details&car_id=${carId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            // Заполняем форму
            document.getElementById('car_id').value = data.id;
            document.getElementById('car_brand').value = data.brand_id;
            document.getElementById('car_year').value = data.year;
            document.getElementById('car_license_plate').value = data.license_plate || '';
            document.getElementById('car_vin').value = data.vin || '';
            document.getElementById('car_color').value = data.color || '';
            document.getElementById('car_mileage').value = data.mileage || '';
            
            // Загружаем модели для выбранной марки
            loadModels();
            
            // После загрузки моделей устанавливаем выбранную модель
            setTimeout(() => {
                document.getElementById('car_model').value = data.model_id;
            }, 500);
            
            const modal = new bootstrap.Modal(document.getElementById('carModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Ошибка загрузки данных автомобиля:', error);
            showNotification('Ошибка загрузки данных автомобиля: ' + error.message, 'error');
        });
}

// Функция сохранения автомобиля
function saveCar() {
    const form = document.getElementById('car-form');
    const formData = new FormData(form);
    
    const action = currentCarId ? 'update_car' : 'add_car';
    formData.append('action', action);
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('carModal'));
        modal.hide();
        
        loadCars();
        showNotification('Автомобиль успешно сохранен', 'success');
    })
    .catch(error => {
        console.error('Ошибка сохранения автомобиля:', error);
        showNotification('Ошибка сохранения автомобиля: ' + error.message, 'error');
    });
}

// Функция удаления автомобиля
function deleteCar(carId) {
    if (!confirm('Удалить этот автомобиль?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_car');
    formData.append('car_id', carId);
    
    fetch('backend/dashboard_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        loadCars();
        showNotification('Автомобиль удален', 'success');
    })
    .catch(error => {
        console.error('Ошибка удаления автомобиля:', error);
        showNotification('Ошибка удаления автомобиля: ' + error.message, 'error');
    });
}

// Вспомогательные функции
function formatNumber(number) {
    return new Intl.NumberFormat('ru-RU').format(number);
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