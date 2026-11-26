<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class SiteSetting
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function paymentDetails(): array
    {
        $stmt = $this->db->query(
            "SELECT setting_key, setting_value
             FROM site_settings
             WHERE setting_key IN ('bank_name', 'bank_code', 'bank_account', 'bank_holder', 'bank_branch')"
        );
        $settings = [];
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return [
            'bank_name' => $settings['bank_name'] ?? '',
            'bank_code' => $settings['bank_code'] ?? '',
            'bank_account' => $settings['bank_account'] ?? '',
            'bank_holder' => $settings['bank_holder'] ?? '',
            'bank_branch' => $settings['bank_branch'] ?? '',
        ];
    }

    public function savePaymentDetails(array $details): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO site_settings (setting_key, setting_value)
             VALUES (:setting_key, :setting_value)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );
        foreach (['bank_name', 'bank_code', 'bank_account', 'bank_holder', 'bank_branch'] as $key) {
            $stmt->execute([
                'setting_key' => $key,
                'setting_value' => trim((string) ($details[$key] ?? '')),
            ]);
        }
    }
}
