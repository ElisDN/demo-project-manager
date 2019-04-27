<?php

declare(strict_types=1);

namespace App\Model\User\Service;

use Ramsey\Uuid\Uuid;

class PasswordGenerator
{
    public function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}