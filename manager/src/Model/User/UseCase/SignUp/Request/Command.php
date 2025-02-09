<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\SignUp\Request;

class Command
{
    /**
     * @var string
     */
    public $email;
    /**
     * @var string
     */
    public $password;
}
