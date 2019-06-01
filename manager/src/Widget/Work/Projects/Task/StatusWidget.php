<?php

declare(strict_types=1);

namespace App\Widget\Work\Projects\Task;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StatusWidget extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('work_projects_task_status', [$this, 'status'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function status(Environment $twig, string $status): string
    {
        return $twig->render('widget/work/projects/task/status.html.twig', [
            'status' => $status
        ]);
    }
}
