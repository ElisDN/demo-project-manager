<?php

declare(strict_types=1);

namespace App\Model\Work\UseCase\Projects\Project\Membership\Add;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $project;
    /**
     * @Assert\NotBlank()
     */
    public $member;
    /**
     * @Assert\NotBlank()
     */
    public $departments;
    /**
     * @Assert\NotBlank()
     */
    public $roles;

    public function __construct(string $project)
    {
        $this->project = $project;
    }
}
