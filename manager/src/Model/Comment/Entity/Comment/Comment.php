<?php

declare(strict_types=1);

namespace App\Model\Comment\Entity\Comment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="comment_comments", indexes={
 *     @ORM\Index(columns={"date"}),
 *     @ORM\Index(columns={"entity_type", "entity_id"})
 * })
 */
class Comment
{
    /**
     * @var Id
     * @ORM\Column(type="comment_comment_id")
     * @ORM\Id
     */
    private $id;
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;
    /**
     * @var AuthorId
     * @ORM\Column(type="comment_comment_author_id")
     */
    private $authorId;
    /**
     * @var Entity
     * @ORM\Embedded(class="Entity")
     */
    private $entity;
    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $text;
    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="datetime_immutable", nullable=true, name="update_date")
     */
    private $updateDate;
    /**
     * @ORM\Version()
     * @ORM\Column(type="integer")
     */
    private $version;

    public function __construct(AuthorId $author, Id $id, \DateTimeImmutable $date, string $text, Entity $entity)
    {
        $this->authorId = $author;
        $this->id = $id;
        $this->date = $date;
        $this->text = $text;
        $this->entity = $entity;
    }

    public function edit(\DateTimeImmutable $date, string $text): void
    {
        $this->updateDate = $date;
        $this->text = $text;
    }

    public function getAuthorId(): AuthorId
    {
        return $this->authorId;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getEntity(): Entity
    {
        return $this->entity;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
