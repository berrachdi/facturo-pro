<?php

declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use PDO;

class ClientRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, phone, address, siret, status, created_at
             FROM clients
             ORDER BY name ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, email, phone, address, siret, status, created_at
             FROM clients
             WHERE id = ?'
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result !== false ? $result : null;
    }

    public function create(array $data): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO clients (name, email, phone, address, siret)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['name'],
            $data['email']   ?: null,
            $data['phone']   ?: null,
            $data['address'] ?: null,
            $data['siret']   ?: null,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clients
             SET name = ?, email = ?, phone = ?, address = ?, siret = ?
             WHERE id = ?'
        );
        $stmt->execute([
            $data['name'],
            $data['email']   ?: null,
            $data['phone']   ?: null,
            $data['address'] ?: null,
            $data['siret']   ?: null,
            $id,
        ]);
    }

    public function archive(int $id): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE clients SET status = ? WHERE id = ?'
        );
        $stmt->execute(['archived', $id]);
    }
}
