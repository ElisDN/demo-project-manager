<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects;

use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\UseCase\Projects\Task\ChildOf;
use App\Model\Work\UseCase\Projects\Task\Edit;
use App\Model\Work\UseCase\Projects\Task\Executor;
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
use App\ReadModel\Work\Projects\Task\Filter;
use App\ReadModel\Work\Projects\Task\TaskFetcher;
use App\Security\Voter\Work\Projects\TaskAccess;
use App\Controller\ErrorHandler;
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
            $request->query->get('sort', 't.id'),
            $request->query->get('direction', 'desc')
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
            $request->query->get('sort', 't.id'),
            $request->query->get('direction', 'desc')
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
            $request->query->get('sort', 't.id'),
            $request->query->get('direction', 'desc')
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

        $command = Edit\Command::fromTask($task);

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
     * @Route("/{id}/child", name=".child")
     * @param Task $task
     * @param Request $request
     * @param ChildOf\Handler $handler
     * @return Response
     */
    public function childOf(Task $task, Request $request, ChildOf\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = ChildOf\Command::fromTask($task);

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

        $command = new Executor\Assign\Command($task->getId()->getValue());

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

        $command = new Executor\Revoke\Command($task->getId()->getValue(), $member->getId()->getValue());

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

        $command = new Take\Command($task->getId()->getValue(), $this->getUser()->getId());

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

        $command = new TakeAndStart\Command($task->getId()->getValue(), $this->getUser()->getId());

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

        $command = new Start\Command($task->getId()->getValue());

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

        $command = Move\Command::fromTask($task);

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

        $command = Plan\Set\Command::fromTask($task);

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

        $command = new Plan\Remove\Command($task->getId()->getValue());

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
     * @param Status\Handler $statusHandler
     * @param Progress\Handler $progressHandler
     * @param Type\Handler $typeHandler
     * @param Priority\Handler $priorityHandler
     * @return Response
     */
    public function show(
        Task $task,
        Request $request,
        MemberFetcher $members,
        TaskFetcher $tasks,
        Status\Handler $statusHandler,
        Progress\Handler $progressHandler,
        Type\Handler $typeHandler,
        Priority\Handler $priorityHandler
    ): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::VIEW, $task);

        if (!$member = $members->find($this->getUser()->getId())) {
            throw $this->createAccessDeniedException();
        }

        $statusCommand = Status\Command::fromTask($task);
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

        $progressCommand = Progress\Command::fromTask($task);
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

        $typeCommand = Type\Command::fromTask($task);
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

        $priorityCommand = Priority\Command::fromTask($task);
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

        return $this->render('app/work/projects/tasks/show.html.twig', [
            'project' => $task->getProject(),
            'task' => $task,
            'member' => $member,
            'children' => $tasks->childrenOf($task->getId()->getValue()),
            'statusForm' => $statusForm->createView(),
            'progressForm' => $progressForm->createView(),
            'typeForm' => $typeForm->createView(),
            'priorityForm' => $priorityForm->createView(),
        ]);
    }
}
