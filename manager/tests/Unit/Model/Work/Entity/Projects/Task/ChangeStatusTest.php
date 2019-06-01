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

        $task->changeStatus($status = new Status(Status::WORKING), $date = new \DateTimeImmutable());

        self::assertEquals($status, $task->getStatus());

        self::assertEquals($date, $task->getStartDate());
        self::assertNull($task->getEndDate());
    }

    public function testAlready(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($status = new Status(Status::WORKING), $date = new \DateTimeImmutable());

        $this->expectExceptionMessage('Status is already same.');
        $task->changeStatus($status, $date);
    }

    public function testDonePriority(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($status = new Status(Status::DONE), new \DateTimeImmutable());

        self::assertEquals($status, $task->getStatus());
        self::assertEquals(100, $task->getProgress());
    }

    public function testStartDate(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus(
            new Status(Status::WORKING),
            $date = new \DateTimeImmutable('+1 day')
        );

        self::assertEquals($date, $task->getStartDate());
        self::assertNull($task->getEndDate());
    }

    public function testEndDateWithStartDate(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus(
            new Status(Status::WORKING),
            $startDate = new \DateTimeImmutable('+1 day')
        );

        $task->changeStatus(
            new Status(Status::DONE),
            $endDate = new \DateTimeImmutable('+1 day')
        );

        self::assertEquals($startDate, $task->getStartDate());
        self::assertEquals($endDate, $task->getEndDate());
    }

    public function testEndDateWithoutStartDate(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus(
            new Status(Status::DONE),
            $endDate = new \DateTimeImmutable('+1 day')
        );

        self::assertEquals($endDate, $task->getStartDate());
        self::assertEquals($endDate, $task->getEndDate());
    }

    public function testEndDateReset(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus(
            new Status(Status::DONE),
            $endDate = new \DateTimeImmutable('+1 day')
        );

        $task->changeStatus(
            new Status(Status::WORKING),
            new \DateTimeImmutable('+2 days')
        );

        self::assertEquals($endDate, $task->getStartDate());
        self::assertNull($task->getEndDate());
    }
}
