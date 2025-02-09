<?php

declare(strict_types=1);

namespace App\Controller\Api\Work\Projects;

use App\Model\Work\Entity\Members\Member\Member;
use App\Model\Work\Entity\Projects\Task\File\File;
use App\Model\Work\Entity\Projects\Task\Task;
use App\Model\Work\UseCase\Projects\Task\Plan;
use App\ReadModel\Work\Projects\Action\ActionFetcher;
use App\ReadModel\Work\Projects\Action\Feed\Feed;
use App\ReadModel\Work\Projects\Action\Feed\Item;
use App\ReadModel\Work\Projects\Task\CommentFetcher;
use App\Service\Gravatar;
use App\Service\Uploader\FileUploader;
use App\Service\Work\Processor\Processor;
use cebe\markdown\MarkdownExtra;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/work/projects/tasks", name="work.projects.tasks")
 */
class TasksController extends AbstractController
{
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
            }, $task->getFiles()),
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
                            'project' => [
                                'id' => $action['set_project_id'],
                                'name' => $action['set_project_name'],
                            ],
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
                            'executor' => [
                                'id' => $action['set_executor_id'],
                                'name' => $action['set_executor_name'],
                            ],
                            'revoked_executor' => [
                                'id' => $action['set_revoked_executor_id'],
                                'name' => $action['set_revoked_executor_name'],
                            ],
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