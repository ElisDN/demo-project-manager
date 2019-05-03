<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Members\Member;

use App\Model\EntityNotFoundException;
use App\Model\Work\Entity\Members\Group\Id as GroupId;
use Doctrine\ORM\EntityManagerInterface;

class MemberRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repo;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(Member::class);
        $this->em = $em;
    }

    public function has(Id $id): bool
    {
        return $this->repo->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->andWhere('t.id = :id')
                ->setParameter(':id', $id->getValue())
                ->getQuery()->getSingleScalarResult() > 0;
    }

    public function hasByGroup(GroupId $id): bool
    {
        return $this->repo->createQueryBuilder('t')
                ->select('COUNT(t.id)')
                ->andWhere('t.group = :group')
                ->setParameter(':group', $id->getValue())
                ->getQuery()->getSingleScalarResult() > 0;
    }

    public function get(Id $id): Member
    {
        /** @var Member $member */
        if (!$member = $this->repo->find($id->getValue())) {
            throw new EntityNotFoundException('Member is not found.');
        }
        return $member;
    }

    public function add(Member $member): void
    {
        $this->em->persist($member);
    }
}
