<?php

declare(strict_types=1);

namespace App\Service;

class Gravatar
{
    public static function url(string $email, int $size): string
    {
        return 'https://www.gravatar.com/avatar/'. md5($email) . '?' . http_build_query([
            's' => $size,
            'd' => 'identicon',
        ]);
    }
}
