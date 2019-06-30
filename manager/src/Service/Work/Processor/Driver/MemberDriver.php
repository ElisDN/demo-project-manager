<?php

declare(strict_types=1);

namespace App\Service\Work\Processor\Driver;

use App\ReadModel\Work\Members\Member\MemberFetcher;
use Twig\Environment;

class MemberDriver implements Driver
{
    private const PATTERN = '/\@[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i';

    private $members;
    private $twig;

    public function __construct(MemberFetcher $members, Environment $twig)
    {
        $this->members = $members;
        $this->twig = $twig;
    }

    public function process(string $text): string
    {
        return preg_replace_callback(self::PATTERN, function (array $matches) {
            $id = ltrim($matches[0], '@');
            if (!$member = $this->members->find($id)) {
                return $matches[0];
            }
            return $this->twig->render('processor/work/member.html.twig', [
                'member' => $member,
            ]);
        }, $text);
    }
}
