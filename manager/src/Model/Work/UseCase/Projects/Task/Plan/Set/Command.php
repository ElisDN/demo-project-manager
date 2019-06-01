<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Plan\Set;

use App\Model\Work\Entity\Projects\Task\Task;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @Assert\NotBlank()
     */
    public $date;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public static function fromTask(Task $task): self
    {
        $command = new self($task->getId()->getValue());
        $command->date = $task->getPlanDate() ?: new \DateTimeImmutable();
        return $command;
    }
}

