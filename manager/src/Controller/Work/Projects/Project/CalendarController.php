<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects\Project;

use App\Model\Work\Entity\Projects\Project\Project;
use App\ReadModel\Work\Projects\Calendar\CalendarFetcher;
use App\ReadModel\Work\Projects\Calendar\Query;
use App\Security\Voter\Work\Projects\ProjectAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    /**
     * @Route("/work/projects/{project_id}/calendar", name="work.projects.project.calendar")
     * @ParamConverter("project", options={"id" = "project_id"})
     * @param Project $project
     * @param Request $request
     * @param CalendarFetcher $calendar
     * @return Response
     * @throws \Exception
     */
    public function show(Project $project, Request $request, CalendarFetcher $calendar): Response
    {
        $this->denyAccessUnlessGranted(ProjectAccess::VIEW, $project);

        $now = new \DateTimeImmutable();

        $query = Query\Query::fromDate($now)->forProject($project->getId()->getValue());

        $form = $this->createForm(Query\Form::class, $query);
        $form->handleRequest($request);

        $result = $calendar->byMonth($query);

        return $this->render('app/work/projects/calendar.html.twig', [
            'project' => $project,
            'dates' => iterator_to_array(new \DatePeriod($result->start, new \DateInterval('P1D'), $result->end)),
            'now' => $now,
            'result' => $result,
            'next' => $result->month->modify('+1 month'),
            'prev' => $result->month->modify('-1 month'),
            'form' => $form->createView(),
        ]);
    }
}
