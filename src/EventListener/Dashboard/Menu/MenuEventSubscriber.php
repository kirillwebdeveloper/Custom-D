<?php

namespace App\EventListener\Dashboard\Menu;

use App\Event\Dashboard\Menu\MenuEvent;
use App\Modele\Menu\MenuItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Class MenuEventSubscriber.
 */
class MenuEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Security
     */
    private $security;

    /**
     * MenuEventSubscriber constructor.
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [MenuEvent::class => ['addMenu']];
    }

    /**
     * Add menu.
     */
    public function addMenu(MenuEvent $event): void
    {
        $menu = [];

        // tickets
        $menu[] = (new MenuItem('ticket.breadcrumb', 'dashboard_ticket_index', [], ['TICKET_VIEW'], 'far fa-envelope'));

        // projects
        $menu[] = (new MenuItem('project.breadcrumb', false, [], [], 'fas fa-sitemap'))
            ->addChild(
                new MenuItem(
                    'project.breadcrumb',
                    'dashboard_project_view_index',
                    [],
                    ['PROJECT_VIEW']
                )
            )
            ->addChild(
                new MenuItem(
                    'project.prospector.breadcrumb',
                    'dashboard_project_prospector_index',
                    [],
                    ['PROSPECTOR_MANAGE']
                )
            );

        foreach ($menu as $menuItem) {
            $isGranted = true;
            foreach ($menuItem->getRoles() as $role) {
                if (!$this->security->isGranted($role)) {
                    $isGranted = false;
                    break;
                }
            }
            if ($isGranted) {
                if ($menuItem->hasChildren()) {
                    $children = [];
                    foreach ($menuItem->getChildren() as $child) {
                        $childIsGranted = true;
                        foreach ($child->getRoles() as $childRole) {
                            if (!$this->security->isGranted($childRole)) {
                                $childIsGranted = false;
                                break;
                            }
                        }
                        if ($childIsGranted) {
                            $children[] = $child;
                        }
                    }
                    $menuItem->setChildren($children);

                    if ($menuItem->hasChildren()) {
                        $event->addItem($menuItem);
                    }
                } else {
                    $event->addItem($menuItem);
                }
            }
        }
        $this->activateByRoute($event->getRequest()->get('_route'), $menu);
    }

    /**
     * Activate by route.
     *
     * @param MenuItem[] $items
     *
     * @return MenuItem[]
     */
    private function activateByRoute(string $route, $items)
    {
        foreach ($items as $item) {
            if ($item->hasChildren()) {
                $this->activateByRoute($route, $item->getChildren());
            } elseif ($item->getRoute() === $route) {
                $item->setIsActive(true);
            } elseif (mb_substr($item->getRoute(), 0, mb_strrpos($item->getRoute(), '_')) === mb_substr(
                    $route,
                    0,
                    mb_strrpos($route, '_')
                )) {
                $item->setIsActive(true);
            }
        }

        return $items;
    }
}
