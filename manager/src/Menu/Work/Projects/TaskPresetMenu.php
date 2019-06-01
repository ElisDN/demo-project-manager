<?php

declare(strict_types=1);

namespace App\Menu\Work\Projects;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class TaskPresetMenu
{
    private $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function build(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root')
            ->setChildrenAttributes(['class' => 'nav nav-tabs mb-4']);

        if ($options['project_id']) {
            $params = array_replace_recursive($options['route_params'], ['project_id' => $options['project_id']]);
        } else {
            $params = $options['route_params'];
        }

        $route = $options['project_id'] ? 'work.projects.project.tasks' : 'work.projects.tasks';
        $menu
            ->addChild('All Tasks', [
                'route' => $route,
                'routeParameters' => $params
            ])
            ->setExtra('routes', [['route' => $route]])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $route = $options['project_id'] ? 'work.projects.project.tasks.me' : 'work.projects.tasks.me';
        $menu
            ->addChild('For Me', [
                'route' => $route,
                'routeParameters' => array_replace_recursive($params, ['form' => ['executor' => null]]),
            ])
            ->setExtra('routes', [['route' => $route]])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        $route = $options['project_id'] ? 'work.projects.project.tasks.own' : 'work.projects.tasks.own';
        $menu
            ->addChild('My Own', [
                'route' => $route,
                'routeParameters' => array_replace_recursive($params, ['form' => ['author' => null]]),
            ])
            ->setExtra('routes', [['route' => $route]])
            ->setAttribute('class', 'nav-item')
            ->setLinkAttribute('class', 'nav-link');

        return $menu;
    }
}
