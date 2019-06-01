<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task;

use App\Model\Work\Entity\Projects\Task\Status;
use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class ChangeStatusTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($status = new Status(Status::WORKING));

        self::assertEquals($status, $task->getStatus());
    }

    public function testAlready(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($status = new Status(Status::WORKING));

        $this->expectExceptionMessage('Status is already same.');
        $task->changeStatus($status);
    }

    public function testDonePriority(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($status = new Status(Status::DONE));

        self::assertEquals($status, $task->getStatus());
        self::assertEquals(100, $task->getProgress());
    }
}
