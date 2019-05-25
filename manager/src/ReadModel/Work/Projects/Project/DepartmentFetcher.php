<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Project;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

class DepartmentFetcher
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function listOfProject(string $project): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('work_projects_project_departments')
            ->andWhere('project_id = :project')
            ->setParameter(':project', $project)
            ->orderBy('name')
            ->execute();

        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    public function allOfProject(string $project): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.id',
                'd.name',
                '(
                    SELECT COUNT(ms.member_id)
                    FROM work_projects_project_memberships ms
                    INNER JOIN work_projects_project_membership_departments md ON ms.id = md.membership_id
                    WHERE md.department_id = d.id AND ms.project_id = :project
                ) AS members_count'
            )
            ->from('work_projects_project_departments', 'd')
            ->andWhere('project_id = :project')
            ->setParameter(':project', $project)
            ->orderBy('name')
            ->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
