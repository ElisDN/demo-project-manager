<?php

declare(strict_types=1);

namespace App\ReadModel\User;

class AuthView
{
    public $id;
    public $email;
    public $password_hash;
    public $role;
    public $status;
}
