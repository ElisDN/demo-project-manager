<?php

declare(strict_types=1);

namespace App\Event\Listener\Work\Projects\Task;

use App\Model\Work\Entity\Members\Member\MemberRepository;
use App\Model\Work\Entity\Projects\Task\Event\TaskExecutorAssigned;
use App\Model\Work\Entity\Projects\Task\TaskRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class EmailNotificationSubscriber implements EventSubscriberInterface
{
    private $tasks;
    private $members;
    private $mailer;
    private $twig;

    public function __construct(TaskRepository $tasks, MemberRepository $members, \Swift_Mailer $mailer, Environment $twig)
    {
        $this->tasks = $tasks;
        $this->members = $members;
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TaskExecutorAssigned::class => [
                ['onTaskExecutorAssignedExecutor'],
                ['onTaskExecutorAssignedAuthor']
            ],
        ];
    }

    public function onTaskExecutorAssignedExecutor(TaskExecutorAssigned $event): void
    {
        if ($event->executorId->isEqual($event->actorId)) {
            return;
        }

        $task = $this->tasks->get($event->taskId);
        $executor = $this->members->get($event->executorId);
        $author = $task->getAuthor();

        if ($executor === $author) {
            return;
        }

        $message = (new \Swift_Message('Task Executor Assignment'))
            ->setTo([$executor->getEmail()->getValue() => $executor->getName()->getFull()])
            ->setBody($this->twig->render('mail/work/projects/task/executor-assigned-executor.html.twig', [
                'task' => $task,
                'executor' => $executor,
            ]), 'text/html');

        if (!$this->mailer->send($message)) {
            throw new \RuntimeException('Unable to send message.');
        }
    }

    public function onTaskExecutorAssignedAuthor(TaskExecutorAssigned $event): void
    {
        $task = $this->tasks->get($event->taskId);
        $executor = $this->members->get($event->executorId);
        $author = $task->getAuthor();

        if ($executor === $author) {
            return;
        }

        $message = (new \Swift_Message('Your Task Executor Assignment'))
            ->setTo([$author->getEmail()->getValue() => $author->getName()->getFull()])
            ->setBody($this->twig->render('mail/work/projects/task/executor-assigned-author.html.twig', [
                'task' => $task,
                'author' => $author,
                'executor' => $executor,
            ]), 'text/html');

        if (!$this->mailer->send($message)) {
            throw new \RuntimeException('Unable to send message.');
        }
    }
}
