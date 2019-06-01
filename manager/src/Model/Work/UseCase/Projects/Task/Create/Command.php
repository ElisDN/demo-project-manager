<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Task\Create;

use App\Model\Work\Entity\Projects\Task\Type;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $project;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $member;
    /**
     * @var NameRow[]
     * @Assert\NotBlank()
     * @Assert\Valid()
     */
    public $names;
    /**
     * @var string
     */
    public $content;
    /**
     * @var int
     */
    public $parent;
    /**
     * @var \DateTimeImmutable
     * @Assert\Date()
     */
    public $plan;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $type;
    /**
     * @Assert\NotBlank()
     */
    public $priority;

    public function __construct(string $project, string $member)
    {
        $this->project = $project;
        $this->member = $member;
        $this->type = Type::NONE;
        $this->priority = 2;
    }
}
