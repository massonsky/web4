- Принципы применения:
-- - KISS: Минимальные изменения — только перестановка деклараций.
-- - DRY: INSERT IGNORE + SELECT для справочников избегает дублирования логики.
-- - YAGNI: Нет лишних фич (e.g., без geo-валидации города).
-- - SOLID: SRP (процедура только для клиента+авто); OCP (легко добавить поля).
-- - Производительность: Индексы на UNIQUE полях; асимптотика O(1) на lookup (vs O(n) без индексов).
-- =====================================================
CREATE DATABASE IF NOT EXISTS AutoServiceDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE AutoServiceDB;
-- Справочные таблицы (с COMMENT для документации)
CREATE TABLE Cities (
city_id INT AUTO_INCREMENT PRIMARY KEY,
city_name VARCHAR(100) NOT NULL UNIQUE,
INDEX idx_city_name (city_name)
) COMMENT 'Справочник городов';
CREATE TABLE CarTypes (
type_id INT AUTO_INCREMENT PRIMARY KEY,
type_name VARCHAR(50) NOT NULL UNIQUE,
description TEXT,
INDEX idx_type_name (type_name)
) COMMENT 'Справочник типов автомобилей';
CREATE TABLE Brands (
brand_id INT AUTO_INCREMENT PRIMARY KEY,
brand_name VARCHAR(100) NOT NULL UNIQUE,
country VARCHAR(50),
INDEX idx_brand_name (brand_name)
) COMMENT 'Справочник марок автомобилей';
CREATE TABLE Models (
model_id INT AUTO_INCREMENT PRIMARY KEY,
model_name VARCHAR(100) NOT NULL,
brand_id INT NOT NULL,
FOREIGN KEY (brand_id) REFERENCES Brands(brand_id) ON DELETE CASCADE ON UPDATE CASCADE,
UNIQUE KEY uk_model_brand (model_name, brand_id),
INDEX idx_model_brand (brand_id)
) COMMENT 'Справочник моделей (зависит от марки)';
CREATE TABLE ServiceCategories (
category_id INT AUTO_INCREMENT PRIMARY KEY,
category_name VARCHAR(100) NOT NULL UNIQUE,
description TEXT,
INDEX idx_category_name (category_name)
) COMMENT 'Справочник категорий услуг';
CREATE TABLE OrderStatuses (
status_id INT AUTO_INCREMENT PRIMARY KEY,
status_name ENUM('new', 'in_progress', 'completed', 'cancelled') NOT NULL UNIQUE,
description TEXT
) COMMENT 'Справочник статусов заказов';
CREATE TABLE EmployeeRoles (
role_id INT AUTO_INCREMENT PRIMARY KEY,
role_name VARCHAR(50) NOT NULL UNIQUE,
description TEXT,
INDEX idx_role_name (role_name)
) COMMENT 'Справочник ролей сотрудников';
-- Основные таблицы
CREATE TABLE Clients (
client_id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
phone VARCHAR(20) UNIQUE,
email VARCHAR(100) UNIQUE,
password VARCHAR(255) NOT NULL,
city_id INT,
address TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (city_id) REFERENCES Cities(city_id) ON DELETE SET NULL ON UPDATE CASCADE,
INDEX idx_client_name (last_name, first_name),
INDEX idx_client_phone (phone),
INDEX idx_client_email (email)
) COMMENT 'Клиенты (владельцы авто)';
CREATE TABLE Cars (
car_id INT AUTO_INCREMENT PRIMARY KEY,
vin VARCHAR(17) UNIQUE NOT NULL,
license_plate VARCHAR(20) UNIQUE NOT NULL,
year INT NOT NULL CHECK (year > 1900 AND year <= 2030),
mileage INT DEFAULT 0 CHECK (mileage >= 0),
client_id INT NOT NULL,
brand_id INT NOT NULL,
model_id INT NOT NULL,
type_id INT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (client_id) REFERENCES Clients(client_id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (brand_id) REFERENCES Brands(brand_id) ON DELETE RESTRICT ON UPDATE CASCADE,
FOREIGN KEY (model_id) REFERENCES Models(model_id) ON DELETE RESTRICT ON UPDATE CASCADE,
FOREIGN KEY (type_id) REFERENCES CarTypes(type_id) ON DELETE RESTRICT ON UPDATE CASCADE,
INDEX idx_car_client (client_id),
INDEX idx_car_vin (vin),
INDEX idx_car_plate (license_plate)
) COMMENT 'Автомобили клиентов (каскадное удаление с клиентом)';
CREATE TABLE Employees (
employee_id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
phone VARCHAR(20) UNIQUE,
role_id INT NOT NULL,
salary DECIMAL(10,2) DEFAULT 0.00 CHECK (salary >= 0),
hire_date DATE NOT NULL DEFAULT CURDATE(),
password VARCHAR(255),
FOREIGN KEY (role_id) REFERENCES EmployeeRoles(role_id) ON DELETE RESTRICT ON UPDATE CASCADE,
INDEX idx_employee_name (last_name, first_name),
INDEX idx_employee_phone (phone)
) COMMENT 'Сотрудники мастерской';
CREATE TABLE Services (
service_id INT AUTO_INCREMENT PRIMARY KEY,
service_name VARCHAR(200) NOT NULL,
category_id INT NOT NULL,
base_price DECIMAL(10,2) NOT NULL CHECK (base_price >= 0),
duration_minutes INT DEFAULT 60 CHECK (duration_minutes > 0),
description TEXT,
FOREIGN KEY (category_id) REFERENCES ServiceCategories(category_id) ON DELETE RESTRICT ON UPDATE CASCADE,
UNIQUE KEY uk_service_category (service_name, category_id),
INDEX idx_service_category (category_id)
) COMMENT 'Справочник услуг (цены динамичны, но базовые здесь)';
CREATE TABLE Parts (
part_id INT AUTO_INCREMENT PRIMARY KEY,
part_name VARCHAR(200) NOT NULL,
part_number VARCHAR(50) UNIQUE NOT NULL,
price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
stock_quantity INT DEFAULT 0 CHECK (stock_quantity >= 0),
supplier VARCHAR(100),
INDEX idx_part_number (part_number),
INDEX idx_part_name (part_name)
) COMMENT 'Справочник запчастей (инвентарь)';
CREATE TABLE Orders (
order_id INT AUTO_INCREMENT PRIMARY KEY,
car_id INT NOT NULL,
employee_id INT NOT NULL,
order_date DATE NOT NULL DEFAULT CURDATE(),
estimated_completion DATE,
actual_completion DATE,
status_id INT NOT NULL DEFAULT 1,
total_cost DECIMAL(10,2) DEFAULT 0.00,
notes TEXT,
FOREIGN KEY (car_id) REFERENCES Cars(car_id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (employee_id) REFERENCES Employees(employee_id) ON DELETE RESTRICT ON UPDATE CASCADE,
FOREIGN KEY (status_id) REFERENCES OrderStatuses(status_id) ON DELETE RESTRICT ON UPDATE CASCADE,
INDEX idx_order_car (car_id),
INDEX idx_order_employee (employee_id),
INDEX idx_order_date (order_date),
INDEX idx_order_status (status_id)
) COMMENT 'Заказы на ремонт (каскад с авто; total_cost обновляется триггерами)';
CREATE TABLE OrderServices (
order_service_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
service_id INT NOT NULL,
quantity INT DEFAULT 1 CHECK (quantity > 0),
unit_price DECIMAL(10,2) NOT NULL,
total DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (service_id) REFERENCES Services(service_id) ON DELETE RESTRICT ON UPDATE CASCADE,
INDEX idx_os_order (order_id),
INDEX idx_os_service (service_id)
) COMMENT 'Услуги в заказе (каскад с заказом)';
CREATE TABLE OrderParts (
order_part_id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
part_id INT NOT NULL,
quantity INT DEFAULT 1 CHECK (quantity > 0),
unit_price DECIMAL(10,2) NOT NULL,
total DECIMAL(10,2) GENERATED ALWAYS AS (quantity * unit_price) STORED,
FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE ON UPDATE CASCADE,
FOREIGN KEY (part_id) REFERENCES Parts(part_id) ON DELETE RESTRICT ON UPDATE CASCADE,
INDEX idx_op_order (order_id),
INDEX idx_op_part (part_id)
) COMMENT 'Запчасти в заказе (каскад с заказом; обновляйте stock_quantity триггером)';
-- Триггеры для целостности (автоматическое обновление stock и total_cost)
DELIMITER //
CREATE TRIGGER after_order_part_insert
AFTER INSERT ON OrderParts
FOR EACH ROW
BEGIN
UPDATE Parts SET stock_quantity = stock_quantity - NEW.quantity WHERE part_id = NEW.part_id;
UPDATE Orders SET total_cost = CalculateOrderTotal(NEW.order_id) WHERE order_id = NEW.order_id;
END //
CREATE TRIGGER after_order_part_delete
AFTER DELETE ON OrderParts
FOR EACH ROW
BEGIN
UPDATE Parts SET stock_quantity = stock_quantity + OLD.quantity WHERE part_id = OLD.part_id;
UPDATE Orders SET total_cost = CalculateOrderTotal(OLD.order_id) WHERE order_id = OLD.order_id;
END //
CREATE TRIGGER after_order_service_insert
AFTER INSERT ON OrderServices
FOR EACH ROW
BEGIN
UPDATE Orders SET total_cost = CalculateOrderTotal(NEW.order_id) WHERE order_id = NEW.order_id;
END //
CREATE TRIGGER after_order_service_delete
AFTER DELETE ON OrderServices
FOR EACH ROW
BEGIN
UPDATE Orders SET total_cost = CalculateOrderTotal(OLD.order_id) WHERE order_id = OLD.order_id;
END //
DELIMITER ;
-- Представления (документация в комментариях)
-- Представление: Клиенты с их автомобилями
CREATE VIEW ClientCarsView AS
SELECT
c.client_id,
CONCAT(c.first_name, ' ', c.last_name) AS full_name,
c.phone,
c.email,
ct.city_name,
car.vin,
car.license_plate,
car.year,
CONCAT(b.brand_name, ' ', m.model_name) AS car_model,
ct2.type_name AS car_type
FROM Clients c
LEFT JOIN Cities ct ON c.city_id = ct.city_id
LEFT JOIN Cars car ON c.client_id = car.client_id
LEFT JOIN Brands b ON car.brand_id = b.brand_id
LEFT JOIN Models m ON car.model_id = m.model_id
LEFT JOIN CarTypes ct2 ON car.type_id = ct2.type_id
ORDER BY c.last_name, c.first_name;
-- Представление: История ремонтов по клиентам
CREATE VIEW ClientOrderHistoryView AS
SELECT
c.client_id,
CONCAT(c.first_name, ' ', c.last_name) AS client_name,
car.license_plate,
o.order_id,
o.order_date,
os.status_name,
o.total_cost,
GROUP_CONCAT(DISTINCT s.service_name SEPARATOR ', ') AS services,
SUM(op.quantity * op.unit_price) AS parts_cost
FROM Clients c
JOIN Cars car ON c.client_id = car.client_id
JOIN Orders o ON car.car_id = o.car_id
JOIN OrderStatuses os ON o.status_id = os.status_id
LEFT JOIN OrderServices osrv ON o.order_id = osrv.order_id
LEFT JOIN Services s ON osrv.service_id = s.service_id
LEFT JOIN OrderParts op ON o.order_id = op.order_id
GROUP BY c.client_id, car.license_plate, o.order_id, o.order_date, os.status_name, o.total_cost
ORDER BY c.client_id, o.order_date DESC;
-- Представление: Активные заказы
CREATE VIEW CurrentOrdersView AS
SELECT
o.order_id,
car.license_plate,
CONCAT(c.first_name, ' ', c.last_name) AS client_name,
CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
er.role_name,
o.order_date,
o.estimated_completion,
o.total_cost
FROM Orders o
JOIN Cars car ON o.car_id = car.car_id
JOIN Clients c ON car.client_id = c.client_id
JOIN Employees e ON o.employee_id = e.employee_id
JOIN EmployeeRoles er ON e.role_id = er.role_id
JOIN OrderStatuses os ON o.status_id = os.status_id
WHERE os.status_name = 'in_progress'
ORDER BY o.order_date;
-- Представление: Доходы по категориям услуг (monthly)
CREATE VIEW MonthlyIncomeByCategoryView AS
SELECT
sc.category_name,
MONTH(o.order_date) AS month,
YEAR(o.order_date) AS year,
SUM(o.total_cost) AS total_income,
COUNT(o.order_id) AS order_count
FROM Orders o
JOIN OrderServices osrv ON o.order_id = osrv.order_id
JOIN Services s ON osrv.service_id = s.service_id
JOIN ServiceCategories sc ON s.category_id = sc.category_id
GROUP BY sc.category_name, MONTH(o.order_date), YEAR(o.order_date)
ORDER BY year DESC, month DESC, total_income DESC;
-- Функции (с документацией в COMMENT)
DELIMITER //
CREATE FUNCTION CalculateOrderTotal(p_order_id INT)
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
DECLARE v_total DECIMAL(10,2) DEFAULT 0.00;
SELECT
COALESCE(SUM(os.total), 0) + COALESCE(SUM(op.total), 0)
INTO v_total
FROM Orders o
LEFT JOIN OrderServices os ON o.order_id = os.order_id
LEFT JOIN OrderParts op ON o.order_id = op.order_id
WHERE o.order_id = p_order_id;
RETURN v_total;
END //
DELIMITER ;
DELIMITER //
CREATE FUNCTION GetOrderStatusName(p_order_id INT)
RETURNS VARCHAR(20)
READS SQL DATA
DETERMINISTIC
BEGIN
DECLARE v_status VARCHAR(20);
SELECT os.status_name INTO v_status
FROM Orders o
JOIN OrderStatuses os ON o.status_id = os.status_id
WHERE o.order_id = p_order_id
LIMIT 1;
RETURN COALESCE(v_status, 'unknown');
END //
DELIMITER ;
-- Хранимые процедуры (исправленная CreateClientAndCar с правильным порядком деклараций)
DELIMITER //
CREATE PROCEDURE CreateClientAndCar(
IN p_first_name VARCHAR(50),
IN p_last_name VARCHAR(50),
IN p_phone VARCHAR(20),
IN p_city_name VARCHAR(100),
IN p_vin VARCHAR(17),
IN p_license_plate VARCHAR(20),
IN p_year INT,
IN p_brand_name VARCHAR(100),
IN p_model_name VARCHAR(100),
IN p_type_name VARCHAR(50),
OUT p_client_id INT,
OUT p_car_id INT
)
BEGIN
-- Декларации переменных (перед handler'ами по правилам MySQL)
DECLARE new_city_id INT DEFAULT NULL;
DECLARE new_brand_id INT DEFAULT NULL;
DECLARE new_model_id INT DEFAULT NULL;
DECLARE new_type_id INT DEFAULT NULL;
-- Обработчик ошибок (после переменных)
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
ROLLBACK;
RESIGNAL;
END;
START TRANSACTION;
-- Вставка/получение города (IGNORE для DRY: избегает проверки EXISTS)
INSERT IGNORE INTO Cities (city_name) VALUES (p_city_name);
SELECT city_id INTO new_city_id FROM Cities WHERE city_name = p_city_name;
-- Вставка/получение типа авто
INSERT IGNORE INTO CarTypes (type_name) VALUES (p_type_name);
SELECT type_id INTO new_type_id FROM CarTypes WHERE type_name = p_type_name;
-- Вставка/получение бренда
INSERT IGNORE INTO Brands (brand_name) VALUES (p_brand_name);
SELECT brand_id INTO new_brand_id FROM Brands WHERE brand_name = p_brand_name;
-- Вставка/получение модели (зависит от бренда)
INSERT IGNORE INTO Models (model_name, brand_id) VALUES (p_model_name, new_brand_id);
SELECT model_id INTO new_model_id FROM Models WHERE model_name = p_model_name AND brand_id = new_brand_id;
-- Создать клиента
INSERT INTO Clients (first_name, last_name, phone, city_id)
VALUES (p_first_name, p_last_name, p_phone, new_city_id);
SET p_client_id = LAST_INSERT_ID();
-- Создать авто (каскадно с клиентом)
INSERT INTO Cars (vin, license_plate, year, client_id, brand_id, model_id, type_id)
VALUES (p_vin, p_license_plate, p_year, p_client_id, new_brand_id, new_model_id, new_type_id);
SET p_car_id = LAST_INSERT_ID();
COMMIT;
END //
DELIMITER ;
DELIMITER //
CREATE PROCEDURE CreateOrderWithDetails(
IN p_car_id INT,
IN p_employee_id INT,
IN p_service_ids TEXT,
IN p_part_ids TEXT,
IN p_quantities TEXT,
OUT p_order_id INT
)
BEGIN
DECLARE i INT DEFAULT 0;
DECLARE service_id INT;
DECLARE part_id INT;
DECLARE qty INT DEFAULT 1;
DECLARE EXIT HANDLER FOR SQLEXCEPTION
BEGIN
ROLLBACK;
RESIGNAL;
END;
START TRANSACTION;
INSERT INTO Orders (car_id, employee_id, status_id, total_cost)
VALUES (p_car_id, p_employee_id, 1, 0.00);
SET p_order_id = LAST_INSERT_ID();
WHILE i < CHAR_LENGTH(p_service_ids) - CHAR_LENGTH(REPLACE(p_service_ids, ',', '')) + 1 DO
SET service_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p_service_ids, ',', i+1), ',', -1) AS UNSIGNED);
INSERT INTO OrderServices (order_id, service_id, quantity, unit_price)
SELECT p_order_id, service_id, 1, base_price FROM Services WHERE service_id = service_id;
SET i = i + 1;
END WHILE;
SET i = 0;
WHILE i < CHAR_LENGTH(p_part_ids) - CHAR_LENGTH(REPLACE(p_part_ids, ',', '')) + 1 DO
SET part_id = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p_part_ids, ',', i+1), ',', -1) AS UNSIGNED);
SET qty = CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(p_quantities, ',', i+1), ',', -1) AS UNSIGNED);
INSERT INTO OrderParts (order_id, part_id, quantity, unit_price)
SELECT p_order_id, part_id, qty, price FROM Parts WHERE part_id = part_id;
SET i = i + 1;
END WHILE;
UPDATE Orders SET total_cost = CalculateOrderTotal(p_order_id) WHERE order_id = p_order_id;
COMMIT;
END //
DELIMITER ;
DELIMITER //
CREATE PROCEDURE SafeDeleteOrder(IN p_order_id INT)
BEGIN
DECLARE v_status VARCHAR(20);
SELECT GetOrderStatusName(p_order_id) INTO v_status;
IF v_status IN ('new', 'cancelled') THEN
DELETE FROM Orders WHERE order_id = p_order_id;
ELSE
SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot delete order in progress or completed';
END IF;
END //
DELIMITER ;
-- Тестовые данные (для инициализации справочников)
INSERT IGNORE INTO Cities (city_name) VALUES ('Moscow'), ('Saint Petersburg');
INSERT IGNORE INTO CarTypes (type_name) VALUES ('Sedan'), ('SUV');
INSERT IGNORE INTO Brands (brand_name) VALUES ('Toyota'), ('BMW');
INSERT IGNORE INTO Models (model_name, brand_id) VALUES ('Camry', 1), ('X5', 2);
INSERT IGNORE INTO ServiceCategories (category_name) VALUES ('Maintenance'), ('Repair');
INSERT IGNORE INTO Services (service_name, category_id, base_price) VALUES ('Oil Change', 1, 50.00), ('Brake Repair', 2, 200.00);
INSERT IGNORE INTO Parts (part_name, part_number, price, stock_quantity) VALUES ('Oil Filter', 'OF123', 10.00, 100);
INSERT IGNORE INTO OrderStatuses (status_name) VALUES ('new'), ('in_progress'), ('completed'), ('cancelled');
INSERT IGNORE INTO EmployeeRoles (role_name) VALUES ('Mechanic'), ('Manager');
-- Дополнительные таблицы для личного кабинета

-- Корзина покупок для клиентов
CREATE TABLE ShoppingCart (
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
CREATE TABLE Notifications (
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
CREATE TABLE UserSessions (
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
CREATE TABLE ActivityLogs (
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
CREATE TABLE SystemSettings (
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
CREATE TABLE Reviews (
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

-- Представления для личного кабинета

-- Представление: Статистика для админа
CREATE VIEW AdminDashboardView AS
SELECT
    (SELECT COUNT(*) FROM Clients) AS total_clients,
    (SELECT COUNT(*) FROM Orders WHERE DATE(order_date) = CURDATE()) AS today_orders,
    (SELECT COUNT(*) FROM Orders WHERE status_id = 2) AS active_orders,
    (SELECT SUM(total_cost) FROM Orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE())) AS monthly_revenue,
    (SELECT AVG(rating) FROM Reviews) AS average_rating,
    (SELECT COUNT(*) FROM Parts WHERE stock_quantity < 10) AS low_stock_parts;

-- Представление: Корзина клиента с деталями
CREATE VIEW ClientCartView AS
SELECT
    sc.cart_id,
    sc.client_id,
    s.service_id,
    s.service_name,
    sc.quantity,
    s.base_price,
    (sc.quantity * s.base_price) AS total_price,
    cat.category_name,
    sc.added_at
FROM ShoppingCart sc
JOIN Services s ON sc.service_id = s.service_id
JOIN ServiceCategories cat ON s.category_id = cat.category_id
ORDER BY sc.added_at DESC;

-- Представление: Уведомления пользователей
CREATE VIEW UserNotificationsView AS
SELECT
    n.notification_id,
    n.user_id,
    n.user_type,
    n.title,
    n.message,
    n.is_read,
    n.created_at,
    CASE 
        WHEN n.user_type = 'client' THEN CONCAT(c.first_name, ' ', c.last_name)
        WHEN n.user_type = 'employee' THEN CONCAT(e.first_name, ' ', e.last_name)
    END AS user_name
FROM Notifications n
LEFT JOIN Clients c ON n.user_id = c.client_id AND n.user_type = 'client'
LEFT JOIN Employees e ON n.user_id = e.employee_id AND n.user_type = 'employee'
ORDER BY n.created_at DESC;

-- Функции для личного кабинета
DELIMITER //
CREATE FUNCTION GetUserRole(p_user_id INT, p_user_type VARCHAR(10))
RETURNS VARCHAR(50)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_role VARCHAR(50) DEFAULT 'client';
    
    IF p_user_type = 'employee' THEN
        SELECT er.role_name INTO v_role
        FROM Employees e
        JOIN EmployeeRoles er ON e.role_id = er.role_id
        WHERE e.employee_id = p_user_id;
    END IF;
    
    RETURN COALESCE(v_role, 'client');
END //
DELIMITER ;

-- Процедуры для личного кабинета
DELIMITER //
CREATE PROCEDURE AddToCart(
    IN p_client_id INT,
    IN p_service_id INT,
    IN p_quantity INT
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    INSERT INTO ShoppingCart (client_id, service_id, quantity)
    VALUES (p_client_id, p_service_id, p_quantity)
    ON DUPLICATE KEY UPDATE 
        quantity = quantity + p_quantity,
        added_at = CURRENT_TIMESTAMP;
    
    COMMIT;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE CreateNotification(
    IN p_user_id INT,
    IN p_user_type VARCHAR(10),
    IN p_title VARCHAR(200),
    IN p_message TEXT
)
BEGIN
    INSERT INTO Notifications (user_id, user_type, title, message)
    VALUES (p_user_id, p_user_type, p_title, p_message);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE LogUserActivity(
    IN p_user_id INT,
    IN p_user_type VARCHAR(10),
    IN p_action VARCHAR(100),
    IN p_description TEXT,
    IN p_ip_address VARCHAR(45)
)
BEGIN
    INSERT INTO ActivityLogs (user_id, user_type, action, description, ip_address)
    VALUES (p_user_id, p_user_type, p_action, p_description, p_ip_address);
END //
DELIMITER ;

-- Инициализация настроек системы
INSERT IGNORE INTO SystemSettings (setting_key, setting_value, description) VALUES
('site_name', 'AutoService Pro', 'Название сайта'),
('maintenance_mode', 'false', 'Режим обслуживания'),
('max_cart_items', '10', 'Максимальное количество услуг в корзине'),
('notification_email', 'admin@autoservice.com', 'Email для уведомлений'),
('working_hours', '9:00-18:00', 'Рабочие часы');

-- Пример использования (тестирование процедуры)
CALL CreateClientAndCar('John', 'Doe', '+123456789', 'Moscow', '1HGCM82633A004352', 'ABC123', 2020, 'Toyota', 'Camry', 'Sedan', @client_id, @car_id);
SELECT @client_id, @car_id;  -- Ожидаемо: Новые ID клиента и авто
SELECT * FROM ClientCarsView LIMIT 5;  -- Проверка представления