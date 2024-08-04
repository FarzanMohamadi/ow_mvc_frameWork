<?php

/**
 * update server admin action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.frmupdateserver.controllers
 * @since 1.0
 */
class FRMUPDATESERVER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param array $params
     */
    public function index(array $params = array())
    {
        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmupdateserver', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmupdateserver', 'admin_page_title'));
        $service = FRMUPDATESERVER_BOL_Service::getInstance();

        $config = OW::getConfig();
        $form = new Form('setting');
        $form->setMethod(Form::METHOD_POST);
        $form->setAction(OW::getRouter()->urlForRoute('frmupdateserver.admin'));

        $prefixDownloadUrl = new TextField('prefix_download_path');
        $prefixDownloadUrl->setValue($config->getValue('frmupdateserver', 'prefix_download_path'));
        $prefixDownloadUrl->setRequired();
        $prefixDownloadUrl->setLabel(OW::getLanguage()->text('frmupdateserver', 'prefix_download_path_label'));
        $prefixDownloadUrl->setHasInvitation(false);
        $form->addElement($prefixDownloadUrl);

        $submitField = new Submit('submit');
        $form->addElement($submitField);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $config->saveConfig('frmupdateserver', 'prefix_download_path', $form->getElement('prefix_download_path')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'saved_successfully'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin'));
        }

        $deleteCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>OW::getUser()->getId(),'isPermanent'=>true,'activityType'=>'frmupdateserver_delete_all_server_files')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $deleteCode = $frmSecuritymanagerEvent->getData()['code'];
        }

        $updateCode='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>OW::getUser()->getId(),'isPermanent'=>true,'activityType'=>'frmupdateserver_update_server_files')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $updateCode = $frmSecuritymanagerEvent->getData()['code'];
        }

        $deleteUrl=OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('server.delete_all_versions'),array('code' => $deleteCode));

        $updateUrl=OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('server.check_all_for_update'),array('code' => $updateCode));

        $this->assign('update_version_url', $updateUrl);
        $this->assign('delete_all_versions_url', $deleteUrl);
        $this->assign('add_item', OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'));
        $this->assign('plugin_items', OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => 'plugin')));
        $this->assign('theme_items', OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => 'theme')));
        $this->assign('categories', OW::getRouter()->urlForRoute('frmupdateserver.admin.categories', array()));
        $this->assign('sections',$service->getAdminSections($service->SETTINGS_SECTION));

        $cssDir = OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmupdateserver.css");
    }

    /**
     * @param array $params
     */
    public function addItem(array $params = array())
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();

        $form = $service->getItemForm(OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'));
        $this->addForm($form);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $item = $service->getItemByKey($_REQUEST['key']);
                $imageName = $service->saveFile('image');
                if($imageName == null && ($item == null || ($item != null && $item->image==null))){
                    OW::getFeedback()->error(OW::getLanguage()->text('base', 'upload_file_no_file_error'));
                }else {
                    if($item != null && $item->image!=null && $imageName == null){
                        $imageName = $item->image;
                    }
                    $item = $service->addItem($_REQUEST['name'], $_REQUEST['description'], $_REQUEST['key'], $imageName, $_REQUEST['type'], $_REQUEST['guidelineurl']);
                    if ($item == null) {
                        OW::getFeedback()->error(OW::getLanguage()->text('frmupdateserver', 'item_not_exist'));
                        $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'));
                    } else {
                        FRMUPDATESERVER_BOL_PluginInformationDao::getInstance()->addCategoryToItem( $item->id ,$_POST['categoryFieldCheck']);
                        OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'saved_successfully'));
                        $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => $item->type)));
                    }
                }
            }
        }
        $this->assign('sections',$service->getAdminSections($service->ADD_ITEM_SECTION));
    }

    public function deleteItemByNameAndBuildNumber(array $params = array())
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();
        $this->setPageTitle(OW::getLanguage()->text('frmupdateserver', 'admin_delete_item_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmupdateserver', 'admin_delete_item_title'));
        $form = $service->getDeleteItemForm();
        $this->addForm($form);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $item = $service->getItemByKeyAndBuildNumber($_REQUEST['key'],$_REQUEST['build']);
                $result= $service->deleteItemByIDAndBuildNumAndKey($item,$_REQUEST['build'],$_REQUEST['key']);
                if ($result) {
                    OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'item_deleted_successfully'));
                } else {
                    OW::getFeedback()->error(OW::getLanguage()->text('frmupdateserver', 'item_not_exist'));
                }
            }
        }
        $this->assign('sections',$service->getAdminSections($service->DELETE_ITEM_SECTION));
    }

    public function checkUpdateItemAvailableByName(array $params = array())
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();
        $this->setPageTitle(OW::getLanguage()->text('frmupdateserver', 'admin_check_item_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmupdateserver', 'admin_check_item_title'));
        $form = $service->getCheckItemForm();
        $this->addForm($form);

        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $findKey = false;
                $rootZipDirectory = OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir();

                $xmlPlugins = BOL_PluginService::getInstance()->getPluginsXmlInfo();
                foreach ($xmlPlugins as $plugin) {
                    if (!in_array($plugin['key'], FRMUPDATESERVER_BOL_Service::getInstance()->getIgnorePluginsKeyList()) && strcmp($plugin['key'], $_REQUEST['key']) == 0) {
                        $findKey = true;
                        if (!OW::getStorage()->fileExists($rootZipDirectory . 'plugins')) {
                            OW::getStorage()->mkdir($rootZipDirectory . 'plugins');
                        }
                        $dir = $plugin['path'];
                        FRMUPDATESERVER_BOL_Service::getInstance()->checkPluginForUpdate($plugin['key'], $plugin['build'], $dir, $rootZipDirectory);
                        OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'items_checked_successfully'));
                    }
                }

                $themes = UTIL_File::findFiles(OW_DIR_THEME, array('xml'), 1);
                foreach ($themes as $themeXml) {
                    if ( basename($themeXml) === BOL_ThemeService::THEME_XML) {
                        $theme = simplexml_load_file($themeXml);
                        if(!in_array((string) $theme->key, FRMUPDATESERVER_BOL_Service::getInstance()->getIgnoreThemesKeyList())&& strcmp($theme->key, $_REQUEST['key']) == 0) {
                            $findKey = true;
                            if (!OW::getStorage()->fileExists($rootZipDirectory . 'themes')) {
                                OW::getStorage()->mkdir($rootZipDirectory . 'themes');
                            }
                            FRMUPDATESERVER_BOL_Service::getInstance()->checkThemeForUpdate((string)$theme->key, (string)$theme->build, $rootZipDirectory);
                            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'items_checked_successfully'));
                            break;
                        }
                    }
                }

                if(strcmp('core', $_REQUEST['key']) == 0) {
                    $findKey = true;
                    FRMUPDATESERVER_BOL_Service::getInstance()->checkCoreForUpdate($rootZipDirectory);
                    OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'items_checked_successfully'));
                }

                if(!$findKey) {
                    OW::getFeedback()->error(OW::getLanguage()->text('frmupdateserver', 'item_not_exist'));
                }
            }
        }
        $this->assign('sections',$service->getAdminSections($service->CHECK_ITEM_SECTION));
    }

    /**
     * @param array $params
     */
    public function ajaxSaveItemsOrder(array $params = array()){
        if (!empty($_POST['items']) && is_array($_POST['items'])) {
            $service = FRMUPDATESERVER_BOL_Service::getInstance();
            foreach ($_POST['items'] as $index => $id) {
                $item = $service->getItemById($id);
                $item->order = $index + 1;
                $service->saveItem($item);
            }
        }
    }

    /**
     * @param array $params
     */
    public function editItem(array $params = array())
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();

        if(!isset($params['id'])){
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'));
        }else{
            $item = $service->getItemById($params['id']);
            if($item==null){
                $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'));
            }else{
                $category=FRMUPDATESERVER_BOL_PluginInformationDao::getInstance()->getItemInformationById($item->id);
                $categories=json_decode($category->categories);
                $form = $service->getItemForm(OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'), $item->name, $item->description, $item->key, $item->type, $item->guidelineurl,$categories, $item->image);
                $this->addForm($form);
                if(isset($item->image)){
                    $this->assign('oldIconSrc', OW::getPluginManager()->getPlugin('frmupdateserver')->getUserFilesUrl() . $item->image);
                }
                $this->assign('returnUrl', OW::getRouter()->urlForRoute('frmupdateserver.admin'));
            }
        }

    }


    /**
     * @param array $params
     */
    public function deleteItem(array $params = array())
    {
        if(isset($params['id'])){
            $service = FRMUPDATESERVER_BOL_Service::getInstance();
            $item = $service->deleteItem($params['id']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'removed_successfully'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => $item->type)));
        }
    }

    /**
     * @param array $params
     */
    public function items(array $params = array())
    {

        $service = FRMUPDATESERVER_BOL_Service::getInstance();
        $items = array();
        $itemsInformation = array();
        if(isset($params['type'])){
            $items = $service->getItems($params['type']);
            $this->assign('typeLabel', OW::getLanguage()->text('frmupdateserver', $params['type'].'s'));
        }else{
            $items = $service->getItems();
        }

        foreach($items as $item){
            $itemInformation = array();
            $itemInformation['id'] = $item->id;
            $itemInformation['name'] = $item->name;
            $itemInformation['deleteUrl'] = OW::getRouter()->urlForRoute('frmupdateserver.admin.delete.item', array('id' => $item->id));
            $itemInformation['editUrl'] = OW::getRouter()->urlForRoute('frmupdateserver.admin.edit.item', array('id' => $item->id));
            $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getUserFilesUrl() . $item->image;
            $itemsInformation[] = $itemInformation;
        }
        $this->assign('items', $itemsInformation);
        $this->assign('sections',$service->getAdminSections($params['type']=='plugin'?$service->PLUGIN_ITEMS_SECTION:$service->THEME_ITEMS_SECTION));

        $cssDir = OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmupdateserver.css");
    }



    public function itemCategory($params)
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();
        $this->assign("sections", $service->getAdminSections($service->PLUGIN_CATEGORY));
        $this->setPageHeading(OW::getLanguage()->text('frmupdateserver', 'admin_settings_categories'));
        $this->setPageTitle(OW::getLanguage()->text('frmupdateserver', 'admin_settings_categories'));

        $deleteUrls = array();
        $pluginListCategory = array();
        $PluginsCategories = $service->getPluginCategoryList();
        $editUrls = [];
        foreach ($PluginsCategories as $pluginCategory) {
            $editUrls[$pluginCategory->id] =  "OW.ajaxFloatBox('FRMUPDATESERVER_CMP_EditItemFloatBox', {id: ".$pluginCategory->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmupdateserver', 'edit_item_page_title')."'})";
            $pluginListCategory[$pluginCategory->id]['name'] = $pluginCategory->id;
            $pluginListCategory[$pluginCategory->id]['label'] = $pluginCategory->label;
            $deleteUrls[$pluginCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $pluginCategory->id));
        }
        $this->assign('pluginListCategory', $pluginListCategory);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('editUrls',$editUrls);

        $form = new Form('add_category');
        $this->addForm($form);

        $service->addCategoryField($form);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('frmupdateserver', 'form_add_category_submit'));
        $form->addElement($submit);
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                $service->addItemCategory($data['label']);
                $this->redirect();
            }
        }
    }

    public function getService(){
        return FRMUPDATESERVER_BOL_Service::getInstance();
    }


    public function delete( $params )
    {
        if ( isset($params['id']))
        {
            FRMUPDATESERVER_BOL_Service::getInstance()->deleteItemCategory((int) $params['id']);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'database_record_removed'));
        $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.categories'));
    }

    public function editCategoryItem()
    {
        if (isset($_POST)) {
            $this->getService()->editCategoryItem($_POST['id'], $_POST['label']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.categories'));
        }else{
            OW::getFeedback()->error(text('frmupdateserver', 'database_record_edit_fail'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin.categories'));
        }
    }

    
    
    
}