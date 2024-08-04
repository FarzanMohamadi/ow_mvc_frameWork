<?php
/**
 * Widgets admin panel
 *
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_MobileNavigation extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('admin', 'page_title_mobile_menus'));
        $this->setPageHeading($language->text('admin', 'page_title_mobile_menus'));

        $dnd = new ADMIN_CMP_MobileNavigation();
        $this->setup($dnd);
        
        $this->addComponent("dnd", $dnd);
    }
    
    public function rsp()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = trim($_POST['command']);
        $whitelist = array('setup', 'saveOrder', 'deleteItem', 'saveItemSettings');
        if (!in_array($command, $whitelist)) {
            throw new Redirect404Exception();
        }
        $data = json_decode($_POST['data'], true);
        $shared = json_decode($_POST['shared'], true);
        
        $response = call_user_func(array($this, $command), $data, $shared);

        echo json_encode($response);
        exit;
    }
    
    protected function setup( ADMIN_CMP_MobileNavigation $dnd )
    {
        $baseStaticJsUrl = OW::getPluginManager()->getPlugin('BASE')->getStaticJsUrl();
        OW::getDocument()->addScript($baseStaticJsUrl . 'jquery-ui.min.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_slider.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'ajax_utils.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'drag_and_drop_handler.js');
        OW::getDocument()->addScript($baseStaticJsUrl . 'component_drag_and_drop.js');
        $navigationService = BOL_NavigationService::getInstance();
        
        $responderUrl = OW::getRouter()->urlFor("ADMIN_CTRL_MobileNavigation", "rsp");
        $dnd->setResponderUrl($responderUrl);
        
        $template = OW::getPluginManager()->getPlugin("admin")->getCtrlViewDir() . "mobile_drag_and_drop.html";
        $this->setTemplate($template);
        
        $panels = array(
            "top" => BOL_MobileNavigationService::MENU_TYPE_TOP,
            "bottom" => BOL_MobileNavigationService::MENU_TYPE_BOTTOM,
            "hidden" => BOL_MobileNavigationService::MENU_TYPE_HIDDEN,
        );
        
        foreach ( $panels as $panel => $menuType )
        {
            $menuItems = $navigationService->findMenuItems($menuType);
            $items = array();

            foreach ( $menuItems as $item )
            {
                /* @var $item BOL_MenuItem */

                $settings = BOL_MobileNavigationService::getInstance()->getItemSettingsByPrefixAndKey($item["prefix"], $item["key"]);

                $items[] = array(
                    "key" => $item["prefix"] . ':' . $item["key"],
                    "title" => $settings[BOL_MobileNavigationService::SETTING_LABEL],
                    "custom" => $item["prefix"] == BOL_MobileNavigationService::MENU_PREFIX,
                    "visibleFor" => $item["visibleFor"]
                );
            }
            
            $dnd->setupPanel($panel, array(
                "key" => $menuType,
                "items" => $items
            ));
        }
        
        $dnd->setupPanel("new", array(
            "items" => array(
                array("key" => "new-item", "title" => OW::getLanguage()->text("mobile", "admin_nav_new_item_label"))
            )
        ));
        
        $dnd->setPrefix(BOL_MobileNavigationService::MENU_PREFIX);
        $dnd->setSharedData(array(
            "menuPrefix" => BOL_MobileNavigationService::MENU_PREFIX
        ));
        
        $template = OW::getPluginManager()->getPlugin("admin")->getCmpViewDir() . "mobile_navigation.html";
        $dnd->setTemplate($template);
    }

    public function saveOrder( $data, $shared ) 
    {
        $mobileNavigationService = BOL_MobileNavigationService::getInstance();
        $navigationService = BOL_NavigationService::getInstance();
        
        $response = array();
        
        $response["items"] = array();

        foreach ( $data["panels"] as $menu => $items )
        {
            $order = 0;
            
            foreach ( $items as $item )
            {
                $initializeSetting=null;
                list($prefix, $key) = explode(':', $item);
                $menuItem = $navigationService->findMenuItem($prefix, $key);
                
                if ( $menuItem === null )
                {
                    $menuItem = $mobileNavigationService->createEmptyItem($menu, $order);
                    $initializeSetting=$menuItem->getPrefix().':'.$menuItem->getKey();
                }
                else 
                {
                    $menuItem->setOrder($order);
                    $menuItem->setType($menu);
                    
                    $navigationService->saveMenuItem($menuItem);
                }
                
                $order++;
                
                $settings = BOL_MobileNavigationService::getInstance()->getItemSettingsByPrefixAndKey($menuItem->prefix, $menuItem->key);
                
                $response["items"][$item] = array(
                    "key" => $menuItem->getPrefix() . ':' . $menuItem->getKey(),
                    "title" => $settings[BOL_MobileNavigationService::SETTING_LABEL],
                    "custom" => $menuItem->getPrefix() == BOL_MobileNavigationService::MENU_PREFIX,
                    "initializeSetting"=>$initializeSetting
                );
            }
        }
        
        return $response;
    }
    
    public function deleteItem( $data, $shared ) 
    {
        $mobileNavigationService = BOL_MobileNavigationService::getInstance();
        $navigationService = BOL_NavigationService::getInstance();
        list($prefix, $key) = explode(':', $data["key"]);
        
        $menuItem = $navigationService->findMenuItem($prefix, $key);
        
        if ( $menuItem === null  )
        {
            return;
        }
        
        $mobileNavigationService->deleteItem($menuItem);
    }
    
    public function saveItemSettings()
    {
        list($prefix, $key) = explode(':', $_POST["key"]);
        $menuItem = BOL_NavigationService::getInstance()->findMenuItem($prefix, $key);
        
        $form = new ADMIN_CLASS_MobileNavigationItemSettingsForm($menuItem, $menuItem->getPrefix() == BOL_MobileNavigationService::MENU_PREFIX, false);

        if ( $form->isValid($_POST) )
        {
            $form->process();
            $this->redirect(OW::getRouter()->urlForRoute('mobile.admin.navigation'));
        }

    }
}