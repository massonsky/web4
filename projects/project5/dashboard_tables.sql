-- Дополнительные таблицы для личного кабинета

-- Корзина покупок для клиентов
CREATE TABLE IF NOT EXISTS ShoppingCart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    service_id INT NOT NULL,
    quantity INT DEFAULT 1 CHECK (quantity > 0),
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES Clients(client_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (service_id) REFERENCES Services(service_id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uk_cart_client_service (client_id, service_id),
    INDEX idx_cart_client (client_id)
) COMMENT 'Корзина услуг для клиентов';

-- Уведомления для пользователей
CREATE TABLE IF NOT EXISTS Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('client', 'employee') NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notification_user (user_id, user_type),
    INDEX idx_notification_read (is_read)
) COMMENT 'Уведомления для клиентов и сотрудников';

-- Сессии пользователей для "Запомнить меня"
CREATE TABLE IF NOT EXISTS UserSessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('client', 'employee') NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_token (token),
    INDEX idx_session_user (user_id, user_type)
) COMMENT 'Сессии пользователей для автоматического входа';

-- Логи действий для аудита (для админа)
CREATE TABLE IF NOT EXISTS ActivityLogs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    user_type ENUM('client', 'employee') NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_user (user_id, user_type),
    INDEX idx_log_date (created_at),
    INDEX idx_log_action (action)
) COMMENT 'Логи действий пользователей для аудита';

-- Настройки системы (для админа)
CREATE TABLE IF NOT EXISTS SystemSettings (
    setting_id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES Employees(employee_id) ON DELETE SET NULL,
    INDEX idx_setting_key (setting_key)
) COMMENT 'Настройки системы';

-- Отзывы клиентов
CREATE TABLE IF NOT EXISTS Reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    client_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES Clients(client_id) ON DELETE CASCADE,
    UNIQUE KEY uk_review_order (order_id),
    INDEX idx_review_client (client_id),
    INDEX idx_review_rating (rating)
) COMMENT 'Отзывы клиентов о выполненных заказах';

-- Инициализация настроек системы
INSERT IGNORE INTO SystemSettings (setting_key, setting_value, description) VALUES
('site_name', 'AutoService Pro', 'Название сайта'),
('maintenance_mode', 'false', 'Режим обслуживания'),
('max_cart_items', '10', 'Максимальное количество услуг в корзине'),
('notification_email', 'admin@autoservice.com', 'Email для уведомлений'),
('working_hours', '9:00-18:00', 'Рабочие часы');