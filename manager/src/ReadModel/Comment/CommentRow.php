<?php

declare(strict_types=1);

namespace App\ReadModel\Comment;

class CommentRow
{
    public $id;
    public $date;
    public $author_id;
    public $author_name;
    public $author_email;
    public $text;
}
