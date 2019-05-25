<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Role\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;
    /**
     * @var string[]
     */
    public $permissions;
}