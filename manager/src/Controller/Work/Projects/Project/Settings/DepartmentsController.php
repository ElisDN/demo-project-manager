<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Project\Settings;

use App\Annotation\Guid;
use App\Model\Work\Entity\Projects\Project\Department\Id;
use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\UseCase\Projects\Project\Department\Create;
use App\Model\Work\UseCase\Projects\Project\Department\Edit;
use App\Model\Work\UseCase\Projects\Project\Department\Remove;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/{project_id}/settings/departments", name="work.projects.project.settings.departments")
 * @ParamConverter("project", options={"id" = "project_id"})
 * @IsGranted("ROLE_WORK_MANAGE_PROJECTS")
 */
class DepartmentsController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("", name="")
     * @param Project $project
     * @return Response
     */
    public function index(Project $project): Response
    {
        return $this->render('app/work/projects/project/settings/departments/index.html.twig', [
            'project' => $project,
            'departments' => $project->getDepartments(),
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
        $command = new Create\Command($project->getId()->getValue());

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.project.settings.departments', ['project_id' => $project->getId()]);
            } catch (\DomainException $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/project/settings/departments/create.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Project $project
     * @param string $id
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Project $project, string $id, Request $request, Edit\Handler $handler): Response
    {
        $department = $project->getDepartment(new Id($id));

        $command = Edit\Command::fromDepartment($project, $department);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.project.settings.departments.show', ['project_id' => $project->getId(), 'id' => $id]);
            } catch (\DomainException $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/project/settings/departments/edit.html.twig', [
            'project' => $project,
            'department' => $department,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete", methods={"POST"})
     * @param Project $project
     * @param string $id
     * @param Request $request
     * @param Remove\Handler $handler
     * @return Response
     */
    public function delete(Project $project, string $id, Request $request, Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.project.settings.departments', ['project_id' => $project->getId()]);
        }

        $department = $project->getDepartment(new Id($id));

        $command = new Remove\Command($project->getId()->getValue(), $department->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.project.settings.departments', ['project_id' => $project->getId()]);
    }

    /**
     * @Route("/{id}", name=".show", requirements={"id"=Guid::PATTERN}))
     * @param Project $project
     * @return Response
     */
    public function show(Project $project): Response
    {
        return $this->redirectToRoute('work.projects.project.settings.departments', ['project_id' => $project->getId()]);
    }
}
