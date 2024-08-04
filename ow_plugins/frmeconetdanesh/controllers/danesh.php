<?php
class FRMECONETDANESH_CTRL_Danesh extends OW_ActionController
{

    public function tagsWidget($params)
    {
        $language =OW_Language::getInstance();
        $this->setPageHeading($language->text('frmeconetdanesh', 'main_menu_item'));
        $this->setPageTitle($language->text('frmeconetdanesh', 'main_menu_item'));
        if(!FRMSecurityProvider::checkPluginActive('blogs', true)) {
            throw new Redirect404Exception();
        }
        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('blogs', 'view') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('blogs', 'view');
            throw new AuthorizationException($status['msg']);
        }

        $place = 'frmeconetdanesh';

        $componentAdminService = BOL_ComponentAdminService::getInstance();

        $template ='drag_and_drop_entity_panel';

        $schemeList = $componentAdminService->findSchemeList();
        $defaultScheme = $componentAdminService->findSchemeByPlace($place);
        if ( empty($defaultScheme) && !empty($schemeList) )
        {
            $defaultScheme = reset($schemeList);
        }

        if ( !$componentAdminService->isCacheExists($place) )
        {
            $state = array();
            $state['defaultComponents'] = $componentAdminService->findPlaceComponentList($place);
            $state['defaultPositions'] = $componentAdminService->findAllPositionList($place);
            $state['defaultSettings'] = $componentAdminService->findAllSettingList();
            $state['defaultScheme'] = $defaultScheme;

            $componentAdminService->saveCache($place, $state);
        }

        $state = $componentAdminService->findCache($place);

        $defaultComponents = $state['defaultComponents'];
        $defaultPositions = $state['defaultPositions'];
        $defaultSettings = $state['defaultSettings'];
        $defaultScheme = $state['defaultScheme'];

        $componentPanel = new BASE_CMP_DragAndDropEntityPanel($place, '', $defaultComponents, false, $template);
        
        $componentPanel->setSchemeList($schemeList);
        $componentPanel->setPositionList($defaultPositions);
        $componentPanel->setSettingList($defaultSettings);
        $componentPanel->setScheme($defaultScheme);


        $this->assign('componentPanel', $componentPanel->render());

    }

}