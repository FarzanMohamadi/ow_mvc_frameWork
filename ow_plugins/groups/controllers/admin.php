<?php
/**
 * Group Admin
 *
 * @package ow_plugins.groups.controllers
 * @since 1.0
 */
class GROUPS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function getMenu()
    {
        $item[0] = new BASE_MenuItem(array());

        $item[0]->setLabel(OW::getLanguage()->text('groups', 'general_settings'));
        $item[0]->setIconClass('ow_ic_dashboard ow_dynamic_color_icon');
        $item[0]->setKey('1');

        $item[0]->setUrl(
            OW::getRouter()->urlForRoute('groups-admin-widget-panel')
        );

        $item[0]->setOrder(1);

        $item[1] = new BASE_MenuItem(array());

        $item[1]->setLabel(OW::getLanguage()->text('groups', 'additional_features'));
        $item[1]->setIconClass('ow_ic_files ow_dynamic_color_icon');
        $item[1]->setKey('2');
        $item[1]->setUrl(
            OW::getRouter()->urlForRoute('groups-admin-additional-features')
        );

        $item[1]->setOrder(2);

        return new BASE_CMP_ContentMenu($item);
    }

    public function panel()
    {

        $componentService = BOL_ComponentAdminService::getInstance();

        $this->setPageHeading(OW::getLanguage()->text('groups', 'admin_menu_title'));
        $this->setPageTitle(OW::getLanguage()->text('groups', 'admin_menu_title'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = GROUPS_BOL_Service::WIDGET_PANEL_NAME;

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


        if ( !empty($activeScheme) )
        {
            $componentPanel->setScheme($activeScheme);
        }

        $menu = $this->getMenu();

        $this->addComponent('menu', $menu);

        $this->assign('componentPanel', $componentPanel->render());
    }

    public function connect_forum()
    {
        $config = OW::getConfig();
        $language = OW::getLanguage();

        if ( $_GET['isForumConnected'] === 'yes' && !OW::getConfig()->getValue('groups', 'is_forum_connected') )
        {
            try
            {
                OW::getAuthorization()->addAction('groups', 'add_topic');
            }
            catch ( Exception $e ){}

            // Add forum section
            $event = new OW_Event('forum.create_section', array('name' => 'Groups', 'entity' => 'groups', 'isHidden' => true));
            OW::getEventManager()->trigger($event);

            // Add widget
            $event = new OW_Event('forum.add_widget', array('place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_RIGHT));
            OW::getEventManager()->trigger($event);

            $groupsService = GROUPS_BOL_Service::getInstance();

            $groupList = $groupsService->findGroupList(GROUPS_BOL_Service::LIST_ALL);
            if ( !empty($groupList) )
            {
                foreach ( $groupList as $group )
                {
                    // Add forum group
                    $event = new OW_Event('forum.create_group', array('entity' => 'groups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
                    OW::getEventManager()->trigger($event);
                }
            }

            $config->saveConfig('groups', 'is_forum_connected', 1);
            OW::getFeedback()->info($language->text('groups', 'forum_connected'));
        }

        $redirectURL = OW::getRouter()->urlForRoute('groups-admin-widget-panel');
        $this->redirect($redirectURL);
    }

    public function addTelegramWidget()
    {
        if (OW_PluginManager::getInstance()->isPluginActive('frmtelegram')) {
            if(!OW::getConfig()->configExists('groups', 'is_telegram_connected')) {
                OW::getConfig()->saveConfig('groups', 'is_telegram_connected', 1);

                $event = new OW_Event('frmtelegram.add_widget', array('place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_LEFT));
                OW::getEventManager()->trigger($event);
            }
        }
        $redirectURL = OW::getRouter()->urlForRoute('groups-admin-widget-panel');
        $this->redirect($redirectURL);
    }

    public function addIisGroupsPlusWidget()
    {
        if (OW_PluginManager::getInstance()->isPluginActive('frmgroupsplus')) {
            if(!OW::getConfig()->configExists('groups', 'is_frmgroupsplus_connected') || !OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected')) {
                OW::getConfig()->saveConfig('groups', 'is_frmgroupsplus_connected', 1);
                $event = new OW_Event('frmgroupsplus.add_widget', array('place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_LEFT));
                OW::getEventManager()->trigger($event);
            }
        }
        $redirectURL = OW::getRouter()->urlForRoute('groups-admin-widget-panel');
        $this->redirect($redirectURL);
    }

    public function addInstagramWidget()
    {
        if (OW_PluginManager::getInstance()->isPluginActive('frminstagram')) {
            if(!OW::getConfig()->configExists('groups', 'is_instagram_connected')) {
                OW::getConfig()->saveConfig('groups', 'is_instagram_connected', 1);

                $event = new OW_Event('frminstagram.add_widget', array('place' => 'group', 'section' => BOL_ComponentAdminService::SECTION_LEFT));
                OW::getEventManager()->trigger($event);
            }
        }
        $redirectURL = OW::getRouter()->urlForRoute('groups-admin-widget-panel');
        $this->redirect($redirectURL);
    }

    public function additional()
    {
        $this->setPageHeading(OW::getLanguage()->text('groups', 'widgets_panel_heading'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');



        $enableQRGroupForm = new Form('enableQRGroupForm');
        $enableQRSearch = new CheckboxField('enableQRSearch');
        $enableQRSearch ->setLabel(OW::getLanguage()->text('groups', 'enable_QRSearch'));
        $enableQRSearch ->setValue(OW::getConfig()->getValue('groups', 'enable_QRSearch'));
        $enableQRGroupForm->addElement($enableQRSearch);

        $submitEnableQRSearch = new Submit('submitEnableQRSearch');
        $submitEnableQRSearch->setValue(OW::getLanguage()->text('groups', 'enable_QRSearch_submit'));
        $enableQRGroupForm->addElement($submitEnableQRSearch);
        $this->addForm($enableQRGroupForm);

        if (OW::getRequest()->isPost()) {
            if ($enableQRGroupForm->isValid($_POST)) {
                $data = $enableQRGroupForm->getValues();

                if (!isset($data["enableQRSearch"])) {
                    OW::getConfig()->saveConfig('groups', 'enable_QRSearch', 0);
                } else {
                    OW::getConfig()->saveConfig('groups', 'enable_QRSearch', 1);
                }
                OW::getFeedback()->info(OW::getLanguage()->text('groups', 'edit_success_msg'));
            }
        }

        //forum
        $is_forum_connected = OW::getConfig()->getValue('groups', 'is_forum_connected');

        if ( OW::getPluginManager()->isPluginActive('forum') || $is_forum_connected )
        {
            $this->assign('isForumConnected', $is_forum_connected);
            $this->assign('isForumAvailable', true);
        }
        else
        {
            $this->assign('isForumAvailable', false);
        }

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( OW::getConfig()->getValue('groups', 'restore_groups_forum') )
        {
            // Add forum section
            $event = new OW_Event('forum.create_section', array('name' => 'Groups', 'entity' => 'groups', 'isHidden' => true));
            OW::getEventManager()->trigger($event);

            $groupsService = GROUPS_BOL_Service::getInstance();

            $groupList = $groupsService->findGroupList(GROUPS_BOL_Service::LIST_ALL);
            if ( !empty($groupList) )
            {
                foreach ( $groupList as $group )
                {
                    // Add forum group
                    $event = new OW_Event('forum.create_group', array('entity' => 'groups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
                    OW::getEventManager()->trigger($event);
                }
            }

            OW::getConfig()->saveConfig('groups', 'restore_groups_forum', 0);
        }

        //---telegram
        $is_telegram_connected = OW::getConfig()->getValue('groups', 'is_telegram_connected');
        if ( OW::getPluginManager()->isPluginActive('frmtelegram'))
        {
            $this->assign('isTelegramConnected', $is_telegram_connected);
            $this->assign('isTelegramAvailable', true);
        }
        else
        {
            $this->assign('isTelegramAvailable', false);
            OW::getConfig()->deleteConfig('groups', 'is_telegram_connected');
        }

        //---instagram
        $is_instagram_connected = OW::getConfig()->getValue('groups', 'is_instagram_connected');
        if ( OW::getPluginManager()->isPluginActive('frminstagram'))
        {
            $this->assign('isInstagramConnected', $is_instagram_connected);
            $this->assign('isInstagramAvailable', true);
        }
        else
        {
            $this->assign('isInstagramAvailable', false);
            OW::getConfig()->deleteConfig('groups', 'is_instagram_connected');
        }

        $is_frmgroupsplus_connected = OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected');
        if ( OW::getPluginManager()->isPluginActive('frmgroupsplus'))
        {
            $this->assign('isFRMGroupsPlusConnected', $is_frmgroupsplus_connected);
            $this->assign('isFRMGroupsPlusAvailable', true);
        }
        else
        {
            $this->assign('isFRMGroupsPlusAvailable', false);
            OW::getConfig()->deleteConfig('groups', 'is_frmgroupsplus_connected');
        }
    }

    public function uninstall()
    {
        $config = OW::getConfig();

        if ( !$config->configExists('groups', 'uninstall_inprogress') )
        {
            $config->addConfig('groups', 'uninstall_inprogress', 0);
        }

        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            $config->saveConfig('groups', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(GROUPS_BOL_Service::EVENT_UNINSTALL_IN_PROGRESS));
            OW::getFeedback()->info(OW::getLanguage()->text('groups', 'plugin_set_for_uninstall'));

            OW::getApplication()->setMaintenanceMode(true);

            $this->redirect();
        }

        $this->setPageHeading(OW::getLanguage()->text('groups', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');

        $inprogress = $config->getValue('groups', 'uninstall_inprogress');
        $this->assign('inprogress', $inprogress);

        $js = new UTIL_JsGenerator();
        $js->jQueryEvent('#btn-delete-content', 'click', 'return confirm_redirect("' . OW::getLanguage()->text('groups', 'confirm_delete_groups') . '", data.url);');

        OW::getDocument()->addOnloadScript($js);
    }

    public function disconnect_forum()
    {
        $config = OW::getConfig();
        $language = OW::getLanguage();

        if(!OW::getConfig()->getValue('groups', 'is_forum_connected')){
            $redirectURL = OW::getRouter()->urlForRoute('groups-admin-additional-features');
            $this->redirect($redirectURL);
        }

        // delete forum section
        $event = new OW_Event('forum.delete_section', array('name' => 'Groups', 'entity' => 'groups'));
        OW::getEventManager()->trigger($event);

        // delete widget
        $event = new OW_Event('forum.delete_widget');
        OW::getEventManager()->trigger($event);
        $config->saveConfig('groups', 'is_forum_connected', 0);

        OW::getFeedback()->info($language->text('groups', 'forum_disconnected'));
        $redirectURL = OW::getRouter()->urlForRoute('groups-admin-additional-features');
        $this->redirect($redirectURL);
    }
}