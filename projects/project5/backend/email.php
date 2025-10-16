<?php
require_once 'config.php';

class EmailManager {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        $this->smtp_host = SMTP_HOST;
        $this->smtp_port = SMTP_PORT;
        $this->smtp_username = SMTP_USERNAME;
        $this->smtp_password = SMTP_PASSWORD;
        $this->from_email = FROM_EMAIL;
        $this->from_name = FROM_NAME;
    }
    
    /**
     * Отправка email с использованием PHPMailer или встроенной функции mail()
     */
    public function sendEmail($to, $subject, $body, $isHTML = true) {
        try {
            // Если доступен PHPMailer, используем его
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                return $this->sendWithPHPMailer($to, $subject, $body, $isHTML);
            } else {
                // Используем встроенную функцию mail()
                return $this->sendWithBuiltInMail($to, $subject, $body, $isHTML);
            }
        } catch (Exception $e) {
            logError("Email sending failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Отправка с использованием PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $body, $isHTML) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Настройки SMTP
            $mail->isSMTP();
            $mail->Host = $this->smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtp_port;
            $mail->CharSet = 'UTF-8';
            
            // Отправитель и получатель
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            
            // Содержимое
            $mail->isHTML($isHTML);
            $mail->Subject = $subject;
            $mail->Body = $body;
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            logError("PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Отправка с использованием встроенной функции mail()
     */
    private function sendWithBuiltInMail($to, $subject, $body, $isHTML) {
        $headers = "From: {$this->from_name} <{$this->from_email}>\r\n";
        $headers .= "Reply-To: {$this->from_email}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        
        if ($isHTML) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        return mail($to, $subject, $body, $headers);
    }
    
    /**
     * Отправка уведомления о новом заказе
     */
    public function sendOrderNotification($order_data, $client_email) {
        $subject = "Подтверждение заказа №{$order_data['order_id']} - AutoService IT";
        
        $body = $this->getOrderEmailTemplate($order_data);
        
        return $this->sendEmail($client_email, $subject, $body, true);
    }
    
    /**
     * Отправка уведомления об изменении статуса заказа
     */
    public function sendStatusUpdateNotification($order_id, $new_status, $client_email, $client_name) {
        $subject = "Изменение статуса заказа №{$order_id} - AutoService IT";
        
        $body = $this->getStatusUpdateTemplate($order_id, $new_status, $client_name);
        
        return $this->sendEmail($client_email, $subject, $body, true);
    }
    
    /**
     * Отправка приветственного письма при регистрации
     */
    public function sendWelcomeEmail($client_email, $client_name) {
        $subject = "Добро пожаловать в AutoService IT!";
        
        $body = $this->getWelcomeEmailTemplate($client_name);
        
        return $this->sendEmail($client_email, $subject, $body, true);
    }
    
    /**
     * Шаблон письма для подтверждения заказа
     */
    private function getOrderEmailTemplate($order_data) {
        $services_html = '';
        foreach ($order_data['services'] as $service) {
            $services_html .= "<tr>
                <td style='padding: 8px; border-bottom: 1px solid #eee;'>{$service['name']}</td>
                <td style='padding: 8px; border-bottom: 1px solid #eee; text-align: right;'>{$service['price']} ₽</td>
            </tr>";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Подтверждение заказа</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0;'>AutoService IT</h1>
                    <p style='margin: 10px 0 0 0;'>Подтверждение заказа</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #667eea;'>Заказ №{$order_data['order_id']}</h2>
                    
                    <div style='margin: 20px 0;'>
                        <h3>Информация о клиенте:</h3>
                        <p><strong>Имя:</strong> {$order_data['client_name']}</p>
                        <p><strong>Телефон:</strong> {$order_data['client_phone']}</p>
                        <p><strong>Email:</strong> {$order_data['client_email']}</p>
                    </div>
                    
                    <div style='margin: 20px 0;'>
                        <h3>Автомобиль:</h3>
                        <p><strong>Марка:</strong> {$order_data['car_brand']}</p>
                        <p><strong>Модель:</strong> {$order_data['car_model']}</p>
                        <p><strong>Год:</strong> {$order_data['car_year']}</p>
                        <p><strong>Гос. номер:</strong> {$order_data['license_plate']}</p>
                    </div>
                    
                    <div style='margin: 20px 0;'>
                        <h3>Заказанные услуги:</h3>
                        <table style='width: 100%; border-collapse: collapse; margin-top: 10px;'>
                            <thead>
                                <tr style='background: #667eea; color: white;'>
                                    <th style='padding: 10px; text-align: left;'>Услуга</th>
                                    <th style='padding: 10px; text-align: right;'>Цена</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$services_html}
                            </tbody>
                            <tfoot>
                                <tr style='background: #f0f0f0; font-weight: bold;'>
                                    <td style='padding: 10px;'>Итого:</td>
                                    <td style='padding: 10px; text-align: right;'>{$order_data['total_amount']} ₽</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div style='margin: 20px 0;'>
                        <p><strong>Статус заказа:</strong> <span style='color: #28a745;'>{$order_data['status']}</span></p>
                        <p><strong>Дата создания:</strong> {$order_data['created_at']}</p>
                    </div>
                    
                    <div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0;'><strong>Спасибо за ваш заказ!</strong></p>
                        <p style='margin: 5px 0 0 0;'>Мы свяжемся с вами в ближайшее время для уточнения деталей.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Шаблон письма об изменении статуса
     */
    private function getStatusUpdateTemplate($order_id, $new_status, $client_name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Изменение статуса заказа</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0;'>AutoService IT</h1>
                    <p style='margin: 10px 0 0 0;'>Обновление статуса заказа</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #667eea;'>Здравствуйте, {$client_name}!</h2>
                    
                    <p>Статус вашего заказа №{$order_id} изменился:</p>
                    
                    <div style='background: #e9ecef; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;'>
                        <h3 style='margin: 0; color: #28a745;'>{$new_status}</h3>
                    </div>
                    
                    <p>Вы можете отслеживать статус своего заказа в личном кабинете на нашем сайте.</p>
                    
                    <div style='text-align: center; margin-top: 20px;'>
                        <a href='#' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Перейти в личный кабинет</a>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    /**
     * Шаблон приветственного письма
     */
    private function getWelcomeEmailTemplate($client_name) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Добро пожаловать!</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0;'>AutoService IT</h1>
                    <p style='margin: 10px 0 0 0;'>Добро пожаловать!</p>
                </div>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px;'>
                    <h2 style='color: #667eea;'>Здравствуйте, {$client_name}!</h2>
                    
                    <p>Спасибо за регистрацию в AutoService IT - вашем надежном партнере в области автосервиса и IT-услуг!</p>
                    
                    <div style='margin: 20px 0;'>
                        <h3>Наши услуги:</h3>
                        <ul style='list-style-type: none; padding: 0;'>
                            <li style='padding: 5px 0;'>🔧 Автосервис: диагностика, ремонт, ТО</li>
                            <li style='padding: 5px 0;'>💻 IT-услуги: настройка, ремонт компьютеров</li>
                            <li style='padding: 5px 0;'>📱 Мобильные устройства: ремонт телефонов и планшетов</li>
                            <li style='padding: 5px 0;'>🌐 Веб-разработка и дизайн</li>
                        </ul>
                    </div>
                    
                    <p>Теперь вы можете:</p>
                    <ul>
                        <li>Заказывать услуги онлайн</li>
                        <li>Отслеживать статус заказов</li>
                        <li>Просматривать историю обслуживания</li>
                        <li>Получать персональные предложения</li>
                    </ul>
                    
                    <div style='text-align: center; margin-top: 20px;'>
                        <a href='#' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Перейти на сайт</a>
                    </div>
                    
                    <div style='background: #e9ecef; padding: 15px; border-radius: 5px; margin-top: 20px;'>
                        <p style='margin: 0;'><strong>Нужна помощь?</strong></p>
                        <p style='margin: 5px 0 0 0;'>Свяжитесь с нами по телефону или email - мы всегда готовы помочь!</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
}

// Обработка AJAX запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isAjaxRequest()) {
    $action = $_POST['action'] ?? '';
    $emailManager = new EmailManager();
    
    switch ($action) {
        case 'send_test_email':
            if (!checkPermission(ROLE_ADMIN)) {
                sendJsonResponse(['success' => false, 'message' => 'Доступ запрещен'], 403);
                break;
            }
            
            $to = sanitizeInput($_POST['to'] ?? '');
            $subject = sanitizeInput($_POST['subject'] ?? 'Тестовое письмо');
            $message = sanitizeInput($_POST['message'] ?? 'Это тестовое письмо от AutoService IT');
            
            if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                sendJsonResponse(['success' => false, 'message' => 'Некорректный email адрес'], 400);
                break;
            }
            
            $result = $emailManager->sendEmail($to, $subject, $message, true);
            
            if ($result) {
                sendJsonResponse(['success' => true, 'message' => 'Письмо успешно отправлено']);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'Ошибка при отправке письма'], 500);
            }
            break;
            
        default:
            sendJsonResponse(['success' => false, 'message' => 'Неизвестное действие'], 400);
    }
}
?>