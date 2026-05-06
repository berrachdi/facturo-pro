<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

class PaymentRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findByInvoice(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, invoice_id, amount, paid_at, method, note
             FROM payments
             WHERE invoice_id = ?
             ORDER BY paid_at ASC'
        );
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    public function save(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO payments (invoice_id, amount, paid_at, method, note)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['invoice_id'],
            $data['amount'],
            $data['paid_at'],
            $data['method'],
            $data['note'] ?: null,
        ]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM payments WHERE id = ?')->execute([$id]);
    }

    public function sumByInvoice(int $invoiceId): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(amount), 0) FROM payments WHERE invoice_id = ?'
        );
        $stmt->execute([$invoiceId]);
        return (float) $stmt->fetchColumn();
    }
}
