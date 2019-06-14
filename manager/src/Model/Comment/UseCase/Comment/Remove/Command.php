<?php

declare(strict_types=1);

namespace App\Model\Comment\UseCase\Comment\Remove;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
