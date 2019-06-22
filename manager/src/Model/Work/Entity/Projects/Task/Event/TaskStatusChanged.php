<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\Event;

use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\Status;

class TaskStatusChanged
{
    public $actorId;
    public $taskId;
    public $status;

    public function __construct(MemberId $actorId, Id $taskId, Status $status)
    {
        $this->actorId = $actorId;
        $this->taskId = $taskId;
        $this->status = $status;
    }
}
