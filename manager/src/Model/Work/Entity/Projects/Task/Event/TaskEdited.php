<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\Event;

use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Task\Id;

class TaskEdited
{
    public $actorId;
    public $taskId;
    public $name;
    public $content;

    public function __construct(MemberId $actorId, Id $taskId, string $name, ?string $content)
    {
        $this->actorId = $actorId;
        $this->taskId = $taskId;
        $this->name = $name;
        $this->content = $content;
    }
}
