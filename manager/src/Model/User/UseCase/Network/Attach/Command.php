<?php

declare(strict_types=1);

namespace App\Model\User\UseCase\Network\Attach;

class Command
{
    /**
     * @var string
     */
    public $user;
    /**
     * @var string
     */
    public $network;
    /**
     * @var string
     */
    public $identity;

    public function __construct(string $user, string $network, string $identity)
    {
        $this->user = $user;
        $this->network = $network;
        $this->identity = $identity;
    }
}
