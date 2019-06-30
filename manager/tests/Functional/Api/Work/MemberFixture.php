<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api\Work;

use App\Model\User\Entity\User\User;
use App\Model\Work\Entity\Members\Member\Email;
use App\Model\Work\Entity\Members\Member\Id as MemberId;
use App\Model\Work\Entity\Projects\Project\Department\Id as DepartmentId;
use App\Model\Work\Entity\Projects\Project\Id;
use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\Entity\Projects\Role\Permission;
use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\RoleBuilder;
use App\Tests\Functional\AuthFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class MemberFixture extends Fixture implements DependentFixtureInterface
{
    public const REFERENCE_MEMBER_USER = 'test_work_member_user';
    public const REFERENCE_MEMBER_ADMIN = 'test_work_member_admin';

    public function load(ObjectManager $manager): void
    {
        $group = (new GroupBuilder())
            ->withName('Our Clients')
            ->build();

        $manager->persist($group);

        $user = $this->getUser(AuthFixture::REFERENCE_USER);

        $member = (new MemberBuilder())
            ->withId(new MemberId($user->getId()->getValue()))
            ->withEmail(new Email($user->getEmail()->getValue()))
            ->build($group);

        $manager->persist($member);
        $this->setReference(self::REFERENCE_MEMBER_USER, $member);

        $group = (new GroupBuilder())
            ->withName('Our Staff')
            ->build();

        $manager->persist($group);

        $user = $this->getUser(AuthFixture::REFERENCE_ADMIN);

        $member = (new MemberBuilder())
            ->withId(new MemberId($user->getId()->getValue()))
            ->withEmail(new Email($user->getEmail()->getValue()))
            ->build($group);

        $manager->persist($member);
        $this->setReference(self::REFERENCE_MEMBER_ADMIN, $member);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AuthFixture::class,
        ];
    }

    /**
     * @param string $reference
     * @return User|object
     */
    private function getUser(string $reference): User
    {
        return $this->getReference($reference);
    }
}

