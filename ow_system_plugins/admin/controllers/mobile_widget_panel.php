<?php
/**
 * Widgets admin panel
 *
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_MobileWidgetPanel extends ADMIN_CTRL_Abstract
{
    public function init()
    {
        $template = OW::getPluginManager()->getPlugin("admin")->getCtrlViewDir() . "mobile_drag_and_drop.html";
        $this->setTemplate($template);
    }
    
    private function action( $place, $componentTemplate )
    {
        $widgetService = BOL_MobileWidgetService::getInstance();
        
        $dbSettings = $widgetService->findAllSettingList();
        $dbPositions = $widgetService->findAllPositionList($place);
        $dbComponents = $widgetService->findPlaceComponentList($place);

        $componentPanel = new ADMIN_CMP_MobileWidgetPanel($place, $dbComponents, $componentTemplate);
        $componentPanel->setPositionList($dbPositions);
        $componentPanel->setSettingList($dbSettings);

        $this->assign('dnd', $componentPanel->render());
    }

    public function dashboard()
    {
        $this->setPageHeading(OW::getLanguage()->text('mobile', 'widgets_admin_dashboard_heading'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = BOL_MobileWidgetService::PLACE_MOBILE_DASHBOARD;
        $this->action($place, 'mobile_widget_panel');
    }

    public function profile()
    {
        $this->setPageHeading(OW::getLanguage()->text('mobile', 'widgets_admin_profile_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $place = BOL_MobileWidgetService::PLACE_MOBILE_PROFILE;
        $this->action($place, 'mobile_widget_panel');
    }
    
    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('mobile', 'widgets_admin_index_heading'));
        //$this->setPageHeadingIconClass('ow_ic_user');
        
        $place = BOL_MobileWidgetService::PLACE_MOBILE_INDEX;
        $this->action($place, 'mobile_widget_panel');
    }
}