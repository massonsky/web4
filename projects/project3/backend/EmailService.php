<?php
require_once 'config.php';

// Подключаем PHPMailer
// Попытка загрузить через Composer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Альтернативный путь для ручной установки
    $phpmailerPath = __DIR__ . '/PHPMailer/src/';
    if (!file_exists($phpmailerPath . 'Exception.php') || 
        !file_exists($phpmailerPath . 'PHPMailer.php') || 
        !file_exists($phpmailerPath . 'SMTP.php')) {
        throw new Exception('PHPMailer not found. Please install PHPMailer via Composer or manually.');
    }
    
    require_once $phpmailerPath . 'Exception.php';
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }
    
    private function configure() {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = Config::get('MAIL_HOST', 'smtp.gmail.com');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Config::get('MAIL_USERNAME');
            $this->mailer->Password = Config::get('MAIL_PASSWORD');
            $this->mailer->SMTPSecure = Config::get('MAIL_ENCRYPTION', 'tls');
            $this->mailer->Port = Config::get('MAIL_PORT', 587);
            
            // Default sender
            $this->mailer->setFrom(
                Config::get('MAIL_FROM_ADDRESS'), 
                Config::get('MAIL_FROM_NAME', 'Cargo Transport')
            );
            
            // Encoding
            $this->mailer->CharSet = 'UTF-8';
            
        } catch (Exception $e) {
            error_log('Email configuration error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    public function sendOrderConfirmation($orderData) {
        try {
            // Recipients
            $this->mailer->addAddress(Config::get('ADMIN_EMAIL', 'admin@example.com'));
            
            // Content
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Новый заказ #' . $orderData['order_number'];
            
            $htmlBody = $this->generateOrderEmailTemplate($orderData);
            $this->mailer->Body = $htmlBody;
            
            // Plain text version
            $this->mailer->AltBody = $this->generatePlainTextOrder($orderData);
            
            $result = $this->mailer->send();
            
            // Log email sending
            $this->logEmail($orderData['order_number'], 'order_confirmation', $result);
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Email sending error: ' . $e->getMessage());
            $this->logEmail($orderData['order_number'] ?? 'unknown', 'order_confirmation', false, $e->getMessage());
            return false;
        } finally {
            // Clear addresses for next use
            $this->mailer->clearAddresses();
        }
    }
    
    private function generateOrderEmailTemplate($orderData) {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Новый заказ</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .order-details { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .total { font-size: 18px; font-weight: bold; color: #667eea; }
                .footer { text-align: center; padding: 20px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🚛 Cargo Transport</h1>
                    <p>Новый заказ на перевозку</p>
                </div>
                
                <div class="content">
                    <h2>Заказ #' . htmlspecialchars($orderData['order_number']) . '</h2>
                    
                    <div class="order-details">
                        <h3>📍 Маршрут</h3>
                        <p><strong>Откуда:</strong> ' . htmlspecialchars($orderData['from']) . '</p>
                        <p><strong>Куда:</strong> ' . htmlspecialchars($orderData['to']) . '</p>
                        <p><strong>Расстояние:</strong> ' . htmlspecialchars($orderData['distance']) . ' км</p>
                    </div>
                    
                    <div class="order-details">
                        <h3>📦 Груз</h3>
                        <p><strong>Вес:</strong> ' . htmlspecialchars($orderData['weight']) . ' кг</p>
                        <p><strong>Тип груза:</strong> ' . htmlspecialchars($orderData['cargo_type']) . '</p>
                    </div>
                    
                    <div class="order-details">
                        <h3>🚚 Услуги</h3>
                        <p><strong>Погрузка:</strong> ' . ($orderData['loading'] ? 'Да' : 'Нет') . '</p>
                        <p><strong>Дополнительные услуги:</strong> ' . htmlspecialchars($orderData['additional_services']) . '</p>
                    </div>
                    
                    <div class="order-details">
                        <h3>💰 Стоимость</h3>
                        <p><strong>Базовая стоимость:</strong> ' . number_format($orderData['base_cost'], 0, ',', ' ') . ' ₽</p>
                        <p><strong>Погрузка:</strong> ' . number_format($orderData['loading_cost'], 0, ',', ' ') . ' ₽</p>
                        <p><strong>Доп. услуги:</strong> ' . number_format($orderData['additional_cost'], 0, ',', ' ') . ' ₽</p>
                        <p class="total">Итого: ' . number_format($orderData['total_cost'], 0, ',', ' ') . ' ₽</p>
                    </div>
                    
                    <div class="order-details">
                        <h3>📅 Информация о заказе</h3>
                        <p><strong>Дата создания:</strong> ' . date('d.m.Y H:i', strtotime($orderData['created_at'])) . '</p>
                        <p><strong>Статус:</strong> Ожидает обработки</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Это автоматическое уведомление от системы Cargo Transport</p>
                    <p>© ' . date('Y') . ' Cargo Transport. Все права защищены.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    private function generatePlainTextOrder($orderData) {
        $text = "НОВЫЙ ЗАКАЗ #" . $orderData['order_number'] . "\n\n";
        $text .= "МАРШРУТ:\n";
        $text .= "Откуда: " . $orderData['from'] . "\n";
        $text .= "Куда: " . $orderData['to'] . "\n";
        $text .= "Расстояние: " . $orderData['distance'] . " км\n\n";
        
        $text .= "ГРУЗ:\n";
        $text .= "Вес: " . $orderData['weight'] . " кг\n";
        $text .= "Тип: " . $orderData['cargo_type'] . "\n\n";
        
        $text .= "УСЛУГИ:\n";
        $text .= "Погрузка: " . ($orderData['loading'] ? 'Да' : 'Нет') . "\n";
        $text .= "Доп. услуги: " . $orderData['additional_services'] . "\n\n";
        
        $text .= "СТОИМОСТЬ:\n";
        $text .= "Базовая: " . number_format($orderData['base_cost'], 0, ',', ' ') . " ₽\n";
        $text .= "Погрузка: " . number_format($orderData['loading_cost'], 0, ',', ' ') . " ₽\n";
        $text .= "Доп. услуги: " . number_format($orderData['additional_cost'], 0, ',', ' ') . " ₽\n";
        $text .= "ИТОГО: " . number_format($orderData['total_cost'], 0, ',', ' ') . " ₽\n\n";
        
        $text .= "Дата: " . date('d.m.Y H:i', strtotime($orderData['created_at'])) . "\n";
        $text .= "Статус: Ожидает обработки\n";
        
        return $text;
    }
    
    private function logEmail($orderNumber, $type, $success, $error = null) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'order_number' => $orderNumber,
            'email_type' => $type,
            'success' => $success ? 'true' : 'false',
            'error' => $error
        ];
        
        $logEntry = json_encode($logData) . "\n";
        $logDir = __DIR__ . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        file_put_contents($logDir . '/email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function testConnection() {
        try {
            $this->mailer->smtpConnect();
            $this->mailer->smtpClose();
            return true;
        } catch (Exception $e) {
            error_log('SMTP connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
?>