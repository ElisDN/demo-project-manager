<?php

declare(strict_types=1);

namespace App\DataFixtures\Work;

use App\Model\Work\Entity\Members\Group\Group;
use App\Model\Work\Entity\Members\Group\Id;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class GroupFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $group = new Group(
            Id::next(),
            'Our Staff'
        );

        $manager->persist($group);

        $group = new Group(
            Id::next(),
            'Customers'
        );

        $manager->persist($group);

        $manager->flush();
    }
}
