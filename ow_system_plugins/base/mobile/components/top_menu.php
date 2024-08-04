<?php
/**
 * Main menu component class. 
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_TopMenu extends BASE_CMP_Menu
{

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getMobileCmpViewDir() . 'top_menu.html');
        $this->name = BOL_MobileNavigationService::MENU_TYPE_TOP;
        $menuItems = BOL_NavigationService::getInstance()->findMenuItems(BOL_MobileNavigationService::MENU_TYPE_TOP);
        $this->setMenuItems(BOL_NavigationService::getInstance()->getMenuItems($menuItems));        
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $event = new BASE_CLASS_EventCollector('base.mobile_top_menu_add_options');
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $optionsArray = array();
        foreach ( $data as $item )
        {
            $optionsArray[$item['prefix'].$item['key']] = array(
                'url' => (isset($item['url']) ? trim($item['url']) : null),
                'id' => (isset($item['id']) ? trim($item['id']) : null)
            );
        }
        if(count($this->assignedVars['data'])==0){
            OW::getDocument()->addStyleDeclaration('
			#owm_header_left_btn {
				visibility: hidden;
    		}
		');
        }
        foreach ( $this->assignedVars['data'] as $key => $dataItem )
        {
            if ( !empty($optionsArray[$dataItem['prefix'].$dataItem['key']]) )
            {
                $this->assignedVars['data'][$key]['addUrl'] = $optionsArray[$dataItem['prefix'].$dataItem['key']]['url'];
                $this->assignedVars['data'][$key]['addId'] = $optionsArray[$dataItem['prefix'].$dataItem['key']]['id'];
            }
        }
    }
}