<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

class FRMADVANCESEARCH_MCTRL_Container extends OW_MobileActionController
{
    public function index($params)
    {
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmadvancesearch', 'search_users');

        OW::getDocument()->setHeading(OW::getLanguage()->text('frmadvancesearch','search_users'));
        $this->setPageHeading(OW::getLanguage()->text('frmadvancesearch', 'search_users'));
        $this->setPageHeadingIconClass('ow_ic_write');

        $place = 'frmadvancesearch';
        $componentPanel = $this->initDragAndDrop($place, OW::getUser()->getId());
    }

    private function initDragAndDrop( $place, $entityId = null, $componentTemplate = "widget_panel" )
    {
        $widgetService = BOL_MobileWidgetService::getInstance();

        $state = $widgetService->findCache($place);
        if ( empty($state) )
        {
            $state = array();
            $state['defaultComponents'] = $widgetService->findPlaceComponentList($place);
            $state['defaultPositions'] = $widgetService->findAllPositionList($place);
            $state['defaultSettings'] = $widgetService->findAllSettingList();

            $widgetService->saveCache($place, $state);
        }

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];

        $componentPanel = new BASE_MCMP_WidgetPanel($place, $entityId, $defaultComponents, $componentTemplate);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);

        $this->addComponent('dnd', $componentPanel);

        return $componentPanel;
    }

}