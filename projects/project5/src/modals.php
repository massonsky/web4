<!-- Модальные окна -->

<!-- Модальное окно входа -->
<div id="loginModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-sign-in-alt mr-2"></i>Вход в систему</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <input type="email" id="loginEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Пароль</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>Войти
                </button>
            </form>
            <div class="auth-links">
                <p>Нет аккаунта? <a href="#" onclick="closeModal(document.getElementById('loginModal')); showRegisterModal();">Зарегистрироваться</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно регистрации -->
<div id="registerModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user-plus mr-2"></i>Регистрация</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="registerForm" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="registerFirstName">Имя</label>
                        <input type="text" id="registerFirstName" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="registerLastName">Фамилия</label>
                        <input type="text" id="registerLastName" name="last_name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <input type="email" id="registerEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="registerPhone">Телефон</label>
                    <input type="tel" id="registerPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="registerCity">Город</label>
                    <select id="registerCity" name="city_id">
                        <option value="">Выберите город</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Пароль</label>
                    <input type="password" id="registerPassword" name="password" required>
                </div>
                <div class="form-group">
                    <label for="registerPasswordConfirm">Подтвердите пароль</label>
                    <input type="password" id="registerPasswordConfirm" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-user-plus"></i>Зарегистрироваться
                </button>
            </form>
            <div class="auth-links">
                <p>Уже есть аккаунт? <a href="#" onclick="closeModal(document.getElementById('registerModal')); showLoginModal();">Войти</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно нового заказа -->
<div id="newOrderModal" class="modal">
    <div class="modal-overlay"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-plus mr-2"></i>Новый заказ</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="newOrderForm" class="order-form">
                <div class="form-group">
                    <label for="orderService">Услуга</label>
                    <select id="orderService" name="service_id" required>
                        <option value="">Выберите услугу</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="orderCarModel">Модель автомобиля</label>
                    <input type="text" id="orderCarModel" name="car_model" placeholder="Например: Toyota Camry 2020">
                </div>
                <div class="form-group">
                    <label for="orderDescription">Описание проблемы</label>
                    <textarea id="orderDescription" name="description" rows="4" placeholder="Опишите проблему или требования к работе"></textarea>
                </div>
                <div class="form-group">
                    <label for="orderDate">Желаемая дата</label>
                    <input type="date" id="orderDate" name="preferred_date">
                </div>
                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-check"></i>Создать заказ
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Лоадер -->
<div id="loader" class="loader" style="display: none;">
    <div class="loader-content">
        <div class="spinner"></div>
        <p>Загрузка...</p>
    </div>
</div>

<style>
/* Стили для модальных окон */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.modal-content {
    position: relative;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.9), rgba(20, 20, 20, 0.9));
    border: 1px solid rgba(0, 212, 255, 0.3);
    border-radius: 15px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 212, 255, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid rgba(0, 212, 255, 0.2);
}

.modal-header h2 {
    margin: 0;
    color: #00d4ff;
    font-size: 1.5rem;
}

.modal-close {
    background: none;
    border: none;
    color: #ffffff;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #00d4ff;
}

.modal-body {
    padding: 20px;
}

.auth-form, .order-form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    color: #ffffff;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 12px;
    border: 1px solid rgba(0, 212, 255, 0.3);
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.5);
    color: #ffffff;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #00d4ff;
    box-shadow: 0 0 10px rgba(0, 212, 255, 0.3);
}

.form-group input.error {
    border-color: #ff4757;
    box-shadow: 0 0 10px rgba(255, 71, 87, 0.3);
}

.error-message {
    color: #ff4757;
    font-size: 12px;
    margin-top: 5px;
}

.btn-full {
    width: 100%;
    margin-top: 10px;
}

.auth-links {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(0, 212, 255, 0.2);
}

.auth-links p {
    color: #cccccc;
    margin: 0;
}

.auth-links a {
    color: #00d4ff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.auth-links a:hover {
    color: #ffffff;
}

/* Лоадер */
.loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    z-index: 2000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-content {
    text-align: center;
    color: #ffffff;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 3px solid rgba(0, 212, 255, 0.3);
    border-top: 3px solid #00d4ff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Адаптивность */
@media (max-width: 768px) {
    .modal-content {
        width: 95%;
        margin: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .modal-header h2 {
        font-size: 1.2rem;
    }
}
</style>