<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task;

use App\Model\Work\Entity\Projects\Task\Id;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\Entity\Projects\Task\Type;
use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $member = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();

        $task = new Task(
            $id = new Id(1),
            $project,
            $member,
            $date = new \DateTimeImmutable(),
            $type = new Type(Type::FEATURE),
            $priority = 2,
            $name = 'Test Task',
            $content = 'Test Content'
        );

        self::assertEquals($id, $task->getId());
        self::assertEquals($project, $task->getProject());
        self::assertEquals($member, $task->getAuthor());
        self::assertEquals($date, $task->getDate());
        self::assertEquals($type, $task->getType());
        self::assertEquals($priority, $task->getPriority());
        self::assertEquals($name, $task->getName());
        self::assertEquals($content, $task->getContent());
        self::assertEquals(0, $task->getProgress());

        self::assertNull($task->getParent());
        self::assertNull($task->getPlanDate());
        self::assertNull($task->getStartDate());
        self::assertNull($task->getEndDate());

        self::assertTrue($task->isNew());
    }
}
