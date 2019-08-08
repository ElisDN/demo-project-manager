<?php

declare(strict_types=1);

namespace App\Controller\Api\Work\Projects;

use App\Controller\Api\PaginationSerializer;
use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Task\File\File;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\UseCase\Projects\Task\Plan;
use App\ReadModel\Work\Projects\Action\ActionFetcher;
use App\ReadModel\Work\Projects\Action\Feed\Feed;
use App\ReadModel\Work\Projects\Action\Feed\Item;
use App\ReadModel\Work\Projects\Task\CommentFetcher;
use App\ReadModel\Work\Projects\Task\Filter;
use App\ReadModel\Work\Projects\Task\TaskFetcher;
use App\Security\Voter\Work\Projects\TaskAccess;
use App\Service\Gravatar;
use App\Service\Uploader\FileUploader;
use App\Service\Work\Processor\Processor;
use cebe\markdown\MarkdownExtra;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/work/tasks", name="work.projects.tasks")
 */
class TasksController extends AbstractController
{
    private const PER_PAGE = 50;

    private $serializer;
    private $denormalizer;
    private $validator;

    public function __construct(SerializerInterface $serializer, DenormalizerInterface $denormalizer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->denormalizer = $denormalizer;
        $this->validator = $validator;
    }

    /**
     * @Route("", name="", methods={"GET"})
     * @param Request $request
     * @param TaskFetcher $fetcher
     * @return Response
     * @throws ExceptionInterface
     */
    public function index(Request $request, TaskFetcher $fetcher): Response
    {
        if ($this->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $filter = Filter\Filter::all();
        } else {
            $filter = Filter\Filter::all()->forMember($this->getUser()->getId());
        }

        /** @var Filter\Filter $filter */
        $filter = $this->denormalizer->denormalize($request->query->get('filter', []), Filter\Filter::class, 'array', [
            'object_to_populate' => $filter,
            'ignored_attributes' => ['member', 'project'],
        ]);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort'),
            $request->query->get('direction')
        );

        return $this->json([
            'items' => array_map(static function (array $item) {
                return [
                    'id' => $item['id'],
                    'project' => [
                        'id' => $item['project_id'],
                        'name' => $item['project_name'],
                    ],
                    'author' => [
                        'id' => $item['author_id'],
                        'name' => $item['author_name'],
                    ],
                    'date' => $item['date'],
                    'plan_date' => $item['plan_date'],
                    'parent' => $item['parent'],
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'progress' => $item['progress'],
                    'priority' => $item['priority'],
                    'status' => $item['status'],
                    'executors'=> array_map(static function (array $member) {
                        return [
                            'name' => $member['name'],
                        ];
                    }, $item['executors']),
                ];
            }, (array)$pagination->getItems()),
            'pagination' => PaginationSerializer::toArray($pagination),
        ]);
    }

    /**
     * @Route("/{id}/plan", name=".plan", methods={"PUT"})
     * @param Task $task
     * @param Request $request
     * @param Plan\Set\Handler $handler
     * @return Response
     * @throws \Exception
     */
    public function plan(Task $task, Request $request, Plan\Set\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $data = json_decode($request->getContent(), true);

        $command = new Plan\Set\Command($this->getUser()->getId(), $task->getId()->getValue());
        $command->date = !empty($data['date']) ? new \DateTimeImmutable($data['date']) : null;

        $violations = $this->validator->validate($command);
        if (\count($violations)) {
            $json = $this->serializer->serialize($violations, 'json');
            return new JsonResponse($json, 400, [], true);
        }

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @Route("/{id}/plan", name=".plan.delete", methods={"DELETE"})
     * @param Task $task
     * @param Plan\Remove\Handler $handler
     * @return Response
     */
    public function removePlan(Task $task, Plan\Remove\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::MANAGE, $task);

        $command = new Plan\Remove\Command($this->getUser()->getId(), $task->getId()->getValue());
        $handler->handle($command);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name=".show", requirements={"id"="\d+"}))
     * @param Task $task
     * @param CommentFetcher $comments
     * @param FileUploader $uploader
     * @param ActionFetcher $actions
     * @param MarkdownExtra $markdown
     * @param \HTMLPurifier $purifier
     * @param Processor $processor
     * @return Response
     * @throws \Exception
     */
    public function show(
        Task $task,
        CommentFetcher $comments,
        FileUploader $uploader,
        ActionFetcher $actions,
        MarkdownExtra $markdown,
        \HTMLPurifier $purifier,
        Processor $processor
    ): Response
    {
        $this->denyAccessUnlessGranted(TaskAccess::VIEW, $task);

        $feed = new Feed(
            $actions->allForTask($task->getId()->getValue()),
            $comments->allForTask($task->getId()->getValue())
        );

        return $this->json([
            'id' => $task->getId()->getValue(),
            'project' => [
                'id' => $task->getProject()->getId()->getValue(),
                'name' => $task->getProject()->getName(),
            ],
            'author' => [
                'id' => $task->getAuthor()->getId()->getValue(),
                'name' => $task->getAuthor()->getName()->getFull(),
                'avatar' => Gravatar::url($task->getAuthor()->getEmail()->getValue(), 100),
            ],
            'date' => $task->getDate()->format(DATE_ATOM),
            'plan_date' => $task->getPlanDate() ? $task->getPlanDate()->format(DATE_ATOM) : null,
            'start_date' => $task->getStartDate() ? $task->getStartDate()->format(DATE_ATOM) : null,
            'end_date' => $task->getEndDate() ? $task->getEndDate()->format(DATE_ATOM) : null,
            'name' => $task->getName(),
            'content' => $processor->process($purifier->purify($markdown->parse($task->getContent()))),
            'files' => array_map(static function (File $file) use ($uploader) {
                return [
                    'id' => $file->getId()->getValue(),
                    'date' => $file->getDate()->format(DATE_ATOM),
                    'member' => [
                        'id' => $file->getMember()->getId()->getValue(),
                        'name' => $file->getMember()->getName()->getFull(),
                    ],
                    'info' => [
                        'url' => $uploader->generateUrl($file->getInfo()->getPath()),
                        'name' => $file->getInfo()->getName(),
                        'size' => $file->getInfo()->getSize(),
                    ],
                ];
            }, $task->getFiles()),
            'type' => $task->getType()->getName(),
            'progress' => $task->getProgress(),
            'priority' => $task->getPriority(),
            'parent' => $task->getParent() ? [
                'id' => $task->getParent()->getId()->getValue(),
                'name' => $task->getParent()->getName(),
            ] : null,
            'status' => $task->getStatus()->getName(),
            'executors'=> array_map(static function (Member $member) {
                return [
                    'id' => $member->getId()->getValue(),
                    'name' => $member->getName()->getFull(),
                    'avatar' => Gravatar::url($member->getEmail()->getValue(), 100),
                ];
            }, $task->getExecutors()),
            'feed' => array_map(static function (Item $item) use ($markdown, $purifier, $processor) {
                $action = $item->getAction();
                $comment = $item->getComment();
                return [
                    'date' => $item->getDate()->format(DATE_ATOM),
                    'action' => $action ? [
                        'id' => $action['id'],
                        'date' => $action['date'],
                        'actor' => [
                            'id' => $action['actor_id'],
                            'name' => $action['actor_name'],
                        ],
                        'set' => [
                            'project' => $action['set_project_id'] ? [
                                'id' => $action['set_project_id'],
                                'name' => $action['set_project_name'],
                            ] : null,
                            'name' => $action['set_name'],
                            'content' => $action['set_content'],
                            'file' => $action['set_file_id'],
                            'removed_file' => $action['set_removed_file_id'],
                            'parent' => $action['set_parent_id'],
                            'removed_parent' => $action['set_removed_parent'],
                            'type' => $action['set_type'],
                            'status' => $action['set_status'],
                            'progress' => $action['set_progress'],
                            'priority' => $action['set_priority'],
                            'plan' => $action['set_plan'],
                            'removed_plan' => $action['set_removed_plan'],
                            'executor' => $action['set_executor_id'] ? [
                                'id' => $action['set_executor_id'],
                                'name' => $action['set_executor_name'],
                            ] : null,
                            'revoked_executor' => $action['set_revoked_executor_id'] ? [
                                'id' => $action['set_revoked_executor_id'],
                                'name' => $action['set_revoked_executor_name'],
                            ] : null,
                        ],
                    ] : null,
                    'comment' => $comment ? [
                        'id' => $comment['id'],
                        'date' => $comment['date'],
                        'author' => [
                            'id' => $comment['author_id'],
                            'name' => $comment['author_name'],
                            'avatar' => Gravatar::url($comment['author_email'], 100),
                        ],
                        'content' => $processor->process($purifier->purify($markdown->parse($comment['text']))),
                    ] : [],
                ];
            }, $feed->getItems()),
        ]);
    }
}
