<?php

declare(strict_types=1);

namespace App\Widget\Work\Projects;

use App\ReadModel\Work\Projects\Task\TaskFetcher;
use App\Security\UserIdentity;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class OwnTasksWidget extends AbstractExtension
{
    private const LIMIT = 10;

    private $tasks;
    private $tokens;

    public function __construct(TaskFetcher $tasks, TokenStorageInterface $tokens)
    {
        $this->tasks = $tasks;
        $this->tokens = $tokens;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('work_projects_own_tasks', [$this, 'tasks'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function tasks(Environment $twig): string
    {
        if (null === $token = $this->tokens->getToken()) {
            return '';
        }

        if (!($user = $token->getUser()) instanceof UserIdentity) {
            return '';
        }

        $tasks = $this->tasks->lastOwn($user->getId(), self::LIMIT);

        return $twig->render('widget/work/projects/own-tasks.html.twig', compact('tasks'));
    }
}