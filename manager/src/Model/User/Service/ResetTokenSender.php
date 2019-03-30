<?php

declare(strict_types=1);

namespace App\Model\User\Service;

use App\Model\User\Entity\User\Email;
use App\Model\User\Entity\User\ResetToken;

interface ResetTokenSender
{
    public function send(Email $email, ResetToken $token): void;
}
