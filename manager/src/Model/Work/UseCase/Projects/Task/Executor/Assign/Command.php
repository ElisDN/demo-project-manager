<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Executor\Assign;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @var array
     * @Assert\NotBlank()
     */
    public $members;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}
