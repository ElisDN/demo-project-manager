<?php

declare(strict_types=1);

namespace App\Controller\Work\Members;

use App\Annotation\Guid;
use App\Model\User\Entity\User\User;
use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\UseCase\Members\Member\Archive;
use App\Model\Work\UseCase\Members\Member\Edit;
use App\Model\Work\UseCase\Members\Member\Reinstate;
use App\Model\Work\UseCase\Members\Member\Create;
use App\Model\Work\UseCase\Members\Member\Move;
use App\ReadModel\Work\Members\Member\Filter;
use App\ReadModel\Work\Members\Member\MemberFetcher;
use App\ReadModel\Work\Projects\Project\DepartmentFetcher;
use App\Controller\ErrorHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/members", name="work.members")
 * @IsGranted("ROLE_WORK_MANAGE_MEMBERS")
 */
class MembersController extends AbstractController
{
    private const PER_PAGE = 50;

    private $errors;

    public function __construct(ErrorHandler $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @Route("", name="")
     * @param Request $request
     * @param MemberFetcher $fetcher
     * @return Response
     */
    public function index(Request $request, MemberFetcher $fetcher): Response
    {
        $filter = new Filter\Filter();

        $form = $this->createForm(Filter\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort', 'name'),
            $request->query->get('direction', 'asc')
        );

        return $this->render('app/work/members/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create/{id}", name=".create")
     * @param User $user
     * @param Request $request
     * @param MemberFetcher $members
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(User $user, Request $request, MemberFetcher $members, Create\Handler $handler): Response
    {
        if ($members->exists($user->getId()->getValue())) {
            $this->addFlash('error', 'Member already exists.');
            return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
        }

        $command = new Create\Command($user->getId()->getValue());
        $command->firstName = $user->getName()->getFirst();
        $command->lastName = $user->getName()->getLast();
        $command->email = $user->getEmail() ? $user->getEmail()->getValue() : null;

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.members.show', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/members/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Member $member
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Member $member, Request $request, Edit\Handler $handler): Response
    {
        $command = Edit\Command::fromMember($member);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/members/edit.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/move", name=".move")
     * @param Member $member
     * @param Request $request
     * @param Move\Handler $handler
     * @return Response
     */
    public function move(Member $member, Request $request, Move\Handler $handler): Response
    {
        $command = Move\Command::fromMember($member);

        $form = $this->createForm(Move\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
            } catch (\DomainException $e) {
                $this->errors->handle($e);
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/work/members/move.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/archive", name=".archive", methods={"POST"})
     * @param Member $member
     * @param Request $request
     * @param Archive\Handler $handler
     * @return Response
     */
    public function archive(Member $member, Request $request, Archive\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('archive', $request->request->get('token'))) {
            return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
        }

        $command = new Archive\Command($member->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
    }

    /**
     * @Route("/{id}/reinstate", name=".reinstate", methods={"POST"})
     * @param Member $member
     * @param Request $request
     * @param Reinstate\Handler $handler
     * @return Response
     */
    public function reinstate(Member $member, Request $request, Reinstate\Handler $handler): Response
    {
        if (!$this->isCsrfTokenValid('reinstate', $request->request->get('token'))) {
            return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
        }

        if ($member->getId()->getValue() === $this->getUser()->getId()) {
            $this->addFlash('error', 'Unable to reinstate yourself.');
            return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
        }

        $command = new Reinstate\Command($member->getId()->getValue());

        try {
            $handler->handle($command);
        } catch (\DomainException $e) {
            $this->errors->handle($e);
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('work.members.show', ['id' => $member->getId()]);
    }

    /**
     * @Route("/{id}", name=".show", requirements={"id"=Guid::PATTERN})
     * @param Member $member
     * @param DepartmentFetcher $fetcher
     * @return Response
     */
    public function show(Member $member, DepartmentFetcher $fetcher): Response
    {
        $departments = $fetcher->allOfMember($member->getId()->getValue());

        return $this->render('app/work/members/show.html.twig', compact('member', 'departments'));
    }
}
