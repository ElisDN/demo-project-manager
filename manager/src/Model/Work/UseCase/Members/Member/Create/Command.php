<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Members\Member\Create;

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
     */
    public $group;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstName;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $lastName;
    /**
     * @var string
     * @Assert\Email()
     */
    public $email;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
