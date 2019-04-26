<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Email\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    public $email;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
