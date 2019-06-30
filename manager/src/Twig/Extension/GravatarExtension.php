<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Service\Gravatar;
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
        return Gravatar::url($email, $size);
    }
}
