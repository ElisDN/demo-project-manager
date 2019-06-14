<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Task;

use App\Controller\ErrorHandler;
use App\Model\Comment\Entity\Comment\Comment;
use App\Model\Comment\UseCase\Comment\Edit;
use App\Model\Comment\UseCase\Comment\Remove;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Security\Voter\Comment\CommentAccess;
use App\Security\Voter\Work\Projects\TaskAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/tasks/{task_id}/comments", name="work.projects.tasks.comments")
 * @ParamConverter("task", options={"id" = "task_id"})
 */
class CommentController extends AbstractController
{
    private $errors;

    public function __construct(ErrorHandler $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Task $task
     * @param Comment $comment
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Task $task, Comment $comment, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::VIEW, $task);
        $this->checkCommentIsForTask($task, $comment);
        $this->denyAccessUnlessGranted(CommentAccess::MANAGE, $comment);

        $command = Edit\Command::fromComment($comment);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/tasks/comment/edit.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete", methods={"POST"})
     * @param Task $task
     * @param Comment $comment
     * @param Request $request
     * @param Remove\Handler $handler
     * @return Response
     */
    public function delete(Task $task, Comment $comment, Request $request, Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('delete-comment', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::VIEW, $task);
        $this->checkCommentIsForTask($task, $comment);
        $this->denyAccessUnlessGranted(CommentAccess::MANAGE, $comment);

        $command = new Remove\Command($comment->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    private function checkCommentIsForTask(Task $task, Comment $comment): void
    {
        if (!(
            $comment->getEntity()->getType() === Task::class &&
            (int)$comment->getEntity()->getId() === $task->getId()->getValue()
        )) {
            throw $this->createNotFoundException();
        }
    }
}
