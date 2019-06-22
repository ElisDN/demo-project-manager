<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Project;

use App\Model\Work\Entity\Projects\Project\Project;
use App\ReadModel\Work\Projects\Action\ActionFetcher;
use App\ReadModel\Work\Projects\Action\Filter;
use App\Security\Voter\Work\Projects\ProjectAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/{project_id}/actions", name="work.projects.project.actions")
 * @ParamConverter("project", options={"id" = "project_id"})
 */
class ActionsController extends AbstractController
{
    private const PER_PAGE = 50;

    private $actions;

    public function __construct(ActionFetcher $actions)
    {
        $this->actions = $actions;
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

        $filter = Filter::forProject($project->getId()->getValue());

        $pagination = $this->actions->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE
        );

        return $this->render('app/work/projects/actions.html.twig', [
            'project' => $project,
            'pagination' => $pagination,
        ]);
    }
}
