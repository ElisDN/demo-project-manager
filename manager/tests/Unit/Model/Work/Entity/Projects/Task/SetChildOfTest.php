<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task;

use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class SetChildOfTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);
        $parent = (new TaskBuilder())->build($project, $member);

        $task->setChildOf($parent);

        self::assertEquals($parent, $task->getParent());
    }

    public function testSelf(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $this->expectExceptionMessage('Cyclomatic children.');
        $task->setChildOf($task);
    }

    public function testCycle(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $child1 = (new TaskBuilder())->build($project, $member);
        $child2 = (new TaskBuilder())->build($project, $member);

        $child1->setChildOf($task);
        $child2->setChildOf($child1);

        $this->expectExceptionMessage('Cyclomatic children.');
        $task->setChildOf($child2);
    }
}
