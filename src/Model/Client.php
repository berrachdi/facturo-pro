<?php

declare(strict_types=1);

namespace App\Model;

class Client
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public ?string $email,
        public ?string $phone,
        public ?string $address,
        public ?string $siret,
        public string $status,
        public string $createdAt,
    ) {}
}
