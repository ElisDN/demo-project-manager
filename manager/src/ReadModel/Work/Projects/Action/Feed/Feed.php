<?php

declare(strict_types=1);

namespace App\ReadModel\Work\Projects\Action\Feed;

use App\ReadModel\Comment\CommentRow;

class Feed
{
    private $actions;
    private $comments;

    /**
     * @param array $actions
     * @param CommentRow[] $comments
     */
    public function __construct(array $actions, array $comments)
    {
        $this->actions = $actions;
        $this->comments = $comments;
    }

    /**
     * @return Item[]
     * @throws \Exception
     */
    public function getItems(): array
    {
        $items = [];

        foreach ($this->actions as $action) {
            $items[] = Item::forAction(new \DateTimeImmutable($action['date']), $action);
        }

        foreach ($this->comments as $comment) {
            $items[] = Item::forComment(new \DateTimeImmutable($comment->date), $comment);
        }

        usort($items, static function (Item $a, Item $b) {
            return $a->getDate() <=> $b->getDate();
        });

        return $items;
    }
}
