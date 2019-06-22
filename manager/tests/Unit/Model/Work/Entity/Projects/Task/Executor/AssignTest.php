<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task\Executor;

use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class AssignTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $executor = (new MemberBuilder())->build($group);

        self::assertFalse($task->hasExecutor($executor->getId()));

        $task->assignExecutor($member, new \DateTimeImmutable(), $executor);

        self::assertEquals([$executor], $task->getExecutors());
        self::assertTrue($task->hasExecutor($executor->getId()));
    }

    public function testAlready(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $executor = (new MemberBuilder())->build($group);

        $task->assignExecutor($member, new \DateTimeImmutable(), $executor);

        $this->expectExceptionMessage('Executor is already assigned.');
        $task->assignExecutor($member, new \DateTimeImmutable(), $executor);
    }
}

