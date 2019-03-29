<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Network\Auth;

class Command
{
    /**
     * @var string
     */
    public $network;
    /**
     * @var string
     */
    public $identity;
}
