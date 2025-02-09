<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task\Executor;

use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class RevokeTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $executor = (new MemberBuilder())->build($group);

        $task->assignExecutor($executor);
        self::assertTrue($task->hasExecutor($executor->getId()));

        $task->revokeExecutor($executor->getId());
        self::assertEquals([], $task->getExecutors());
        self::assertFalse($task->hasExecutor($executor->getId()));
    }

    public function testNotFound(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $executor = (new MemberBuilder())->build($group);

        $this->expectExceptionMessage('Executor is not assigned.');
        $task->revokeExecutor($executor->getId());
    }
}

