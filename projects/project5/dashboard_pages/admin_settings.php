<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo '<div class="alert alert-danger">Доступ запрещен</div>';
    exit;
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4">Системные настройки</h2>
        </div>
    </div>

    <!-- Общие настройки -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Общие настройки</h5>
                </div>
                <div class="card-body">
                    <form id="general-settings-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Название сайта:</label>
                                    <input type="text" class="form-control" id="site-name" name="site_name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email администратора:</label>
                                    <input type="email" class="form-control" id="admin-email" name="admin_email">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Телефон поддержки:</label>
                                    <input type="tel" class="form-control" id="support-phone" name="support_phone">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Часы работы:</label>
                                    <input type="text" class="form-control" id="working-hours" name="working_hours" placeholder="Пн-Пт: 9:00-18:00">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Адрес:</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveGeneralSettings()">Сохранить общие настройки</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Настройки заказов -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Настройки заказов</h5>
                </div>
                <div class="card-body">
                    <form id="order-settings-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Минимальная сумма заказа (₽):</label>
                                    <input type="number" class="form-control" id="min-order-amount" name="min_order_amount" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Стоимость доставки (₽):</label>
                                    <input type="number" class="form-control" id="delivery-cost" name="delivery_cost" step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Бесплатная доставка от (₽):</label>
                                    <input type="number" class="form-control" id="free-delivery-from" name="free_delivery_from" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Время обработки заказа (часы):</label>
                                    <input type="number" class="form-control" id="order-processing-time" name="order_processing_time">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auto-confirm-orders" name="auto_confirm_orders">
                                <label class="form-check-label" for="auto-confirm-orders">
                                    Автоматически подтверждать заказы
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveOrderSettings()">Сохранить настройки заказов</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Настройки уведомлений -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Настройки уведомлений</h5>
                </div>
                <div class="card-body">
                    <form id="notification-settings-form">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="email-notifications" name="email_notifications">
                                <label class="form-check-label" for="email-notifications">
                                    Отправлять email уведомления
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="sms-notifications" name="sms_notifications">
                                <label class="form-check-label" for="sms-notifications">
                                    Отправлять SMS уведомления
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify-new-orders" name="notify_new_orders">
                                <label class="form-check-label" for="notify-new-orders">
                                    Уведомлять о новых заказах
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notify-order-status" name="notify_order_status">
                                <label class="form-check-label" for="notify-order-status">
                                    Уведомлять об изменении статуса заказа
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveNotificationSettings()">Сохранить настройки уведомлений</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Настройки безопасности -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Настройки безопасности</h5>
                </div>
                <div class="card-body">
                    <form id="security-settings-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Время сессии (минуты):</label>
                                    <input type="number" class="form-control" id="session-timeout" name="session_timeout">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Максимум попыток входа:</label>
                                    <input type="number" class="form-control" id="max-login-attempts" name="max_login_attempts">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="require-strong-passwords" name="require_strong_passwords">
                                <label class="form-check-label" for="require-strong-passwords">
                                    Требовать сложные пароли
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enable-two-factor" name="enable_two_factor">
                                <label class="form-check-label" for="enable-two-factor">
                                    Включить двухфакторную аутентификацию
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveSecuritySettings()">Сохранить настройки безопасности</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Системная информация -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Системная информация</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Версия PHP:</strong> <?php echo phpversion(); ?></p>
                            <p><strong>Версия MySQL:</strong> <span id="mysql-version">Загрузка...</span></p>
                            <p><strong>Размер базы данных:</strong> <span id="db-size">Загрузка...</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Свободное место на диске:</strong> <span id="disk-space">Загрузка...</span></p>
                            <p><strong>Время работы сервера:</strong> <span id="server-uptime">Загрузка...</span></p>
                            <p><strong>Последнее обновление:</strong> <span id="last-update">Загрузка...</span></p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-info" onclick="loadSystemInfo()">Обновить информацию</button>
                        <button class="btn btn-warning" onclick="clearCache()">Очистить кэш</button>
                        <button class="btn btn-success" onclick="backupDatabase()">Создать резервную копию БД</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    loadAllSettings();
    loadSystemInfo();
});

function loadAllSettings() {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_system_settings' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                populateSettings(response.data);
            } else {
                showAlert('Ошибка загрузки настроек: ' + response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки настроек', 'danger');
        }
    });
}

function populateSettings(settings) {
    // Общие настройки
    $('#site-name').val(settings.site_name || '');
    $('#admin-email').val(settings.admin_email || '');
    $('#support-phone').val(settings.support_phone || '');
    $('#working-hours').val(settings.working_hours || '');
    $('#address').val(settings.address || '');
    
    // Настройки заказов
    $('#min-order-amount').val(settings.min_order_amount || '');
    $('#delivery-cost').val(settings.delivery_cost || '');
    $('#free-delivery-from').val(settings.free_delivery_from || '');
    $('#order-processing-time').val(settings.order_processing_time || '');
    $('#auto-confirm-orders').prop('checked', settings.auto_confirm_orders == '1');
    
    // Настройки уведомлений
    $('#email-notifications').prop('checked', settings.email_notifications == '1');
    $('#sms-notifications').prop('checked', settings.sms_notifications == '1');
    $('#notify-new-orders').prop('checked', settings.notify_new_orders == '1');
    $('#notify-order-status').prop('checked', settings.notify_order_status == '1');
    
    // Настройки безопасности
    $('#session-timeout').val(settings.session_timeout || '');
    $('#max-login-attempts').val(settings.max_login_attempts || '');
    $('#require-strong-passwords').prop('checked', settings.require_strong_passwords == '1');
    $('#enable-two-factor').prop('checked', settings.enable_two_factor == '1');
}

function saveGeneralSettings() {
    const data = {
        action: 'update_system_settings',
        category: 'general',
        settings: {
            site_name: $('#site-name').val(),
            admin_email: $('#admin-email').val(),
            support_phone: $('#support-phone').val(),
            working_hours: $('#working-hours').val(),
            address: $('#address').val()
        }
    };
    
    saveSettings(data, 'Общие настройки сохранены');
}

function saveOrderSettings() {
    const data = {
        action: 'update_system_settings',
        category: 'orders',
        settings: {
            min_order_amount: $('#min-order-amount').val(),
            delivery_cost: $('#delivery-cost').val(),
            free_delivery_from: $('#free-delivery-from').val(),
            order_processing_time: $('#order-processing-time').val(),
            auto_confirm_orders: $('#auto-confirm-orders').is(':checked') ? '1' : '0'
        }
    };
    
    saveSettings(data, 'Настройки заказов сохранены');
}

function saveNotificationSettings() {
    const data = {
        action: 'update_system_settings',
        category: 'notifications',
        settings: {
            email_notifications: $('#email-notifications').is(':checked') ? '1' : '0',
            sms_notifications: $('#sms-notifications').is(':checked') ? '1' : '0',
            notify_new_orders: $('#notify-new-orders').is(':checked') ? '1' : '0',
            notify_order_status: $('#notify-order-status').is(':checked') ? '1' : '0'
        }
    };
    
    saveSettings(data, 'Настройки уведомлений сохранены');
}

function saveSecuritySettings() {
    const data = {
        action: 'update_system_settings',
        category: 'security',
        settings: {
            session_timeout: $('#session-timeout').val(),
            max_login_attempts: $('#max-login-attempts').val(),
            require_strong_passwords: $('#require-strong-passwords').is(':checked') ? '1' : '0',
            enable_two_factor: $('#enable-two-factor').is(':checked') ? '1' : '0'
        }
    };
    
    saveSettings(data, 'Настройки безопасности сохранены');
}

function saveSettings(data, successMessage) {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: data,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert(successMessage, 'success');
            } else {
                showAlert('Ошибка сохранения: ' + response.error, 'danger');
            }
        },
        error: function() {
            showAlert('Ошибка сохранения настроек', 'danger');
        }
    });
}

function loadSystemInfo() {
    $.ajax({
        url: 'backend/dashboard_api.php',
        method: 'POST',
        data: { action: 'get_system_info' },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const info = response.data;
                $('#mysql-version').text(info.mysql_version || 'Неизвестно');
                $('#db-size').text(info.db_size || 'Неизвестно');
                $('#disk-space').text(info.disk_space || 'Неизвестно');
                $('#server-uptime').text(info.server_uptime || 'Неизвестно');
                $('#last-update').text(info.last_update || 'Неизвестно');
            }
        },
        error: function() {
            showAlert('Ошибка загрузки системной информации', 'danger');
        }
    });
}

function clearCache() {
    if (confirm('Вы уверены, что хотите очистить кэш?')) {
        $.ajax({
            url: 'backend/dashboard_api.php',
            method: 'POST',
            data: { action: 'clear_cache' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('Кэш успешно очищен', 'success');
                } else {
                    showAlert('Ошибка очистки кэша: ' + response.error, 'danger');
                }
            },
            error: function() {
                showAlert('Ошибка очистки кэша', 'danger');
            }
        });
    }
}

function backupDatabase() {
    if (confirm('Создать резервную копию базы данных? Это может занять некоторое время.')) {
        $.ajax({
            url: 'backend/dashboard_api.php',
            method: 'POST',
            data: { action: 'backup_database' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showAlert('Резервная копия успешно создана', 'success');
                } else {
                    showAlert('Ошибка создания резервной копии: ' + response.error, 'danger');
                }
            },
            error: function() {
                showAlert('Ошибка создания резервной копии', 'danger');
            }
        });
    }
}
</script>