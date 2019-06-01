<?php

declare(strict_types=1);

namespace App\Widget\Work\Projects\Task;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProgressWidget extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('work_projects_task_progress', [$this, 'progress'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function progress(Environment $twig, int $progress): string
    {
        return $twig->render('widget/work/projects/task/progress.html.twig', [
            'progress' => $progress
        ]);
    }
}
