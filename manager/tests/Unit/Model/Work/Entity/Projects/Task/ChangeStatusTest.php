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

        $task->changeStatus(
            $member,
            $date = new \DateTimeImmutable(),
            $status = new Status(Status::WORKING)
        );

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

        $task->changeStatus(
            $member,
            $date = new \DateTimeImmutable(),
            $status = new Status(Status::WORKING)
        );

        $this->expectExceptionMessage('Status is already same.');
        $task->changeStatus($member, $date, $status);
    }

    public function testDonePriority(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $member);

        $task->changeStatus($member, new \DateTimeImmutable(), $status = new Status(Status::DONE));

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
            $member,
            $date = new \DateTimeImmutable('+1 day'),
            new Status(Status::WORKING)
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
            $member,
            $startDate = new \DateTimeImmutable('+1 day'),
            new Status(Status::WORKING)
        );

        $task->changeStatus(
            $member,
            $endDate = new \DateTimeImmutable('+1 day'),
            new Status(Status::DONE)
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
            $member,
            $endDate = new \DateTimeImmutable('+1 day'),
            new Status(Status::DONE)
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
            $member,
            $endDate = new \DateTimeImmutable('+1 day'),
            new Status(Status::DONE)
        );

        $task->changeStatus(
            $member,
            new \DateTimeImmutable('+2 days'),
            new Status(Status::WORKING)
        );

        self::assertEquals($endDate, $task->getStartDate());
        self::assertNull($task->getEndDate());
    }
}
