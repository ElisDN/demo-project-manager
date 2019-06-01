<?php

declare(strict_types=1);

namespace App\DataFixtures\Work\Projects;

use App\Model\Work\Entity\Projects\Role\Permission;
use App\Model\Work\Entity\Projects\Role\Role;
use App\Model\Work\Entity\Projects\Role\Id;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoleFixture extends Fixture
{
    public const REFERENCE_MANAGER = 'work_project_role_manager';
    public const REFERENCE_GUEST = 'work_project_role_guest';

    public function load(ObjectManager $manager): void
    {
        $guest = $this->createRole('Guest', [
            Permission::VIEW_TASKS,
        ]);
        $manager->persist($guest);
        $this->setReference(self::REFERENCE_GUEST, $guest);

        $manage = $this->createRole('Manager', [
            Permission::MANAGE_PROJECT_MEMBERS,
            Permission::VIEW_TASKS,
            Permission::MANAGE_TASKS,
        ]);
        $manager->persist($manage);
        $this->setReference(self::REFERENCE_MANAGER, $manage);

        $manager->flush();
    }

    private function createRole(string $name, array $permissions): Role
    {
        return new Role(
            Id::next(),
            $name,
            $permissions
        );
    }
}
