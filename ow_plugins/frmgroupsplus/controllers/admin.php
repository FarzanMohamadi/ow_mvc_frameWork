<?php
/**
 * Admin page
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.controllers
 * @since 1.0
 */
class FRMGROUPSPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function groupCategory($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmgroupsplus', 'admin_groupplus_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmgroupsplus', 'admin_category_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmgroupsplus', 'admin_category_heading'));
        $deleteUrls = array();
        $groupListCategory = array();
        $groupCategories = FRMGROUPSPLUS_BOL_Service::getInstance()->getGroupCategoryList();
        $editUrls = [];
        foreach ($groupCategories as $groupCategory) {
            $editUrls[$groupCategory->id] =  "OW.ajaxFloatBox('FRMGROUPSPLUS_CMP_EditItemFloatBox', {id: ".$groupCategory->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmgroupsplus', 'edit_item_page_title')."'})";
            /* @var $contact FRMGROUPSPLUS_BOL_Category */
            $groupListCategory[$groupCategory->id]['name'] = $groupCategory->id;
            $groupListCategory[$groupCategory->id]['label'] = $groupCategory->label;
            $deleteUrls[$groupCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $groupCategory->id));
        }
        $this->assign('groupListCategory', $groupListCategory);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('editUrls',$editUrls);
        $form = new Form('add_category');
        $this->addForm($form);

        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(OW::getLanguage()->text('frmgroupsplus', 'label_category_label'));
        $fieldLabel->setHasInvitation(true);
        $validator = new FRMGROUPSPLUS_CLASS_LabelValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmgroupsplus', 'label_error_already_exist'));
        $fieldLabel->addValidator($validator);
        $form->addElement($fieldLabel);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('frmgroupsplus', 'form_add_category_submit'));
        $form->addElement($submit);
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                FRMGROUPSPLUS_BOL_Service::getInstance()->addGroupCategory($data['label']);
                $this->redirect();
            }
        }

        $notificationForm = new Form('notification_settings');
        $this->addForm($notificationForm);
        $fileUploadFeed= new checkBoxGroup('fileUploadFeed');
        $fileUploadFeed->addOption('fileFeed',OW::getLanguage()->text('frmgroupsplus', 'file_feed_label'));
        $fileUploadFeed->addOption('joinFeed',OW::getLanguage()->text('frmgroupsplus', 'join_feed_label'));
        $fileUploadFeed->addOption('leaveFeed',OW::getLanguage()->text('frmgroupsplus', 'leave_feed_label'));
        $fileUploadFeedValue= OW::getConfig()->getValue('frmgroupsplus','groupFileAndJoinAndLeaveFeed');
        if(isset($fileUploadFeedValue) && $fileUploadFeedValue !=null ){
            $fileUploadFeed->setValue(json_decode($fileUploadFeedValue));
        }
        $fileUploadFeed->setLabel(OW::getLanguage()->text('frmgroupsplus', 'group_file_and_join_feed_label'));
        $config = OW::getConfig();
        $notificationForm->addElement($fileUploadFeed);
        $fileUploadInCreateGroup = new CheckboxField('fileUploadSettings');
        $fileUploadInCreateGroup->setLabel(OW::getLanguage()->text('frmgroupsplus', 'show_file_upload_settings'));
        $fileUploadInCreateGroup->setValue($config->getValue('frmgroupsplus', 'showFileUploadSettings'));
        $notificationForm->addElement($fileUploadInCreateGroup);

        $createTopicInCreateGroup = new CheckboxField('createTopicSettings');
        $createTopicInCreateGroup->setLabel(OW::getLanguage()->text('frmgroupsplus', 'show_add_topic_settings'));
        $createTopicInCreateGroup->setValue($config->getValue('frmgroupsplus', 'showAddTopic'));
        $notificationForm->addElement($createTopicInCreateGroup);

        $createTopicInCreateGroup = new CheckboxField('groupApproveStatus');
        $createTopicInCreateGroup->setLabel(OW::getLanguage()->text('frmgroupsplus', 'group_approve_status'));
        $createTopicInCreateGroup->setValue($config->getValue('frmgroupsplus', 'groupApproveStatus'));
        $notificationForm->addElement($createTopicInCreateGroup);

        $notificationFormSubmit = new Submit('notificationFormAdd');
        $notificationFormSubmit->setValue(OW::getLanguage()->text('frmgroupsplus', 'group_file_and_join_feed_submit'));
        $notificationForm->addElement($notificationFormSubmit);
        if (OW::getRequest()->isPost()) {
            if ($notificationForm->isValid($_POST)) {
                $data = $notificationForm->getValues();
                if(!empty($data['fileUploadFeed'])) {
                    OW::getConfig()->saveConfig('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed', json_encode($data['fileUploadFeed']));
                }else{
                    OW::getConfig()->saveConfig('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed',json_encode([]));
                }

                if(!isset($data["fileUploadSettings"]))
                   $config->saveConfig('frmgroupsplus','showFileUploadSettings',0);
                else
                    $config->saveConfig('frmgroupsplus','showFileUploadSettings',1);

                if(!isset($data["createTopicSettings"]))
                    $config->saveConfig('frmgroupsplus','showAddTopic',0);
                else
                    $config->saveConfig('frmgroupsplus','showAddTopic',1);

                if(!isset($data["groupApproveStatus"]))
                    $config->saveConfig('frmgroupsplus','groupApproveStatus',0);
                else
                    $config->saveConfig('frmgroupsplus','groupApproveStatus',1);

                $this->redirect();
            }
        }
    }

    public function getService(){
        return FRMGROUPSPLUS_BOL_Service::getInstance();
    }


    public function delete( $params )
    {
        if ( isset($params['id']))
        {
            FRMGROUPSPLUS_BOL_Service::getInstance()->deleteGroupCategory((int) $params['id']);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmgroupsplus', 'database_record_edit'));
        $this->redirect(OW::getRouter()->urlForRoute('frmgroupsplus.admin'));
    }

    public function editItem()
    {
        $form = $this->getService()->getItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
           $this->getService()->editItem($form->getElement('id')->getValue(), $form->getElement('label')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmgroupsplus', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmgroupsplus.admin'));
        }else{
            if($form->getErrors()['label'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['label'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmgroupsplus.admin'));
        }
    }

}
