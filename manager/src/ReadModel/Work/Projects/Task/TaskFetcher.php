<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Task;

use App\Model\Work\Entity\Projects\Task\Task;
use App\ReadModel\Work\Projects\Task\Filter\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class TaskFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public function __construct(Connection $connection, EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $connection;
        $this->paginator = $paginator;
        $this->repository = $em->getRepository(Task::class);
    }

    public function find(string $id): ?Task
    {
        return $this->repository->find($id);
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param int $size
     * @param string $sort
     * @param string $direction
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, int $size, ?string $sort, ?string $direction): PaginationInterface
    {
        if (!\in_array($sort, [null, 't.id', 't.date', 'author_name', 'project_name', 'name', 't.type', 't.plan_date', 't.progress', 't.priority', 't.status'], true)) {
            throw new \UnexpectedValueException('Cannot sort by ' . $sort);
        }

        $qb = $this->connection->createQueryBuilder()
            ->select(
                't.id',
                't.date',
                't.author_id',
                'TRIM(CONCAT(m.name_first, \' \', m.name_last)) AS author_name',
                't.project_id',
                'p.name project_name',
                't.name',
                't.parent_id AS parent',
                't.type',
                't.priority',
                't.progress',
                't.plan_date',
                't.status'
            )
            ->from('work_projects_tasks', 't')
            ->innerJoin('t', 'work_members_members', 'm', 't.author_id = m.id')
            ->innerJoin('t', 'work_projects_projects', 'p', 't.project_id = p.id');

        if ($filter->member) {
            $qb->innerJoin('t', 'work_projects_project_memberships', 'ms', 't.project_id = ms.project_id');
            $qb->andWhere('ms.member_id = :member');
            $qb->setParameter(':member', $filter->member);
        }

        if ($filter->project) {
            $qb->andWhere('t.project_id = :project');
            $qb->setParameter(':project', $filter->project);
        }

        if ($filter->author) {
            $qb->andWhere('t.author_id = :author');
            $qb->setParameter(':author', $filter->author);
        }

        if ($filter->text) {
            $vector = "(setweight(to_tsvector(t.name),'A') || setweight(to_tsvector(coalesce(t.content,'')), 'B'))";
            $query = 'plainto_tsquery(:text)';
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(CONCAT(t.name, \' \', coalesce(t.content, \'\')))', ':text'),
                "$vector @@ $query"
            ));
            $qb->setParameter(':text', '%' . mb_strtolower($filter->text) . '%');
            if (empty($sort)) {
                $sort = "ts_rank($vector, $query)";
                $direction = 'desc';
            }
        }

        if ($filter->type) {
            $qb->andWhere('t.type = :type');
            $qb->setParameter(':type', $filter->type);
        }

        if ($filter->priority) {
            $qb->andWhere('t.priority = :priority');
            $qb->setParameter(':priority', $filter->priority);
        }

        if ($filter->status) {
            $qb->andWhere('t.status = :status');
            $qb->setParameter(':status', $filter->status);
        }

        if ($filter->executor) {
            $qb->innerJoin('t', 'work_projects_tasks_executors', 'e', 'e.task_id = t.id');
            $qb->andWhere('e.member_id = :executor');
            $qb->setParameter(':executor', $filter->executor);
        }

        if ($filter->roots) {
            $qb->andWhere('t.parent_id IS NULL');
        }

        if (!$sort) {
            $sort = 't.id';
            $direction = $direction ?: 'desc';
        } else {
            $direction = $direction ?: 'asc';
        }

        $qb->orderBy($sort, $direction);

        $pagination = $this->paginator->paginate($qb, $page, $size);

        $tasks = (array)$pagination->getItems();
        $executors = $this->batchLoadExecutors(array_column($tasks, 'id'));

        $pagination->setItems(array_map(static function (array $task) use ($executors) {
            return array_merge($task, [
                'executors' => array_filter($executors, static function (array $executor) use ($task) {
                    return $executor['task_id'] === $task['id'];
                }),
            ]);
        }, $tasks));

        return $pagination;
    }

    public function childrenOf(int $task): array
    {
        $stmt = $this
            ->connection->createQueryBuilder()
            ->select(
                't.id',
                't.date',
                't.project_id',
                'p.name project_name',
                't.name',
                't.parent_id AS parent',
                't.type',
                't.priority',
                't.progress',
                't.plan_date',
                't.status'
            )
            ->from('work_projects_tasks', 't')
            ->innerJoin('t', 'work_projects_projects', 'p', 't.project_id = p.id')
            ->andWhere('t.parent_id = :parent')
            ->setParameter(':parent', $task)
            ->orderBy('date', 'desc')
            ->execute();

        $tasks = $stmt->fetchAll(FetchMode::ASSOCIATIVE);
        $executors = $this->batchLoadExecutors(array_column($tasks, 'id'));

        return array_map(static function (array $task) use ($executors) {
            return array_merge($task, [
                'executors' => array_filter($executors, static function (array $executor) use ($task) {
                    return $executor['task_id'] === $task['id'];
                }),
            ]);
        }, $tasks);
    }

    private function batchLoadExecutors(array $ids): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'e.task_id',
                'TRIM(CONCAT(m.name_first, \' \', m.name_last)) AS name'
            )
            ->from('work_projects_tasks_executors', 'e')
            ->innerJoin('e', 'work_members_members', 'm', 'm.id = e.member_id')
            ->andWhere('e.task_id IN (:task)')
            ->setParameter(':task', $ids, Connection::PARAM_INT_ARRAY)
            ->orderBy('name')
            ->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function lastOwn(string $member, int $limit): array
    {
        $stmt = $this
            ->connection->createQueryBuilder()
            ->select(
                't.id',
                't.project_id',
                'p.name project_name',
                't.name',
                't.status'
            )
            ->from('work_projects_tasks', 't')
            ->innerJoin('t', 'work_projects_projects', 'p', 't.project_id = p.id')
            ->andWhere('t.author_id = :member')
            ->setParameter(':member', $member)
            ->orderBy('date', 'desc')
            ->setMaxResults($limit)
            ->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }

    public function lastForMe(string $member, int $limit): array
    {
        $stmt = $this
            ->connection->createQueryBuilder()
            ->select(
                't.id',
                't.project_id',
                'p.name project_name',
                't.name',
                't.status'
            )
            ->from('work_projects_tasks', 't')
            ->innerJoin('t', 'work_projects_projects', 'p', 't.project_id = p.id')
            ->innerJoin('t', 'work_projects_tasks_executors', 'e', 'e.task_id = t.id')
            ->andWhere('e.member_id = :executor')
            ->setParameter(':executor', $member)
            ->orderBy('date', 'desc')
            ->setMaxResults($limit)
            ->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }
}
