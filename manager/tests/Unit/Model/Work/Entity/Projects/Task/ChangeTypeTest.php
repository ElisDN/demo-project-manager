<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task;

use App\Model\Work\Entity\Projects\Task\Type;
use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class ChangeTypeTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())
            ->withType(new Type(Type::FEATURE))
            ->build($project, $member);

        $task->changeType($member, new \DateTimeImmutable(), $type = new Type(Type::ERROR));

        self::assertEquals($type, $task->getType());
    }

    public function testAlready(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())
            ->withType($type = new Type(Type::FEATURE))
            ->build($project, $member);

        $this->expectExceptionMessage('Type is already same.');
        $task->changeType($member, new \DateTimeImmutable(), $type);
    }
}
