<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\Event;

use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Task\Id;

class TaskPlanChanged
{
    public $actorId;
    public $taskId;
    public $planDate;

    public function __construct(MemberId $actorId, Id $taskId, ?\DateTimeImmutable $planDate)
    {
        $this->actorId = $actorId;
        $this->taskId = $taskId;
        $this->planDate = $planDate;
    }
}
