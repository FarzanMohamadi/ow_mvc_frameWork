<?php
/**
 * Admin page
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus.controllers
 * @since 1.0
 */
class FRMEVENTPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function eventCategory($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmeventplus', 'admin_eventplus_settings_heading'));
        $service = $this->getService();
        $this->setPageTitle(OW::getLanguage()->text('frmeventplus', 'admin_category_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmeventplus', 'admin_category_heading'));
        $deleteUrls = array();
        $eventListCategory = array();
        $eventCategories = FRMEVENTPLUS_BOL_Service::getInstance()->getEventCategoryList();
        $editUrls = [];
        foreach ($eventCategories as $eventCategory) {
            $editUrls[$eventCategory->id] =  "OW.ajaxFloatBox('FRMEVENTPLUS_CMP_EditItemFloatBox', {id: ".$eventCategory->id."} , {iconClass: 'ow_ic_edit', title: '".OW::getLanguage()->text('frmeventplus', 'edit_item_page_title')."'})";
            /* @var $contact FRMEVENTPLUS_BOL_Category */
            $eventListCategory[$eventCategory->id]['name'] = $eventCategory->id;
            $eventListCategory[$eventCategory->id]['label'] = $eventCategory->label;
            $deleteUrls[$eventCategory->id] = OW::getRouter()->urlFor(__CLASS__, 'delete', array('id' => $eventCategory->id));
        }
        $this->assign('eventListCategory', $eventListCategory);
        $this->assign('deleteUrls', $deleteUrls);
        $this->assign('editUrls',$editUrls);
        $form = new Form('add_category');
        $this->addForm($form);

        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(OW::getLanguage()->text('frmeventplus', 'label_category_label'));
        $fieldLabel->setHasInvitation(true);
        $validator = new FRMEVENTPLUS_CLASS_LabelValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmeventplus', 'label_error_already_exist'));
        $fieldLabel->addValidator($validator);
        $form->addElement($fieldLabel);

        $submit = new Submit('add');
        $submit->setValue(OW::getLanguage()->text('frmeventplus', 'form_add_category_submit'));
        $form->addElement($submit);
        if (OW::getRequest()->isPost()) {
            if ($form->isValid($_POST)) {
                $data = $form->getValues();
                FRMEVENTPLUS_BOL_Service::getInstance()->addEventCategory($data['label']);
                $this->redirect();
            }
        }
    }

    public function getService(){
        return FRMEVENTPLUS_BOL_Service::getInstance();
    }


    public function delete( $params )
    {
        if ( isset($params['id']))
        {
            FRMEVENTPLUS_BOL_Service::getInstance()->deleteEventCategory((int) $params['id']);
        }
        OW::getFeedback()->info(OW::getLanguage()->text('frmeventplus', 'database_record_edit'));
        $this->redirect(OW::getRouter()->urlForRoute('frmeventplus.admin'));
    }

    public function editItem()
    {
        $form = $this->getService()->getItemForm($_POST['id']);
        if ( $form->isValid($_POST) ) {
           $this->getService()->editItem($form->getElement('id')->getValue(), $form->getElement('label')->getValue());
            OW::getFeedback()->info(OW::getLanguage()->text('frmeventplus', 'database_record_edit'));
            $this->redirect(OW::getRouter()->urlForRoute('frmeventplus.admin'));
        }else{
            if($form->getErrors()['label'][0]!=null) {
                OW::getFeedback()->error($form->getErrors()['label'][0]);
            }
            $this->redirect(OW::getRouter()->urlForRoute('frmeventplus.admin'));
        }
    }
}
