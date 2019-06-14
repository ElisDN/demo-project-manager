<?php

declare(strict_types=1);

namespace App\Model\Comment\UseCase\Comment\Edit;

use App\Model\Comment\Entity\Comment\Comment;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $id;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $text;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function fromComment(Comment $comment): self
    {
        $command = new self($comment->getId()->getValue());
        $command->text = $comment->getText();
        return $command;
    }
}
