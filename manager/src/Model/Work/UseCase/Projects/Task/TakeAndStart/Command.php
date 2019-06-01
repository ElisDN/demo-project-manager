<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\TakeAndStart;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @Assert\NotBlank()
     */
    public $member;

    public function __construct(int $id, string $member)
    {
        $this->id = $id;
        $this->member = $member;
    }
}
