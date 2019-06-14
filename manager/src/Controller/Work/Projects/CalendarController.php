<?php

declare(strict_types=1);

namespace App\Controller\Work\Projects;

use App\ReadModel\Work\Projects\Calendar\CalendarFetcher;
use App\ReadModel\Work\Projects\Calendar\Query;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController
{
    /**
     * @Route("/work/projects/calendar", name="work.projects.calendar")
     * @param Request $request
     * @param CalendarFetcher $calendar
     * @return Response
     * @throws \Exception
     */
    public function show(Request $request, CalendarFetcher $calendar): Response
    {
        $now = new \DateTimeImmutable();

        if ($this->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $query = Query\Query::fromDate($now);
        } else {
            $query = Query\Query::fromDate($now)->forMember($this->getUser()->getId());
        }

        $form = $this->createForm(Query\Form::class, $query);
        $form->handleRequest($request);

        $result = $calendar->byMonth($query);

        return $this->render('app/work/projects/calendar.html.twig', [
            'project' => null,
            'dates' => iterator_to_array(new \DatePeriod($result->start, new \DateInterval('P1D'), $result->end)),
            'now' => $now,
            'result' => $result,
            'next' => $result->month->modify('+1 month'),
            'prev' => $result->month->modify('-1 month'),
            'form' => $form->createView(),
        ]);
    }
}
