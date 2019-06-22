<?php

declare(strict_types=1);

namespace App\Menu\Work\Projects;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProjectMenu
{
    private $factory;
    private $auth;

    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $auth)
    {
        $this->factory = $factory;
        $this->auth = $auth;
    }

    public function build(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav nav-tabs mb-4']);

        $menu
            ->addChild('Dashboard', [
                'route' => 'work.projects.project.show',
                'routeParameters' => ['id' => $options['project_id']]
            ])
            ->setExtra('routes', [
                ['route' => 'work.projects.project.show'],
                ['pattern' => '/^work.projects.project.show\..+/']
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Actions', [
                'route' => 'work.projects.project.actions',
                'routeParameters' => ['project_id' => $options['project_id']]
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Tasks', [
                'route' => 'work.projects.project.tasks',
                'routeParameters' => ['project_id' => $options['project_id']]
            ])
            ->setExtra('routes', [
                ['route' => 'work.projects.project.tasks'],
                ['pattern' => '/^work.projects.project.tasks\..+/']
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $menu
            ->addChild('Calendar', [
                'route' => 'work.projects.project.calendar',
                'routeParameters' => ['project_id' => $options['project_id']]
            ])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        if ($this->auth->isGranted('ROLE_WORK_MANAGE_PROJECTS')) {
            $menu
                ->addChild('Settings', [
                    'route' => 'work.projects.project.settings',
                    'routeParameters' => ['project_id' => $options['project_id']]
                ])
                ->setExtra('routes', [
                    ['route' => 'work.projects.project.settings'],
                    ['pattern' => '/^work.projects.project.settings\..+/']
                ])
                ->setAttribute('class', 'nav-item')
                ->setLinkAttribute('class', 'nav-link');
        }

        return $menu;
    }
}
