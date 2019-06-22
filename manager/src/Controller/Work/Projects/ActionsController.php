<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects;

use App\ReadModel\Work\Projects\Action\ActionFetcher;
use App\ReadModel\Work\Projects\Action\Filter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/actions", name="work.projects.actions")
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
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        if ($this->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $filter = Filter::all();
        } else {
            $filter = Filter::all()->forMember($this->getUser()->getId());
        }

        $pagination = $this->actions->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE
        );

        return $this->render('app/work/projects/actions.html.twig', [
            'project' => null,
            'pagination' => $pagination,
        ]);
    }
}
