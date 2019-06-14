<?php

declare(strict_types=1);

namespace App\Model\Comment\UseCase\Comment\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $author;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $entityType;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $entityId;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $text;

    public function __construct(string $author, string $type, string $id)
    {
        $this->author = $author;
        $this->entityType = $type;
        $this->entityId = $id;
    }
}
