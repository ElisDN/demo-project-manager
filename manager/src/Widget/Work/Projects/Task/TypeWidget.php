<?php

declare(strict_types=1);

namespace App\Widget\Work\Projects\Task;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TypeWidget extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('work_projects_task_type', [$this, 'type'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function type(Environment $twig, string $type): string
    {
        return $twig->render('widget/work/projects/task/type.html.twig', [
            'type' => $type
        ]);
    }
}
