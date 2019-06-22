<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects;

use App\Model\Comment\UseCase\Comment;
use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\UseCase\Projects\Task\ChildOf;
use App\Model\Work\UseCase\Projects\Task\Edit;
use App\Model\Work\UseCase\Projects\Task\Executor;
use App\Model\Work\UseCase\Projects\Task\Files;
use App\Model\Work\UseCase\Projects\Task\Move;
use App\Model\Work\UseCase\Projects\Task\Plan;
use App\Model\Work\UseCase\Projects\Task\Priority;
use App\Model\Work\UseCase\Projects\Task\Progress;
use App\Model\Work\UseCase\Projects\Task\Remove;
use App\Model\Work\UseCase\Projects\Task\Start;
use App\Model\Work\UseCase\Projects\Task\Status;
use App\Model\Work\UseCase\Projects\Task\Take;
use App\Model\Work\UseCase\Projects\Task\TakeAndStart;
use App\Model\Work\UseCase\Projects\Task\Type;
use App\ReadModel\Work\Members\Member\MemberFetcher;
use App\ReadModel\Work\Projects\Action\ActionFetcher;
use App\ReadModel\Work\Projects\Action\Feed\Feed;
use App\ReadModel\Work\Projects\Task\CommentFetcher;
use App\ReadModel\Work\Projects\Task\Filter;
use App\ReadModel\Work\Projects\Task\TaskFetcher;
use App\Security\Voter\Work\Projects\TaskAccess;
use App\Controller\ErrorHandler;
use App\Service\Uploader\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/tasks", name="work.projects.tasks")
 * @param Request $request
 * @param TaskFetcher $tasks
 * @return Response
 */
class TasksController extends AbstractController
{
    private const PER_PAGE = 50;

    private $errors;

    public function __construct(ErrorHandler $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @Route("", name="")
     * @ParamConverter("project", options={"id" = "project_id"})
     * @param Request $request
     * @param TaskFetcher $tasks
     * @return Response
     */
    public function index(Request $request, TaskFetcher $tasks): Response
    {
        if ($this->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $filter = Filter\Filter::all();
        } else {
            $filter = Filter\Filter::all()->forMember($this->getUser()->getId());
        }

        $form = $this->createForm(Filter\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $tasks->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => null,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/me", name=".me")
     * @ParamConverter("project", options={"id" = "project_id"})
     * @param Request $request
     * @param TaskFetcher $tasks
     * @return Response
     */
    public function me(Request $request, TaskFetcher $tasks): Response
    {
        $filter = Filter\Filter::all();

        $form = $this->createForm(Filter\Form::class, $filter, [
            'action' => $this->generateUrl('work.projects.tasks'),
        ]);

        $form->handleRequest($request);

        $pagination = $tasks->all(
            $filter->forExecutor($this->getUser()->getId()),
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => null,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/own", name=".own")
     * @ParamConverter("project", options={"id" = "project_id"})
     * @param Request $request
     * @param TaskFetcher $tasks
     * @return Response
     */
    public function own(Request $request, TaskFetcher $tasks): Response
    {
        $filter = Filter\Filter::all();

        $form = $this->createForm(Filter\Form::class, $filter, [
             'action' => $this->generateUrl('work.projects.tasks'),
        ]);

        $form->handleRequest($request);

        $pagination = $tasks->all(
            $filter->forAuthor($this->getUser()->getId()),
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => null,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Task $task
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Task $task, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = Edit\Command::fromTask($this->getUser()->getId(), $task);

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

        return $this->render('app/work/projects/tasks/edit.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/files", name=".files")
     * @param Task $task
     * @param Request $request
     * @param Files\Add\Handler $handler
     * @param FileUploader $uploader
     * @return Response
     */
    public function files(Task $task, Request $request, Files\Add\Handler $handler, FileUploader $uploader): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Files\Add\Command($this->getUser()->getId(), $task->getId()->getValue());

        $form = $this->createForm(Files\Add\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $files = [];
            foreach ($form->get('files')->getData() as $file) {
                $uploaded = $uploader->upload($file);
                $files[] = new Files\Add\File(
                    $uploaded->getPath(),
                    $uploaded->getName(),
                    $uploaded->getSize()
                );
            }
            $command->files = $files;
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/tasks/files.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/files/{file_id}/delete", name=".files.delete", methods={"POST"})
     * @ParamConverter("member", options={"id" = "member_id"})
     * @param Task $task
     * @param string $file_id
     * @param Request $request
     * @param Files\Remove\Handler $handler
     * @return Response
     */
    public function fileDelete(Task $task, string $file_id, Request $request, Files\Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('delete-file', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Files\Remove\Command($this->getUser()->getId(), $task->getId()->getValue(), $file_id);

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/child", name=".child")
     * @param Task $task
     * @param Request $request
     * @param ChildOf\Handler $handler
     * @return Response
     */
    public function childOf(Task $task, Request $request, ChildOf\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = ChildOf\Command::fromTask($this->getUser()->getId(), $task);

        $form = $this->createForm(ChildOf\Form::class, $command);
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

        return $this->render('app/work/projects/tasks/child.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/assign", name=".assign")
     * @param Task $task
     * @param Request $request
     * @param Executor\Assign\Handler $handler
     * @return Response
     */
    public function assign(Task $task, Request $request, Executor\Assign\Handler $handler): Response
    {
        $project = $task->getProject();
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Executor\Assign\Command($this->getUser()->getId(), $task->getId()->getValue());

        $form = $this->createForm(Executor\Assign\Form::class, $command, ['project_id' => $project->getId()->getValue()]);
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

        return $this->render('app/work/projects/tasks/assign.html.twig', [
            'project' => $project,
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/revoke/{member_id}", name=".revoke", methods={"POST"})
     * @ParamConverter("member", options={"id" = "member_id"})
     * @param Task $task
     * @param Member $member
     * @param Request $request
     * @param Executor\Revoke\Handler $handler
     * @return Response
     */
    public function revoke(Task $task, Member $member, Request $request, Executor\Revoke\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('revoke', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Executor\Revoke\Command(
            $this->getUser()->getId(),
            $task->getId()->getValue(),
            $member->getId()->getValue()
        );

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/take", name=".take", methods={"POST"})
     * @param Task $task
     * @param Request $request
     * @param Take\Handler $handler
     * @return Response
     */
    public function take(Task $task, Request $request, Take\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('take', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Take\Command($this->getUser()->getId(), $task->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/take/start", name=".take_and_start", methods={"POST"})
     * @param Task $task
     * @param Request $request
     * @param TakeAndStart\Handler $handler
     * @return Response
     */
    public function takeAndStart(Task $task, Request $request, TakeAndStart\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('take-and-start', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new TakeAndStart\Command($this->getUser()->getId(), $task->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/start", name=".start", methods={"POST"})
     * @param Task $task
     * @param Request $request
     * @param Start\Handler $handler
     * @return Response
     */
    public function start(Task $task, Request $request, Start\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('start', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Start\Command($this->getUser()->getId(), $task->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/move", name=".move")
     * @param Task $task
     * @param Request $request
     * @param Move\Handler $handler
     * @return Response
     */
    public function move(Task $task, Request $request, Move\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = Move\Command::fromTask($this->getUser()->getId(), $task);

        $form = $this->createForm(Move\Form::class, $command);
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

        return $this->render('app/work/projects/tasks/move.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/plan", name=".plan")
     * @param Task $task
     * @param Request $request
     * @param Plan\Set\Handler $handler
     * @return Response
     */
    public function plan(Task $task, Request $request, Plan\Set\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = Plan\Set\Command::fromTask($this->getUser()->getId(), $task);

        $form = $this->createForm(Plan\Set\Form::class, $command);
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

        return $this->render('app/work/projects/tasks/plan.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/plan/remove", name=".plan.remove", methods={"POST"})
     * @param Task $task
     * @param Request $request
     * @param Plan\Remove\Handler $handler
     * @return Response
     */
    public function removePlan(Task $task, Request $request, Plan\Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('remove-plan', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Plan\Remove\Command($this->getUser()->getId(), $task->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
    }

    /**
     * @Route("/{id}/delete", name=".delete", methods={"POST"})
     * @param Task $task
     * @param Request $request
     * @param Remove\Handler $handler
     * @return Response
     */
    public function delete(Task $task, Request $request, Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
        }

        $this->denyAccessUnlessGranted(TaskAccess::DELETE, $task);

        $command = new Remove\Command($task->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.tasks');
    }

    /**
     * @Route("/{id}", name=".show", requirements={"id"="\d+"}))
     * @param Task $task
     * @param Request $request
     * @param MemberFetcher $members
     * @param TaskFetcher $tasks
     * @param CommentFetcher $comments
     * @param ActionFetcher $actions
     * @param Status\Handler $statusHandler
     * @param Progress\Handler $progressHandler
     * @param Type\Handler $typeHandler
     * @param Priority\Handler $priorityHandler
     * @param Comment\Create\Handler $commentHandler
     * @return Response
     */
    public function show(
        Task $task,
        Request $request,
        MemberFetcher $members,
        TaskFetcher $tasks,
        CommentFetcher $comments,
        ActionFetcher $actions,
        Status\Handler $statusHandler,
        Progress\Handler $progressHandler,
        Type\Handler $typeHandler,
        Priority\Handler $priorityHandler,
        Comment\Create\Handler $commentHandler
    ): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::VIEW, $task);

        if (!$member = $members->find($this->getUser()->getId())) {
            throw $this->createAccessDeniedException();
        }

        $statusCommand = Status\Command::fromTask($this->getUser()->getId(), $task);
        $statusForm = $this->createForm(Status\Form::class, $statusCommand);
        $statusForm->handleRequest($request);
        if ($statusForm->isSubmitted() && $statusForm->isValid()) {
            try {
                $statusHandler->handle($statusCommand);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $progressCommand = Progress\Command::fromTask($this->getUser()->getId(), $task);
        $progressForm = $this->createForm(Progress\Form::class, $progressCommand);
        $progressForm->handleRequest($request);
        if ($progressForm->isSubmitted() && $progressForm->isValid()) {
            try {
                $progressHandler->handle($progressCommand);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $typeCommand = Type\Command::fromTask($member->getId()->getValue(), $task);
        $typeForm = $this->createForm(Type\Form::class, $typeCommand);
        $typeForm->handleRequest($request);
        if ($typeForm->isSubmitted() && $typeForm->isValid()) {
            try {
                $typeHandler->handle($typeCommand);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $priorityCommand = Priority\Command::fromTask($this->getUser()->getId(), $task);
        $priorityForm = $this->createForm(Priority\Form::class, $priorityCommand);
        $priorityForm->handleRequest($request);
        if ($priorityForm->isSubmitted() && $priorityForm->isValid()) {
            try {
                $priorityHandler->handle($priorityCommand);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $commentCommand = new Comment\Create\Command(
            $this->getUser()->getId(),
            Task::class,
            (string)$task->getId()->getValue()
        );

        $commentForm = $this->createForm(Comment\Create\Form::class, $commentCommand);
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            try {
                $commentHandler->handle($commentCommand);
                return $this->redirectToRoute('work.projects.tasks.show', ['id' => $task->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        $feed = new Feed(
            $actions->allForTask($task->getId()->getValue()),
            $comments->allForTask($task->getId()->getValue())
        );

        return $this->render('app/work/projects/tasks/show.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'member' => $member,
            'children' => $tasks->childrenOf($task->getId()->getValue()),
            'feed' => $feed,
            'statusForm' => $statusForm->createView(),
            'progressForm' => $progressForm->createView(),
            'typeForm' => $typeForm->createView(),
            'priorityForm' => $priorityForm->createView(),
            'commentForm' => $commentForm->createView(),
        ]);
    }
}
