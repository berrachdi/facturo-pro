<?php

declare(strict_types=1);

namespace App\Model;

class Payment
{
    public function __construct(
        public readonly int $id,
        public int $invoiceId,
        public string $amount,
        public string $paidAt,
        public string $method,
        public ?string $note,
    ) {}
}
