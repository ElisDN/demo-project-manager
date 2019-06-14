<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Calendar\Query;

class Query
{
    public $member;
    public $project;
    public $year;
    public $month;

    public function __construct(int $year, int $month)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public static function fromDate(\DateTimeImmutable $date): self
    {
        return new self((int)$date->format('Y'), (int)$date->format('m'));
    }

    public function forProject(string $project): self
    {
        $clone = clone $this;
        $clone->project = $project;
        return $clone;
    }

    public function forMember(string $member): self
    {
        $clone = clone $this;
        $clone->member = $member;
        return $clone;
    }
}
