<?php

declare(strict_types=1);

namespace App\Model;

interface Flusher
{
    public function flush(): void;
}
