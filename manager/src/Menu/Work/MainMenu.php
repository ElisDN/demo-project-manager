<?php

declare(strict_types=1);

namespace App\Menu\Work;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MainMenu
{
    private $factory;
    private $auth;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth)
    {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    public function build(): ItemInterface
    {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav nav-tabs mb-4']);

        $menu
            ->addChild('Projects', ['route' => 'work.projects'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Actions', ['route' => 'work.projects.actions'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Tasks', ['route' => 'work.projects.tasks'])
            ->setExtra('routes', [
                ['route' => 'work.projects.tasks'],
                ['pattern' => '/^work.projects.tasks\..+/']
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Calendar', ['route' => 'work.projects.calendar'])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        if ($this->auth->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $menu
                ->addChild('Roles', ['route' => 'work.projects.roles'])
                ->setExtra('routes', [
                    ['route' => 'work.projects.roles'],
                    ['pattern' => '/^work.projects.roles\..+/']
                ])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link');
        }

        return $menu;
    }
}
