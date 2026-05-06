<?php

declare(strict_types=1);

namespace App\Model;

class User
{
    public function __construct(
        public readonly int $id,
        public string $name,
        public string $email,
        public string $password,
        public string $createdAt,
    ) {}
}
