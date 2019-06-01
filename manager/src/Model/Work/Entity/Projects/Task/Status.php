<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task;

use Webmozart\Assert\Assert;

class Status
{
    public const NEW = 'new';
    public const WORKING = 'working';
    public const HELP = 'help';
    public const CHECKING = 'checking';
    public const REJECTED = 'rejected';
    public const DONE = 'done';

    private $name;

    public function __construct(string $name)
    {
        Assert::oneOf($name, [
            self::NEW,
            self::WORKING,
            self::HELP,
            self::CHECKING,
            self::REJECTED,
            self::DONE,
        ]);

        $this->name = $name;
    }

    public static function new(): self
    {
        return new self(self::NEW);
    }

    public static function working(): self
    {
        return new self(self::WORKING);
    }

    public function isEqual(self $other): bool
    {
        return $this->getName() === $other->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isDone(): bool
    {
        return $this->name === self::DONE;
    }

    public function isNew(): bool
    {
        return $this->name === self::NEW;
    }

    public function isWorking(): bool
    {
        return $this->name === self::WORKING;
    }
}
