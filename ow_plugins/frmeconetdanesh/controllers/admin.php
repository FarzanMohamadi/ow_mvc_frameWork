<?php
class FRMECONETDANESH_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('frmeconetdanesh', 'admin_settings_title'));
        $this->setPageTitle($language->text('frmeconetdanesh', 'admin_settings_title'));

        $componentService = BOL_ComponentAdminService::getInstance();
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = 'frmeconetdanesh';

        $dbSettings = $componentService->findAllSettingList();

        $dbPositions = $componentService->findAllPositionList($place);

        $dbComponents = $componentService->findPlaceComponentList($place);
        $activeScheme = $componentService->findSchemeByPlace($place);
        $schemeList = $componentService->findSchemeList();

        if (empty($activeScheme) && !empty($schemeList)) {
            $activeScheme = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DragAndDropAdminPanel($place, $dbComponents);
        $componentPanel->setPositionList($dbPositions);
        $componentPanel->setSettingList($dbSettings);
        $componentPanel->setSchemeList($schemeList);


        if (!empty($activeScheme)) {
            $componentPanel->setScheme($activeScheme);
        }

        $this->assign('componentPanel', $componentPanel->render());
    }
}