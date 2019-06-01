<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task;

use Doctrine\ORM\EntityManagerInterface;

class TaskRepository
{
    private $em;
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->connection = $em->getConnection();
    }

    public function add(Task $task): void
    {
        $this->em->persist($task);
    }

    public function nextId(): Id
    {
        return new Id((int)$this->connection->query('SELECT nextval(\'work_projects_tasks_seq\')')->fetchColumn());
    }
}
