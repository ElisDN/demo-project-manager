<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Members\Group;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="work_members_groups")
 */
class Group
{
    /**
     * @var Id
     * @ORM\Column(type="work_members_group_id")
     * @ORM\Id
     */
    private $id;
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;
    /**
     * @ORM\Version()
     * @ORM\Column(type="integer")
     */
    private $version;

    public function __construct(Id $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function edit(string $name): void
    {
        $this->name = $name;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
