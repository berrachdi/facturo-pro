<?php

declare(strict_types=1);

namespace App\Model;

class Invoice
{
    public function __construct(
        public readonly int $id,
        public int $clientId,
        public int $userId,
        public string $number,
        public string $status,
        public string $issueDate,
        public string $dueDate,
        public string $totalHt,
        public string $tvaRate,
        public string $totalTtc,
        public ?string $notes,
        public string $createdAt,
    ) {}
}
