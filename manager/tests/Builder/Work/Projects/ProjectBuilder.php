<?php

declare(strict_types=1);

namespace App\Tests\Builder\Work\Projects;

use App\Model\Work\Entity\Projects\Project\Id;
use App\Model\Work\Entity\Projects\Project\Project;

class ProjectBuilder
{
    private $name;
    private $sort;

    public function __construct()
    {
        $this->name = 'Project';
        $this->sort = 1;
    }

    public function build(): Project
    {
        return new Project(
            Id::next(),
            $this->name,
            $this->sort
        );
    }
}