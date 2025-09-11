# Cargo Transport System - Project 3

## Описание
Система управления грузоперевозками с функциональностью создания заказов, расчета стоимости и отправки email уведомлений.

## Установка и настройка

### 1. Установка PHPMailer

#### Способ 1: Через Composer (рекомендуется)
```bash
composer install
```

#### Способ 2: Ручная установка
1. Скачайте PHPMailer с [GitHub](https://github.com/PHPMailer/PHPMailer)
2. Распакуйте в папку `backend/vendor/phpmailer/`
3. Убедитесь, что файлы находятся по путям:
   - `backend/vendor/phpmailer/Exception.php`
   - `backend/vendor/phpmailer/PHPMailer.php`
   - `backend/vendor/phpmailer/SMTP.php`

### 2. Настройка Email

1. Откройте файл `.env` в корне проекта
2. Настройте параметры email:

```env
# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="Cargo Transport System"

# Admin Email
ADMIN_EMAIL=admin@cargo-transport.com
```

### 3. Настройка Gmail (если используете Gmail)

1. Включите двухфакторную аутентификацию в вашем Google аккаунте
2. Создайте пароль приложения:
   - Перейдите в [Настройки Google аккаунта](https://myaccount.google.com/)
   - Безопасность → Пароли приложений
   - Создайте новый пароль для приложения
   - Используйте этот пароль в `MAIL_PASSWORD`

### 4. Настройка других SMTP провайдеров

#### Yandex Mail
```env
MAIL_HOST=smtp.yandex.ru
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

#### Mail.ru
```env
MAIL_HOST=smtp.mail.ru
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

#### Outlook/Hotmail
```env
MAIL_HOST=smtp-mail.outlook.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

## Структура проекта

```
project3/
├── .env                    # Конфигурация окружения
├── composer.json           # Зависимости PHP
├── index.html             # Главная страница
├── assets/
│   ├── style.css          # Стили
│   └── img/               # Изображения
├── backend/
│   ├── config.php         # Загрузка конфигурации
│   ├── EmailService.php   # Сервис отправки email
│   ├── order.php          # Создание заказа
│   ├── bill_1.php         # Расчет стоимости
│   ├── bill_2.php         # Подтверждение заказа
│   ├── basket.php         # Просмотр заказов
│   └── logs/              # Логи системы
└── src/                   # Дополнительные ресурсы
```

## Функциональность

### Email уведомления
- Автоматическая отправка уведомлений при создании заказа
- HTML и текстовые версии писем
- Логирование отправки email
- Поддержка различных SMTP провайдеров

### Система заказов
- Создание заказов на перевозку
- Расчет стоимости с учетом дополнительных услуг
- Сохранение заказов в файлы
- Просмотр истории заказов

## Тестирование Email

Для тестирования email функциональности:

1. Убедитесь, что настроены правильные SMTP параметры в `.env`
2. Создайте тестовый заказ через веб-интерфейс
3. Проверьте логи в `backend/logs/email_log.txt`
4. Проверьте почтовый ящик администратора

## Безопасность

- Файл `.env` содержит конфиденциальную информацию и не должен попадать в систему контроля версий
- Используйте пароли приложений вместо основных паролей
- Регулярно обновляйте зависимости

## Поддержка

При возникновении проблем:
1. Проверьте логи в `backend/logs/`
2. Убедитесь в правильности настроек SMTP
3. Проверьте, что PHPMailer установлен корректно