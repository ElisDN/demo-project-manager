<?php

declare(strict_types=1);

namespace App\Model;

interface AggregateRoot
{
    public function releaseEvents(): array;
}
