<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

class InvoiceRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function generateNumber(): string
    {
        $year = date('Y');
        $stmt = $this->pdo->prepare(
            "SELECT number FROM invoices
             WHERE number LIKE ?
             ORDER BY number DESC
             LIMIT 1"
        );
        $stmt->execute(["FAC-{$year}-%"]);
        $last = $stmt->fetchColumn();

        $seq = $last ? ((int) substr((string) $last, -3)) + 1 : 1;
        return sprintf('FAC-%s-%03d', $year, $seq);
    }

    public function findAll(array $filters = []): array
    {
        $sql = 'SELECT i.id, i.number, i.status, i.issue_date, i.due_date,
                       i.total_ht, i.tva_rate, i.total_ttc, i.created_at,
                       c.name AS client_name
                FROM invoices i
                JOIN clients c ON c.id = i.client_id';

        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'i.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['client_id'])) {
            $where[]  = 'i.client_id = ?';
            $params[] = (int) $filters['client_id'];
        }

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY i.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT i.*, c.name AS client_name
             FROM invoices i
             JOIN clients c ON c.id = i.client_id
             WHERE i.id = ?'
        );
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if ($invoice === false) {
            return null;
        }

        $invoice['lines'] = $this->findLines($id);
        return $invoice;
    }

    public function save(array $data, array $lines): int
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO invoices
                    (client_id, user_id, number, status, issue_date, due_date,
                     total_ht, tva_rate, total_ttc, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $data['client_id'],
                $data['user_id'],
                $data['number'],
                $data['status'],
                $data['issue_date'],
                $data['due_date'],
                $data['total_ht'],
                $data['tva_rate'],
                $data['total_ttc'],
                $data['notes'] ?: null,
            ]);

            $invoiceId = (int) $this->pdo->lastInsertId();
            $this->insertLines($invoiceId, $lines);

            $this->pdo->commit();
            return $invoiceId;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data, array $lines): void
    {
        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'UPDATE invoices
                 SET client_id = ?, status = ?, issue_date = ?, due_date = ?,
                     total_ht = ?, tva_rate = ?, total_ttc = ?, notes = ?
                 WHERE id = ?'
            );
            $stmt->execute([
                $data['client_id'],
                $data['status'],
                $data['issue_date'],
                $data['due_date'],
                $data['total_ht'],
                $data['tva_rate'],
                $data['total_ttc'],
                $data['notes'] ?: null,
                $id,
            ]);

            $this->pdo->prepare('DELETE FROM invoice_lines WHERE invoice_id = ?')->execute([$id]);
            $this->insertLines($id, $lines);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->pdo->prepare('UPDATE invoices SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM invoices WHERE id = ?')->execute([$id]);
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function findLines(int $invoiceId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, invoice_id, description, quantity, unit_price, total
             FROM invoice_lines
             WHERE invoice_id = ?
             ORDER BY id ASC'
        );
        $stmt->execute([$invoiceId]);
        return $stmt->fetchAll();
    }

    private function insertLines(int $invoiceId, array $lines): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO invoice_lines (invoice_id, description, quantity, unit_price, total)
             VALUES (?, ?, ?, ?, ?)'
        );
        foreach ($lines as $line) {
            $stmt->execute([
                $invoiceId,
                $line['description'],
                $line['quantity'],
                $line['unit_price'],
                $line['total'],
            ]);
        }
    }
}
