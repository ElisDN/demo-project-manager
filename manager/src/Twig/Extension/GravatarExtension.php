<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GravatarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('gravatar', [$this, 'gravatar'], ['is_safe' => ['html']]),
        ];
    }

    public function gravatar(string $email, int $size): string
    {
        return '//www.gravatar.com/avatar/'. md5($email) . '?' . http_build_query([
            's' => $size,
            'd' => 'identicon',
        ]);
    }
}
