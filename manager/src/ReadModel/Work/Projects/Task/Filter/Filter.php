<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Task\Filter;

class Filter
{
    public $member;
    public $author;
    public $project;
    public $text;
    public $type;
    public $status;
    public $priority;
    public $executor;
    public $roots;

    private function __construct(?string $project)
    {
        $this->project = $project;
    }

    public static function forProject(string $project): self
    {
        return new self($project);
    }

    public static function all(): self
    {
        return new self(null);
    }

    public function forMember(string $member): self
    {
        $clone = clone $this;
        $clone->member = $member;
        return $clone;
    }

    public function forAuthor(string $author): self
    {
        $clone = clone $this;
        $clone->author = $author;
        return $clone;
    }

    public function forExecutor(string $executor): self
    {
        $clone = clone $this;
        $clone->executor = $executor;
        return $clone;
    }
}
