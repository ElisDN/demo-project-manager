<?php

declare(strict_types=1);

namespace App\Tests\Unit\Model\Work\Entity\Projects\Task\File;

use App\Model\Work\Entity\Projects\Task\File\File;
use App\Model\Work\Entity\Projects\Task\File\Id;
use App\Model\Work\Entity\Projects\Task\File\Info;
use App\Tests\Builder\Work\Members\GroupBuilder;
use App\Tests\Builder\Work\Members\MemberBuilder;
use App\Tests\Builder\Work\Projects\ProjectBuilder;
use App\Tests\Builder\Work\Projects\TaskBuilder;
use PHPUnit\Framework\TestCase;

class AddTest extends TestCase
{
    public function testSuccess(): void
    {
        $group = (new GroupBuilder())->build();
        $author = (new MemberBuilder())->build($group);
        $project = (new ProjectBuilder())->build();
        $task = (new TaskBuilder())->build($project, $author);

        $member = (new MemberBuilder())->build($group);

        $task->addFile(
            $member,
            $date = new \DateTimeImmutable('+1 day'),
            $id = Id::next(),
            $info = new Info('path', 'name.jpg', 356)
        );

        self::assertCount(1, $files = $task->getFiles());
        self::assertInstanceOf(File::class, $file = end($files));

        self::assertEquals($id, $file->getId());
        self::assertEquals($date, $file->getDate());
        self::assertEquals($member, $file->getMember());
        self::assertEquals($info, $file->getInfo());
    }
}