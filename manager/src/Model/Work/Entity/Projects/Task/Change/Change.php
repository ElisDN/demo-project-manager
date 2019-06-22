<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\Change;

use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Task\Task;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="work_projects_task_changes")
 */
class Change
{
    /**
     * @var Task
     * @ORM\ManyToOne(targetEntity="App\Model\Work\Entity\Projects\Task\Task", inversedBy="changes")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @ORM\Id
     */
    private $task;
    /**
     * @var string
     * @ORM\Column(type="work_projects_task_change_id")
     * @ORM\Id
     */
    private $id;
    /**
     * @var Member
     * @ORM\ManyToOne(targetEntity="App\Model\Work\Entity\Members\Member\Member")
     * @ORM\JoinColumn(name="actor_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $actor;
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;
    /**
     * @var Set
     * @ORM\Embedded(class="Set")
     */
    private $set;

    public function __construct(Task $task, Id $id, Member $actor, \DateTimeImmutable $date, Set $set)
    {
        $this->task = $task;
        $this->id = $id;
        $this->date = $date;
        $this->actor = $actor;
        $this->set = $set;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getActor(): Member
    {
        return $this->actor;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getSet(): Set
    {
        return $this->set;
    }
}
