<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class ActionFetcher
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function allForTask(int $id): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'c.*',
                'TRIM(CONCAT(actor.name_first, \' \', actor.name_last)) AS actor_name',
                'TRIM(CONCAT(set_executor.name_first, \' \', set_executor.name_last)) AS set_executor_name',
                'TRIM(CONCAT(set_revoked_executor.name_first, \' \', set_revoked_executor.name_last)) AS set_revoked_executor_name',
                'set_project.name AS set_project_name'
            )
            ->from('work_projects_task_changes', 'c')
            ->leftJoin('c', 'work_members_members', 'actor', 'c.actor_id = actor.id')
            ->leftJoin('c', 'work_members_members', 'set_executor', 'c.set_executor_id = set_executor.id')
            ->leftJoin('c', 'work_members_members', 'set_revoked_executor', 'c.set_revoked_executor_id = set_executor.id')
            ->leftJoin('c', 'work_projects_tasks', 'task', 'c.task_id = task.id')
            ->leftJoin('c', 'work_projects_projects', 'set_project', 'c.set_project_id = set_project.id')
            ->andWhere('task.id = :task_id')
            ->setParameter(':task_id', $id)
            ->orderBy('c.date', 'asc')
            ->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
