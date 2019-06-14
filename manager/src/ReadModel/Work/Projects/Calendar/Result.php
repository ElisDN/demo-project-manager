<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Calendar;

class Result
{
    public $items;
    public $start;
    public $end;
    public $month;

    public function __construct(array $items, \DateTimeImmutable $start, \DateTimeImmutable $end, \DateTimeImmutable $month)
    {
        $this->items = $items;
        $this->start = $start;
        $this->end = $end;
        $this->month = $month;
    }
}
