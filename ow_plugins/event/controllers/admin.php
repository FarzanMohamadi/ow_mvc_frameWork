<?php

class EVENT_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        OW::getDocument()->setTitle(OW::getLanguage()->text('event', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('event', 'admin_title'));
        $this->setPageHeading(OW::getLanguage()->text('event', 'admin_heading'));

        $componentService = BOL_ComponentAdminService::getInstance();
        $place = EVENT_BOL_EventService::WIDGET_PANEL_NAME;
        $dbSettings = $componentService->findAllSettingList();
        $dbPositions = $componentService->findAllPositionList($place);

        $dbComponents = $componentService->findPlaceComponentList($place);
        $activeScheme = $componentService->findSchemeByPlace($place);
        $schemeList = $componentService->findSchemeList();

        if ( empty($activeScheme) && !empty($schemeList) )
        {
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


        $eventSettingForm = new Form('eventSettingForm');

        $showEventCreatorCheckbox = new CheckboxField('showEventCreatorCheckbox');
        $showEventCreatorCheckbox->setLabel(OW::getLanguage()->text('event', 'show_event_creator'));
        $showEventCreatorCheckbox->setValue(OW::getConfig()->getValue('event', 'showEventCreator'));
        $eventSettingForm->addElement($showEventCreatorCheckbox);

        $eventSettingFormSubmit = new Submit('eventSettingFormSubmit');
        $eventSettingFormSubmit->setValue(OW::getLanguage()->text('event', 'event_admin_form_submit'));
        $eventSettingForm->addElement($eventSettingFormSubmit);

        $this->addForm($eventSettingForm);

        if (OW::getRequest()->isPost()) {
            if ($eventSettingForm->isValid($_POST)) {
                $data = $eventSettingForm->getValues();

                if (!isset($data["showEventCreatorCheckbox"])) {
                    OW::getConfig()->saveConfig('event', 'showEventCreator', 0);
                } else {
                    OW::getConfig()->saveConfig('event', 'showEventCreator', 1);
                }

                OW::getFeedback()->info(OW::getLanguage()->text('event', 'event_submit_successful_message'));
                $this->redirect();
            }
        }
    }

}
