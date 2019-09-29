<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task;

use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class SetRootTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);
        $parent = (new TaskBuilder())->build($project, $member);

        $task->setChildOf($member, new \DateTimeImmutable(), $parent);

        self::assertEquals($parent, $task->getParent());

        $task->setRoot();

        self::assertNull($task->getParent());
    }
}
