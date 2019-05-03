<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Members\Member\Filter;

use App\Model\Work\Entity\Members\Member\Status;

class Filter
{
    public $name;
    public $email;
    public $group;
    public $status = Status::ACTIVE;
}
