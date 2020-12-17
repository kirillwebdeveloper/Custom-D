<?php

namespace App\Event\Dashboard\Menu;

use App\Modele\Menu\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class MenuEvent extends Event
{
    /**
     * @var array
     */
    private $menuItems = [];
    /**
     * @var Request
     */
    private $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @return MenuItem[]
     */
    public function getItems(): array
    {
        return $this->menuItems;
    }

    /**
     * @param MenuItem $item
     *
     * @return self
     */
    public function addItem($item)
    {
        $this->menuItems[] = $item;

        return $this;
    }
}
