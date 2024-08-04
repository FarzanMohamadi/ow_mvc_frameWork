<?php
/**
 * Main menu component class. 
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_MainMenu extends BASE_CMP_Menu
{
    /**
     *
     * @var boolean 
     */
    protected $responsive;
    
    /**
     * Constructor.
     */
    public function __construct( array $params = array() )
    {
        parent::__construct();
        
        $this->responsive = isset($params["responsive"]) && $params["responsive"];
    }
    
    public function render()
    {
        $menuItems = OW::getDocument()->getMasterPage()
                ->getMenu(BOL_NavigationService::MENU_TYPE_MAIN)->getMenuItems();
        
        if ( !$this->responsive )
        {
            $this->setMenuItems($menuItems);
            
            return parent::render();
        }
        
        $responsiveMenu = new BASE_CMP_ResponsiveMenu();
        $responsiveMenu->setMenuItems($menuItems);
        
        return $responsiveMenu->render();
    }
}