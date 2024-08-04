<?php
/**
 * Base menu component class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Menu extends OW_Component
{
    /**
     * @var array
     */
    protected $menuItems = array();
    /**
     * @var string
     */
    protected $name;

    /**
     * Constructor.
     *
     * @param array $menuItems
     * @param string $template
     */
    public function __construct( $menuItems = array() )
    {
        parent::__construct();

        $this->setMenuItems($menuItems);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'menu.html');
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @param string $menuName
     */
    public function setItemActive( $menuName )
    {
        $menuItem = $this->getElement($menuName);
        if(isset($menuItem)) {
            $menuItem->setActive(true);
        }
    }

    /**
     * @param array $menuItems
     */
    public function setMenuItems( $menuItems )
    {
        if ( empty($menuItems) )
        {
            return;
        }
        
        foreach ( $menuItems as $item_index=>$item )
        {
            if ($item->getOrder() == null)
                $item->setOrder($item_index);
            $this->addElement($item);
        }
    }

    /**
     * Adds menu item.
     *
     * @param BASE_MenuItem $menuItem
     */
    public function addElement( BASE_MenuItem $menuItem )
    {
        $this->menuItems[] = $menuItem;
    }

    /**
     * Returns menu item for provided key.
     *
     * @param string $prefix
     * @param string $key
     * @return BASE_MenuItem
     */
    public function getElement( $key, $prefix = null )
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $value )
        {
            if ( $value->getKey() === trim($key) && ( $prefix === null || $value->getPrefix() === trim($prefix) ) )
            {
                return $value;
            }
        }

        return null;
    }

    /**
     * Deletes menu element by key.
     *
     * @param string $prefix
     * @param string $key
     */
    public function removeElement( $key, $prefix = null )
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $itemKey => $value )
        {
            if ( $value->getKey() === trim($key) && ( $prefix === null || $value->getPrefix() === trim($prefix) ) )
            {
                unset($this->menuItems[$itemKey]);
            }
        }
    }

    /**
     * Deactivates all menu elements.
     */
    public function deactivateElements()
    {
        /* @var $value BASE_MenuItem */
        foreach ( $this->menuItems as $itemKey => $value )
        {
            $value->setActive(false);
        }
    }

    protected function getItemViewData( BASE_MenuItem $menuItem )
    {
        return array(
            'label' => $menuItem->getLabel(),
            'url' => $menuItem->getUrl(),
            'class' => $menuItem->getPrefix() . '_' . $menuItem->getKey(),
            'iconClass' => $menuItem->getIconClass(),
            'active' => $menuItem->isActive(),
            'new_window' => $menuItem->getNewWindow(),
            'prefix' => $menuItem->getPrefix(),
            'key' => $menuItem->getKey()
        );
    }


    /**
     * @see OW_Renderable::onBeforeRender()
     *
     */
    public function onBeforeRender()
    {
        $arrayToAssign = array();

        usort($this->menuItems, array(BOL_NavigationService::getInstance(), 'sortObjectListByAsc'));

        /* @var $menuItem BASE_MenuItem */
        foreach ( $this->menuItems as $menuItem )
        {
            $menuItem->activate(OW::getRouter()->getBaseUrl() . OW::getRequest()->getRequestUri());
            $arrayToAssign[] = $this->getItemViewData($menuItem);
        }

        $this->assign('class', 'ow_' . OW_Autoload::getInstance()->classToFilename(get_class($this), false));
        $this->assign('data', $arrayToAssign);
    }
}

/**
 * Base menu element class.
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MenuItem
{
    /**
     * @var string
     */
    private $label;
    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var string
     */
    private $key;
    /**
     * @var integer
     */
    private $order;
    /**
     * @var boolean
     */
    private $newWindow;
    /**
     * @var string
     */
    private $iconClass;
    /**
     * @var boolean
     */
    private $active = false;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct()
    {

    }

    /**
     * @param string $iconClass
     * @return BASE_MenuItem
     */
    public function setIconClass( $iconClass )
    {
        $this->iconClass = $iconClass;
        return $this;
    }

    /**
     * @return string
     */
    public function getIconClass()
    {
        return $this->iconClass;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $key
     * @return BASE_MenuItem
     */
    public function setKey( $key )
    {
        $this->key = trim($key);
        return $this;
    }

    /**
     * @param string $label
     * @return BASE_MenuItem
     */
    public function setLabel( $label )
    {
        $this->label = trim($label);
        return $this;
    }

    /**
     * @param integer $order
     * @return BASE_MenuItem
     */
    public function setOrder( $order )
    {
        $this->order = (int) $order;
        return $this;
    }

    /**
     * @param string $url
     * @return BASE_MenuItem
     */
    public function setUrl( $url )
    {
        $this->url = trim($url);
        return $this;
    }

    /**
     * @return boolean
     */
    public function getNewWindow()
    {
        return $this->newWindow;
    }

    /**
     * @param boolean $newWindow
     * @return BASE_MenuItem
     */
    public function setNewWindow( $newWindow )
    {
        $this->newWindow = $newWindow;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string  $prefix
     * @return BASE_MenuItem
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function isActive()
    {
        return $this->active;
    }

    /***
     * @deprecated User $menu->setItemActive() instead
     * @param $active
     * @return $this
     */
    public function setActive( $active )
    {
        $this->active = (bool) $active;
        return $this;
    }

    /**
     * @param string $url
     * @return boolean
     */
    public function activate( $url )
    {
        if ( UTIL_String::removeFirstAndLastSlashes($this->url) === UTIL_String::removeFirstAndLastSlashes($url) )
        {
            $this->setActive(true);
        }
    }
}