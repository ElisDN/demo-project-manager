<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Project\Settings;

use App\Annotation\Guid;
use App\Model\Work\Entity\Members\Member\Id;
use App\Model\Work\Entity\Projects\Project\Project;
use App\Model\Work\UseCase\Projects\Project\Membership;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/{project_id}/settings/members", name="work.projects.project.settings.members")
 * @ParamConverter("project", options={"id" = "project_id"})
 * @IsGranted("ROLE_WORK_MANAGE_PROJECTS")
 */
class MembersController extends AbstractController
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
        return $this->render('app/work/projects/project/settings/members/index.html.twig', [
            'project' => $project,
            'memberships' => $project->getMemberships(),
        ]);
    }

    /**
     * @Route("/assign", name=".assign")
     * @param Project $project
     * @param Request $request
     * @param Membership\Add\Handler $handler
     * @return Response
     */
    public function assign(Project $project, Request $request, Membership\Add\Handler $handler): Response
    {
        if (!$project->getDepartments()) {
            $this->addFlash('error', 'Add departments before adding members.');
            return $this->redirectToRoute('work.projects.project.settings.members', ['project_id' => $project->getId()]);
        }

        $command = new Membership\Add\Command($project->getId()->getValue());

        $form = $this->createForm(Membership\Add\Form::class, $command, ['project' => $project->getId()->getValue()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.project.settings.members', ['project_id' => $project->getId()]);
            } catch (\DomainException $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/project/settings/members/assign.html.twig', [
            'project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{member_id}/edit", name=".edit")
     * @param Project $project
     * @param string $member_id
     * @param Request $request
     * @param Membership\Edit\Handler $handler
     * @return Response
     */
    public function edit(Project $project, string $member_id, Request $request, Membership\Edit\Handler $handler): Response
    {
        $membership = $project->getMembership(new Id($member_id));

        $command = Membership\Edit\Command::fromMembership($project, $membership);

        $form = $this->createForm(Membership\Edit\Form::class, $command, ['project' => $project->getId()->getValue()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.projects.project.settings.members', ['project_id' => $project->getId()]);
            } catch (\DomainException $e) {
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/projects/project/settings/members/edit.html.twig', [
            'project' => $project,
            'membership' => $membership,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{member_id}/revoke", name=".revoke", methods={"POST"})
     * @param Project $project
     * @param string $member_id
     * @param Request $request
     * @param Membership\Remove\Handler $handler
     * @return Response
     */
    public function revoke(Project $project, string $member_id, Request $request, Membership\Remove\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('revoke', $request->request->get('token'))) {
            return $this->redirectToRoute('work.projects.project.settings.departments', ['project_id' => $project->getId()]);
        }

        $command = new Membership\Remove\Command($project->getId()->getValue(), $member_id);

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->logger->warning($e->getMessage(), ['exception' => $e]);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.projects.project.settings.members', ['project_id' => $project->getId()]);
    }

    /**
     * @Route("/{member_id}", name=".show", requirements={"member_id"=Guid::PATTERN}))
     * @param Project $project
     * @return Response
     */
    public function show(Project $project): Response
    {
        return $this->redirectToRoute('work.projects.project.settings.members', ['project_id' => $project->getId()]);
    }
}
