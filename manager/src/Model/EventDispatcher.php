<?php

declare(strict_types=1);

namespace App\Model;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatcher
{
    private $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(array $events): void
    {
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }
    }
}
