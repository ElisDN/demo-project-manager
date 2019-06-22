<?php

declare(strict_types=1);

namespace App\DataFixtures\Work\Projects;

use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Project\Membership;
use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\Status;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\Entity\Projects\Task\Type;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class TaskFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        $projects = [
            $this->getReference(ProjectFixture::REFERENCE_FIRST),
            $this->getReference(ProjectFixture::REFERENCE_SECOND),
        ];

        $previous = [];

        $date = new \DateTimeImmutable('-100 days');

        for ($i = 0; $i < 100; $i++) {

            /** @var Project $project */
            /** @var Member $actor */
            $project = $faker->randomElement($projects);
            $actor = $faker->randomElement($project->getMemberships())->getMember();

            $task = $this->createRandomTask(new Id($i + 1), $project, $faker, $date);
            $date = $date->modify('+' . $faker->numberBetween(1, 3) . 'days 3minutes');

            if ($faker->boolean(40)) {
                $task->plan($actor, $date, $date->modify('+' . $faker->numberBetween(1, 30) . 'days'));
            }

            $memberships = $project->getMemberships();
            foreach ($faker->randomElements($memberships, $faker->numberBetween(0, count($memberships))) as $membership) {
                /** @var Membership $membership */
                $task->assignExecutor($actor, $date, $membership->getMember());
            }

            if ($faker->boolean(60)) {
                $task->changeProgress($actor, $date->modify('+5 hours'), $faker->randomElement([25, 50, 75]));
                $task->changeStatus(
                    $actor,
                    $date->modify('+' . $faker->numberBetween(1, 2) . 'days'),
                    new Status($faker->randomElement([
                        Status::WORKING,
                        Status::HELP,
                        Status::CHECKING,
                        Status::REJECTED,
                        Status::DONE,
                    ]))
                );
            }

            if ($faker->boolean()) {
                $task->changePriority($actor, $date->modify('+3 days'), $faker->randomElement(array_diff([1, 2, 3, 4], [$task->getPriority()])));
            }

            if ($previous && $faker->boolean(30)) {
                $task->setChildOf($actor, $date->modify('+5 days'), $faker->randomElement($previous));
            }

            $previous[] = $task;

            $manager->persist($task);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProjectFixture::class,
        ];
    }

    private function createRandomTask(Id $id, Project $project, Generator $faker, \DateTimeImmutable $date): Task
    {
        return new Task(
            $id,
            $project,
            $faker->randomElement($project->getMemberships())->getMember(),
            $date,
            new Type($faker->randomElement([Type::NONE, Type::FEATURE, Type::ERROR])),
            $faker->numberBetween(1, 4),
            trim($faker->sentence(random_int(2, 3)), '.'),
            $faker->paragraphs(3, true)
        );
    }
}
