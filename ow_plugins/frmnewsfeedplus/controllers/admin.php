<?php
/**
 * frmnewsfeedplus admin action controller
 *
 */
class FRMNEWSFEEDPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param array $params
     */
    public function index(array $params = array())
    {
        $this->setPageHeading(OW::getLanguage()->text('frmnewsfeedplus', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmnewsfeedplus', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');


        $selectBox = new Selectbox('newsfeed_order_list');
        $options = array();
        $options[FRMNEWSFEEDPLUS_BOL_Service::ORDER_BY_ACTIVITY] = $language->text('frmnewsfeedplus', 'sort_by_activity');
        $options[FRMNEWSFEEDPLUS_BOL_Service::ORDER_BY_ACTION] = $language->text('frmnewsfeedplus', 'sort_by_action');
        $selectBox->setOptions($options);
        $selectBox->setRequired(true);
        $selectBox->setLabel($language->text('frmnewsfeedplus','newsfeed_list_display_order'));
        $form->addElement($selectBox);

        $enableQRSearch = new CheckboxField('enableQRSearch');
        $enableQRSearch ->setLabel(OW::getLanguage()->text('frmnewsfeedplus', 'enable_QRSearch'));
        $enableQRSearch ->setValue(OW::getConfig()->getValue('frmnewsfeedplus', 'enable_QRSearch'));
        $form->addElement($enableQRSearch);


        $allowSortField = new CheckboxField('allow_sort');
        $allowSortField->setLabel($language->text('frmnewsfeedplus', 'admin_allow_sort_label'));
        $form->addElement($allowSortField);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            if ( $config->configExists('frmnewsfeedplus', 'newsfeed_list_order') )
            {
                $config->saveConfig('frmnewsfeedplus', 'newsfeed_list_order',$data['newsfeed_order_list']);
            }
            if ( $config->configExists('frmnewsfeedplus', 'allow_sort') )
            {
                $config->saveConfig('frmnewsfeedplus', 'allow_sort',$data['allow_sort']);
            }

            if (!isset($data["enableQRSearch"])) {
                OW::getConfig()->saveConfig('frmnewsfeedplus', 'enable_QRSearch', 0);
            } else {
                OW::getConfig()->saveConfig('frmnewsfeedplus', 'enable_QRSearch', 1);
            }

            OW::getFeedback()->info($language->text('frmnewsfeedplus', 'modified_successfully'));
            $this->redirect();
        }
        if($config->configExists('frmnewsfeedplus', 'newsfeed_list_order')) {
            $selectBox->setValue($config->getValue('frmnewsfeedplus', 'newsfeed_list_order'));
            $allowSortField->setValue($config->getValue('frmnewsfeedplus','allow_sort'));
        }
    }

}