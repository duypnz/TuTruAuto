<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Environment;
use PHPMailer\PHPMailer\PHPMailer;
use InvalidArgumentException;
use RuntimeException;

final class EmailService
{
    private array $config;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    public function sendOrderConfirmation(array $order): void
    {
        $this->sendOrderEmail(
            $order,
            'Tu Tru Auto - Xác nhận đơn hàng #' . (int) ($order['id'] ?? 0),
            'Đã tiếp nhận đơn hàng của bạn.',
            'Chúng tôi đã nhận được yêu cầu đặt mua xe. Showroom sẽ liên hệ để xác nhận.'
        );
    }

    public function sendOrderStatusUpdate(array $order): void
    {
        $this->sendOrderEmail(
            $order,
            'Tu Tru Auto - Cập nhật đơn hàng #' . (int) ($order['id'] ?? 0),
            'Trạng thái đơn hàng đã được cập nhật.',
            'Thông tin trạng thái đơn hàng của bạn vừa thay đổi.'
        );
    }

    public function sendTestEmail(string $recipient): void
    {
        $recipient = trim($recipient);
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Địa chỉ email nhận thử không hợp lệ.');
        }

        $mail = $this->createMailer($recipient);
        $mail->Subject = 'Test Email - TuTruAuto';
        $mail->isHTML(false);
        $mail->Body = 'Chức năng gửi email TuTruAuto hoạt động thành công';
        $mail->AltBody = $mail->Body;
        $mail->send();
    }

    private function sendOrderEmail(
        array $order,
        string $subject,
        string $heading,
        string $message
    ): void {
        $email = trim((string) ($order['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Đơn hàng không có email khách hàng hợp lệ.');
        }

        $orderId = (int) ($order['id'] ?? 0);
        $customerName = trim((string) ($order['customer_name'] ?? ''));
        $status = (string) ($order['status'] ?? '');
        $statusLabels = [
            'pending' => 'Chờ xác nhận mua xe',
            'deposit_pending' => 'Chờ xác nhận đặt cọc',
            'deposit_paid' => 'Đã đặt cọc',
            'purchased' => 'Đã mua xe',
            'cancelled' => 'Đã hủy',
        ];
        if ($orderId < 1 || $customerName === '' || !isset($statusLabels[$status])) {
            throw new RuntimeException('Thiếu thông tin cần thiết để gửi email đơn hàng.');
        }

        $carNames = [];
        foreach (($order['items'] ?? []) as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            if ($name !== '') {
                $carNames[] = $name;
            }
        }
        if ($carNames === []) {
            throw new RuntimeException('Đơn hàng không có thông tin xe để gửi email.');
        }

        $escape = static fn (string $value): string => htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
        $escapedCarNames = array_map($escape, $carNames);
        $carList = '<ul><li>' . implode('</li><li>', $escapedCarNames) . '</li></ul>';
        $statusLabel = $statusLabels[$status];
        $body = '<!doctype html><html lang="vi"><body style="font-family:Arial,sans-serif;color:#222">'
            . '<h1>' . $escape($heading) . '</h1>'
            . '<p>' . $escape($message) . '</p>'
            . '<p><strong>Mã đơn hàng:</strong> #' . $orderId . '</p>'
            . '<p><strong>Tên khách hàng:</strong> ' . $escape($customerName) . '</p>'
            . '<p><strong>Tên xe:</strong></p>' . $carList
            . '<p><strong>Trạng thái đơn hàng:</strong> ' . $escape($statusLabel) . '</p>'
            . '<p>Trân trọng,<br>Tu Tru Auto</p></body></html>';
        $plainText = $heading . "\n" . $message
            . "\nMã đơn hàng: #" . $orderId
            . "\nTên khách hàng: " . $customerName
            . "\nTên xe: " . implode(', ', $carNames)
            . "\nTrạng thái đơn hàng: " . $statusLabel
            . "\nTu Tru Auto";

        $mail = $this->createMailer($email, $customerName);
        $senderEmail = trim($this->config['SMTP_USERNAME'] ?? '');
        if (strcasecmp($email, $senderEmail) !== 0) {
            $mail->addBCC($senderEmail);
        }
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $plainText;
        $mail->send();
    }

    private function createMailer(string $recipient, string $recipientName = ''): PHPMailer
    {
        $username = trim($this->config['SMTP_USERNAME'] ?? '');
        $password = trim($this->config['SMTP_PASSWORD'] ?? '');
        $fromEmail = trim($this->config['MAIL_FROM_EMAIL'] ?? '') ?: $username;
        if ($username === '' || $password === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Thiếu cấu hình Gmail SMTP hoặc địa chỉ người gửi không hợp lệ.');
        }

        $host = trim($this->config['SMTP_HOST'] ?? 'smtp.gmail.com');
        $port = filter_var($this->config['SMTP_PORT'] ?? '587', FILTER_VALIDATE_INT);
        $encryption = strtolower(trim($this->config['SMTP_ENCRYPTION'] ?? 'tls'));
        if ($host === '' || $port === false || $port < 1 || $encryption !== 'tls') {
            throw new RuntimeException('Cấu hình SMTP không hợp lệ; Gmail SMTP cần mã hóa TLS.');
        }

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $port;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = 'base64';
        $mail->setFrom($fromEmail, $this->config['MAIL_FROM_NAME'] ?? 'Tu Tru Auto');
        $mail->addAddress($recipient, $recipientName);

        return $mail;
    }

    private function loadConfig(): array
    {
        $config = [];
        foreach ([
            'SMTP_HOST',
            'SMTP_PORT',
            'SMTP_USERNAME',
            'SMTP_PASSWORD',
            'SMTP_ENCRYPTION',
            'MAIL_FROM_EMAIL',
            'MAIL_FROM_NAME',
        ] as $key) {
            $config[$key] = Environment::get($key);
        }

        return $config;
    }
}
