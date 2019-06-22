<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\Event;

use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\Type;

class TaskTypeChanged
{
    public $actorId;
    public $taskId;
    public $type;

    public function __construct(MemberId $actorId, Id $taskId, Type $type)
    {
        $this->actorId = $actorId;
        $this->taskId = $taskId;
        $this->type = $type;
    }
}
