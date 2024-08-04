<?php
/**
 * frmforumplus admin action controller
 *
 */
class FRMFORUMPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param array $params
     */
    public function index(array $params = array())
    {
        $this->setPageHeading(OW::getLanguage()->text('frmforumplus', 'admin_settings_heading'));
        $this->setPageTitle(OW::getLanguage()->text('frmforumplus', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        $config =  OW::getConfig();
        $language = OW::getLanguage();

        $form = new Form('form');

        $field = new CheckboxField('mobile_forum_group_visibile');
        $field->setLabel($language->text('frmforumplus', 'mobile_forum_group_visibile_label'));
        $form->addElement($field);

        $subscribeGroupUsersCheckbox = new CheckboxField('subscribe_group_users_to_topic_checkbox');
        $subscribeGroupUsersCheckbox->setLabel($language->text('frmforumplus', 'subscribe_group_users_to_topic_checkbox_label'));
        $form->addElement($subscribeGroupUsersCheckbox);

        $submit = new Submit('save');
        $form->addElement($submit);
        $this->addForm($form);

        $headerTextArea = new Textarea('headerForumGroupWidgetHtml');
        $headerTextArea->setLabel(OW::getLanguage()->text('frmforumplus', 'header_html_forum_group_widget'));
        //$commentTextArea->setValue($configs['HeaderForumGroupWidgetHtml']);
        $form->addElement($headerTextArea);
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            if ( $config->configExists('frmforumplus', 'mobile_forum_group_visibile') )
            {
                $config->saveConfig('frmforumplus', 'mobile_forum_group_visibile', $data['mobile_forum_group_visibile']);
            }
            if ($config->configExists('frmforumplus', 'subscribe_group_users_to_topic'))
            {
                $config->saveConfig('frmforumplus', 'subscribe_group_users_to_topic', $data['subscribe_group_users_to_topic_checkbox']);
            }
            $config->saveConfig('frmforumplus', 'headerForumGroupWidgetHtml', $data['headerForumGroupWidgetHtml']);

            OW::getFeedback()->info(OW::getLanguage()->text('frmforumplus', 'modified_successfully'));
            $this->redirect();
        }
        if($config->configExists('frmforumplus', 'mobile_forum_group_visibile'))
        {
            $field->setValue($config->getValue('frmforumplus', 'mobile_forum_group_visibile'));
        }
        if ( $config->configExists('frmforumplus', 'headerForumGroupWidgetHtml') )
        {
            $headerTextArea->setValue($config->getValue('frmforumplus', 'headerForumGroupWidgetHtml'));
        }
        if ($config->configExists('frmforumplus', 'subscribe_group_users_to_topic'))
        {
            $subscribeGroupUsersCheckbox->setValue($config->getValue('frmforumplus', 'subscribe_group_users_to_topic'));
        }
    }

}