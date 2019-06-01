<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task;

use Webmozart\Assert\Assert;

class Id
{
    private $value;

    public function __construct(int $value)
    {
        Assert::notEmpty($value);
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}
