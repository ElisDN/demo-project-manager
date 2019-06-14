<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Project;

use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\UseCase\Projects\Task\Create;
use App\Model\Work\UseCase\Projects\Task\Executor;
use App\Model\Work\UseCase\Projects\Task\Plan;
use App\ReadModel\Work\Projects\Task\Filter;
use App\ReadModel\Work\Projects\Task\TaskFetcher;
use App\Security\Voter\Work\Projects\ProjectAccess;
use App\Controller\ErrorHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/{project_id}/tasks", name="work.projects.project.tasks")
 * @ParamConverter("project", options={"id" = "project_id"})
 */
class TasksController extends AbstractController
{
    private const PER_PAGE = 50;

    private $tasks;
    private $errors;

    public function __construct(TaskFetcher $tasks, ErrorHandler $errors)
    {
        $this->tasks = $tasks;
        $this->errors = $errors;
    }

    /**
     * @Route("", name="")
     * @param Project $project
     * @param Request $request
     * @return Response
     */
    public function index(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProjectAccess::VIEW, $project);

        $filter = Filter\Filter::forProject($project->getId()->getValue());

        $form = $this->createForm(Filter\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $this->tasks->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/me", name=".me")
     * @param Project $project
     * @param Request $request
     * @return Response
     */
    public function me(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProjectAccess::VIEW, $project);

        $filter = Filter\Filter::forProject($project->getId()->getValue());

        $form = $this->createForm(Filter\Form::class, $filter, [
            'action' => $this->generateUrl('work.projects.project.tasks', ['project_id' => $project->getId()]),
        ]);
        $form->handleRequest($request);

        $pagination = $this->tasks->all(
            $filter->forExecutor($this->getUser()->getId()),
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/own", name=".own")
     * @param Project $project
     * @param Request $request
     * @return Response
     */
    public function own(Project $project, Request $request): Response
    {
        $this->denyAccessUnlessGranted(ProjectAccess::VIEW, $project);

        $filter = Filter\Filter::forProject($project->getId()->getValue());

        $form = $this->createForm(Filter\Form::class, $filter, [
            'action' => $this->generateUrl('work.projects.project.tasks', ['project_id' => $project->getId()]),
        ]);
        $form->handleRequest($request);

        $pagination = $this->tasks->all(
            $filter->forAuthor($this->getUser()->getId()),
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->render('app/work/projects/tasks/index.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Project $project
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Project $project, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ProjectAccess::VIEW, $project);

        $command = new Create\Command(
            $project->getId()->getValue(),
            $this->getUser()->getId()
        );

        if ($parent = $request->query->get('parent')) {
            $command->parent = $parent;
        }

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.project.tasks', ['project_id' => $project->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/project/tasks/create.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }
}
