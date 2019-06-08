<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Files\Remove;

use App\Model\Flusher;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\TaskRepository;
use App\Model\Work\Entity\Projects\Task\File\Id as FileId;

class Handler
{
    private $tasks;
    private $flusher;

    public function __construct(
        TaskRepository $tasks,
        Flusher $flusher
    )
    {
        $this->tasks = $tasks;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $task = $this->tasks->get(new Id($command->id));

        $task->removeFile(new FileId($command->file));

        $this->flusher->flush();
    }
}

