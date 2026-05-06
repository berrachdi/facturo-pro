<?php

declare(strict_types=1);

namespace App\Model;

class InvoiceLine
{
    public function __construct(
        public readonly int $id,
        public int $invoiceId,
        public string $description,
        public string $quantity,
        public string $unitPrice,
        public string $total,
    ) {}
}
