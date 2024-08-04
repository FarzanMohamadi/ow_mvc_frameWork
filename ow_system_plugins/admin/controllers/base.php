<?php
/**
 * Admin index controller class.
 *
 * @package ow_system_plugins.admin.controllers
 * @since 1.0
 */
class ADMIN_CTRL_Base extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $this->setPageHeading(OW::getLanguage()->text('admin', 'admin_dashboard'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');
        $this->assign('version', OW::getConfig()->getValue('base', 'soft_version'));
        $this->assign('build', OW::getConfig()->getValue('base', 'soft_build'));
    }

    /**
     * Generate sitemap
     */
    public function generateSitemap()
    {
        do
        {
            BOL_SeoService::getInstance()->generateSitemap();
        }
        while ( !(int) OW::getConfig()->getValue('base', 'seo_sitemap_build_finished') );

        exit;
    }

    public function dashboard( $paramList )
    {
        $this->setPageHeading(OW::getLanguage()->text('admin', 'admin_dashboard'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = BOL_ComponentAdminService::PLASE_ADMIN_DASHBOARD;
        $customize = !empty($paramList['mode']) && $paramList['mode'] == 'customize';
        
        $service = BOL_ComponentAdminService::getInstance();
        $schemeList = $service->findSchemeList();
        $state = $service->findCache($place);

        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $service->findPlaceComponentList($place);
            $state['defaultPositions'] = $service->findAllPositionList($place);
            $state['defaultSettings'] = $service->findAllSettingList();
            $state['defaultScheme'] = (array) $service->findSchemeByPlace($place);

            $service->saveCache($place, $state);
        }

        if ( empty($state['defaultScheme']) && !empty($schemeList) )
        {
            $state['defaultScheme'] = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DashboardWidgetPage($place, $state['defaultComponents'], $customize);
        $componentPanel->allowCustomize(true);

        $customizeUrls = array(
            'customize' => OW::getRouter()->urlForRoute('admin_dashboard_customize', array('mode' => 'customize')),
            'normal' => OW::getRouter()->urlForRoute('admin_dashboard')
        );

        $componentPanel->customizeControlCunfigure($customizeUrls['customize'], $customizeUrls['normal']);

        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($state['defaultPositions']);
        $componentPanel->setSettingList($state['defaultSettings']);
        $componentPanel->setScheme($state['defaultScheme']);

        $this->addComponent('componentPanel', $componentPanel);
    }
}