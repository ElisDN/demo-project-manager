<?php

declare(strict_types=1);

namespace App\Model\Work\Entity\Projects\Task\File;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class Info
{
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $path;
    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $name;
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $size;

    public function __construct(string $path, string $name, int $size)
    {
        $this->path = $path;
        $this->name = $name;
        $this->size = $size;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }
}
