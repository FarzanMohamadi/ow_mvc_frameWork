<?php
class FRMTERMS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index($params)
    {
        $this->setDocumentKey('terms_plugin_settings_page');

        $language = OW::getLanguage();
        $this->setPageHeading($language->text('frmterms', 'admin_page_heading'));
        $this->setPageTitle($language->text('frmterms', 'admin_page_title'));
        $sectionId = 1;
        if(isset($params['sectionId'])){
            $sectionId = $params['sectionId'];
        }

        $service = $this->getService();

        $addItemCMP = new FRMTERMS_CMP_AddItem($sectionId);
        $this->addComponent('addItemCMP',$addItemCMP);

        $allItems = $service->getAllItemSorted($sectionId);

        $activeItems = array();
        $inactiveItems = array();
        $imageDir = OW::getPluginManager()->getPlugin('frmterms')->getStaticUrl().'images/';

        foreach ( $allItems as $item )
        {
            $deleteItemUrl = OW::getRouter()->urlForRoute('frmterms.admin.delete-item', array('id'=>$item->id));
            $deactiveItemUrl = OW::getRouter()->urlForRoute('frmterms.admin.deactivate-item', array('id'=>$item->id));
            $activeItemUrl = OW::getRouter()->urlForRoute('frmterms.admin.activate-item', array('id'=>$item->id));
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$item->id,'isPermanent'=>true,'activityType'=>'delete_item_frmterms')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $deleteItemUrl = OW::getRequest()->buildUrlQueryString($deleteItemUrl,array('code'=>$code));
            }
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$item->id,'isPermanent'=>true,'activityType'=>'deactive_item_frmterms')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $deactiveItemUrl = OW::getRequest()->buildUrlQueryString($deactiveItemUrl,array('code'=>$code));
            }
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$item->id,'isPermanent'=>true,'activityType'=>'active_item_frmterms')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
                $activeItemUrl = OW::getRequest()->buildUrlQueryString($activeItemUrl,array('code'=>$code));
            }
            if($item->use){
                $activeItems[] = array(
                    'langId' => $item->langId,
                    'header' => $item->header,
                    'description' => $item->description,
                    'use' => $item->use,
                    'id' => $item->id,
                    'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmterms','delete_item_warning')."')){location.href='".$deleteItemUrl."';}",
                    'activateUrl' => $activeItemUrl,
                    'deactivateUrl' => $deactiveItemUrl,
                    'editUrl' => "OW.ajaxFloatBox('FRMTERMS_CMP_EditItemFloatBox', {id: ".$item->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmterms', 'edit_item_page_title')."'})",
                    'notification' => (bool) $item->notification,
                    'email' => (bool) $item->email
                );
            }else{
                $inactiveItems[] = array(
                    'langId' => $item->langId,
                    'header' => $item->header,
                    'description' => $item->description,
                    'use' => $item->use,
                    'id' => $item->id,
                    'deleteUrl' => "if(confirm('".OW::getLanguage()->text('frmterms','delete_item_warning')."')){location.href='".$deleteItemUrl."';}",
                    'activateUrl' => $activeItemUrl,
                    'deactivateUrl' => $deactiveItemUrl,
                    'editUrl' => "OW.ajaxFloatBox('FRMTERMS_CMP_EditItemFloatBox', {id: ".$item->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmterms', 'edit_item_page_title')."'})",
                    'notification' => (bool) $item->notification,
                    'email' => (bool) $item->email
                );
            }
        }

        $versionMarked = array();
        $versions = array();
        $maxVersion = $service->getMaxVersion($sectionId);
        $itemsVersioned = $service->getItemsAndVersions($sectionId);

        foreach ($itemsVersioned as $item) {
            if (!in_array($item->version, $versionMarked)) {
                $versionMarked[] = $item->version;

//                $time_temp = date_create();
//                date_timestamp_set($time_temp, $item->time);
//                $time_temp = date_format($time_temp, 'Y-m-d H:i:s');
                $formattedDate = UTIL_DateTime::formatSimpleDate($item->time);
                $current = false;
                if ($item->version == $maxVersion) {
                    $current = true;
                }
                $versions[] = array(
                    'deleteVersionUrl' => "if(confirm('".OW::getLanguage()->text('frmterms','delete_section_warning')."')){location.href='".OW::getRouter()->urlForRoute('frmterms.admin.delete-version', array('sectionId'=>$sectionId, 'version' => $item->version))."';}",
                    'time' => $formattedDate,
                    'url' => OW::getRouter()->urlForRoute('frmterms.comparison-archive', array('sectionId' => $sectionId, 'version' => $item->version)),
                    'current' => $current
                );
            }
        }

        $this->assign("versions", $versions);


        if(OW::getConfig()->getValue('frmterms', 'terms'.$sectionId)){
            $this->assign('sectionStatusChangeUrl',OW::getRouter()->urlForRoute('frmterms.admin.deactivate-section', array('sectionId'=>$sectionId)));
            $this->assign('sectionStatusChangeLabel',OW::getLanguage()->text('frmterms','deactivate_section_button'));
            $this->assign('sectionStatus',OW::getLanguage()->text('frmterms','section_is_active'));
        }else{
            $this->assign('sectionStatusChangeUrl',OW::getRouter()->urlForRoute('frmterms.admin.activate-section', array('sectionId'=>$sectionId)));
            $this->assign('sectionStatusChangeLabel',OW::getLanguage()->text('frmterms','activate_section_button'));
            $this->assign('sectionStatus',OW::getLanguage()->text('frmterms','section_is_inactive'));
        }

        $this->assign('notificationImageSrc', $imageDir . 'notification.png');
        $this->assign('emailImageSrc', $imageDir . 'email.png');
        $this->assign('addVersionUrl', "javascript:if(confirm('".addslashes(OW::getLanguage()->text('frmterms','add_version_warning'))."')){location.href='".OW::getRouter()->urlForRoute('frmterms.admin.add-version', array('sectionId'=>$sectionId))."';}");
        $this->assign('addVersionLabel', OW::getLanguage()->text('frmterms','add_version_label'));
        $this->assign('number_of_exist_version', OW::getLanguage()->text('frmterms','number_of_exist_version',array('value' => $maxVersion)));
        $this->assign('sections', $service->getAdminSections($sectionId));
        $this->assign('activeItems',$activeItems);
        $this->assign('inactiveItems',$inactiveItems);

        if(OW::getConfig()->getValue('frmterms', 'showOnRegistrationForm')){
            $this->assign('showOnJoinFormStatusDescription',  OW::getLanguage()->text('frmterms','terms_show_on_join_form_enable'));
            $this->assign('showOnJoinFormStatus',  OW::getLanguage()->text('frmterms','terms_show_in_join_form_set_disable', array('value' => OW::getRouter()->urlForRoute('frmterms.admin.deactivate-terms-on-join', array('sectionId'=>$sectionId)))));
            $this->assign('showOnJoinFormStatusClass',  'ow_green');
        }else{
            $this->assign('showOnJoinFormStatusDescription',  OW::getLanguage()->text('frmterms','terms_show_on_join_form_disable'));
            $this->assign('showOnJoinFormStatus',  OW::getLanguage()->text('frmterms','terms_show_in_join_form_set_enable', array('value' => OW::getRouter()->urlForRoute('frmterms.admin.activate-terms-on-join', array('sectionId'=>$sectionId)))));
            $this->assign('showOnJoinFormStatusClass',  'ow_red');
        }

        $cssDir = OW::getPluginManager()->getPlugin("frmterms")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "save-ajax-order-item.css");
    }

    public function activateTermsOnJoin($params){
        OW::getConfig()->saveConfig('frmterms', 'showOnRegistrationForm', true);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'terms_show_in_join_form'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$params['sectionId'])) );
    }

    public function deactivateTermsOnJoin($params){
        OW::getConfig()->saveConfig('frmterms', 'showOnRegistrationForm', false);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'terms_hide_in_join_form'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$params['sectionId'])) );
    }

    public function getService(){
        return FRMTERMS_BOL_Service::getInstance();
    }

    public function deleteItem($params)
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_item_frmterms')));
        }
        $item = $this->getService()->deleteItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_deleted'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$item->sectionId)) );
    }

    public function deleteVersion($params)
    {
        $this->getService()->deleteVersion($params['sectionId'], $params['version']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_deleted'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$params['sectionId'])) );
    }

    public function deactivateItem($params)
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'deactive_item_frmterms')));
        }
        $item = $this->getService()->deactivateItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_deactivate_item'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$item->sectionId)) );
    }

    public function activateItem($params)
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'active_item_frmterms')));
        }
        $item = $this->getService()->activateItem($params['id']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_activate_item'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$item->sectionId)) );
    }

    public function deactivateSection($params)
    {
        $this->getService()->deactivateSection($params['sectionId']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_deactivate_section'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$params['sectionId'])) );
    }

    public function activateSection($params)
    {
        $this->getService()->activateSection($params['sectionId']);
        OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_activate_section'));
        $this->redirect( OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$params['sectionId'])) );
    }

    public function addItem($params)
    {
        $form = $this->getService()->getItemForm();
        if ( $form->isValid($_POST) ) {
            $dicc=$_POST['description'];
            $item = $this->getService()->addItem($form->getElement('sectionId')->getValue(),$form->getElement('header')->getValue(),$dicc, $form->getElement('use')->getValue(), $form->getElement('notification')->getValue(), $form->getElement('email')->getValue());

            OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_add'));
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$item->sectionId)));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin'));
        }
    }

    public function addVersion($params)
    {
        $sectionId = $params['sectionId'];
        $items = $this->getService()->getItemsUsingStatus(true,$sectionId);
        if(empty($items)){
            OW::getFeedback()->error(OW::getLanguage()->text('frmterms', 'add_version_without_items'));
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId' => $sectionId)));
        }else{
            $this->getService()->addVersion($sectionId, $items, true);
            OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_add_version'));
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId' => $sectionId)));
        }
    }

    public function editItem()
    {
        $form = $this->getService()->getItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
            $dicc=$_POST['description'];
            $item = $this->getService()->editItem($form->getElement('id')->getValue(), $form->getElement('header')->getValue(), $dicc, $form->getElement('use')->getValue(), $form->getElement('notification')->getValue(), $form->getElement('email')->getValue());

            OW::getFeedback()->info(OW::getLanguage()->text('frmterms', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin.section-id', array('sectionId'=>$item->sectionId)));
        }else{
            $this->redirect(OW::getRouter()->urlForRoute('frmterms.admin'));
        }
    }

    public function ajaxSaveOrder(){
        if ( !empty($_POST['active']) && is_array($_POST['active']) )
        {
            foreach ( $_POST['active'] as $index => $id )
            {
                $item = $this->getService()->getItemById($id);
                $item->order = $index + 1;
                $item->use = true;
                $this->getService()->saveItem($item);
            }
        }

        if ( !empty($_POST['inactive']) && is_array($_POST['inactive']) )
        {
            foreach ( $_POST['inactive'] as $index => $id )
            {
                $item = $this->getService()->getItemById($id);
                $item->order = $index + 1;
                $item->use = false;
                $this->getService()->saveItem($item);
            }
        }
    }

}