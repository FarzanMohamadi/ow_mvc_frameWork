<?php
/**
 * Admin menu class. Works with all admin menu types.
 *
 * @package ow_system_plugins.admin.components
 * @since 1.0
 */
class ADMIN_CMP_AdminMenu extends BASE_CMP_Menu
{
    /**
     * @var boolean
     */
    private $active = false;

    /**
     * Constructor.
     * 
     * @param array $itemsList
     */
    public function __construct( $itemsList )
    {
        parent::__construct();
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($itemsList));
        // set default template
        $this->setTemplate(null);
    }

    public function render($subMenuClass = null)
    {
        $this->assign('isActive', $this->active);
        $this->assign('subMenuClass', $subMenuClass);

        return parent::render();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        /* @var $menuItem BASE_MenuItem */
        foreach ( $this->menuItems as $menuItem )
        {
            if ( $menuItem->isActive() )
            {
                $this->active = true;
            }
        }
    }

    /**
     * Returns first element.
     *
     * @return BASE_MenuItem
     */
    public function getFirstElement()
    {
        usort($this->menuItems, array(BOL_NavigationService::getInstance(), 'sortObjectListByAsc'));
        return $this->menuItems[0];
    }

    /**
     * Returns menu elements count.
     *
     * @return integer
     */
    public function getElementsCount()
    {
        return count($this->menuItems);
    }

    /**
     * Checks if menu has active elements.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    public function setCategory($category)
    {
        $this->assign('category', $category);
    }
}