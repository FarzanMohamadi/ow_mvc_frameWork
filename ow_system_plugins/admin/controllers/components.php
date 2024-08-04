<?php
/**
 * Widgets admin panel
 *
 * @package ow_system_plugins.base.controller
 * @since 1.0
 */
class ADMIN_CTRL_Components extends ADMIN_CTRL_Abstract
{
    /**
     * @var BOL_ComponentService
     *
     */
    private $componentsService;

    public function __construct()
    {
        parent::__construct();

        $this->componentsService = BOL_ComponentAdminService::getInstance();
    }

    public function init()
    {
        $basePluginDir = OW::getPluginManager()->getPlugin('BASE')->getRootDir();

        $controllersTemplate = OW::getPluginManager()->getPlugin('ADMIN')->getCtrlViewDir() . 'drag_and_drop_components.html';
        $this->setTemplate($controllersTemplate);
    }

    private function action( $place, $componentTemplate )
    {
        $dbSettings = $this->componentsService->findAllSettingList();

        $dbPositions = $this->componentsService->findAllPositionList($place);

        $dbComponents = $this->componentsService->findPlaceComponentList($place);
        $activeScheme = $this->componentsService->findSchemeByPlace($place);
        $schemeList = $this->componentsService->findSchemeList();

        if ( empty($activeScheme) && !empty($schemeList) )
        {
            $activeScheme = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DragAndDropAdminPanel($place, $dbComponents, $componentTemplate);
        $componentPanel->setPositionList($dbPositions);
        $componentPanel->setSettingList($dbSettings);
        $componentPanel->setSchemeList($schemeList);
        if ( !empty($activeScheme) )
        {
            $componentPanel->setScheme($activeScheme);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function dashboard()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'widgets_admin_dashboard_heading'));
        $this->setDocumentKey('admin_area_dashboard_page');
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = BOL_ComponentAdminService::PLACE_DASHBOARD;
        $this->action($place, 'drag_and_drop_panel');
    }

    public function profile()
    {
        $this->setPageHeading(OW::getLanguage()->text('base', 'widgets_admin_profile_heading'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $place = BOL_ComponentAdminService::PLACE_PROFILE;
        $this->action($place, 'drag_and_drop_panel');
    }
}