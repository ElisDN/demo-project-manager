<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Create;

use Symfony\Component\Validator\Constraints as Assert;

class NameRow
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
