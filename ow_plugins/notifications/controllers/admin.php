<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.notifications
 */
class NOTIFICATIONS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('notifications', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('notifications', 'admin_settings_heading'));

        $form = new Form("form");

        $textField = new TextField('delete_days_for_viewed');
        $textField->setLabel(OW::getLanguage()->text('notifications', 'delete_days_for_viewed'))
            ->setValue(OW::getConfig()->getValue('notifications','delete_days_for_viewed', 7))
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $form->addElement($textField);

        $textField = new TextField('delete_days_for_not_viewed');
        $textField->setLabel(OW::getLanguage()->text('notifications', 'delete_days_for_not_viewed'))
            ->setValue(OW::getConfig()->getValue('notifications','delete_days_for_not_viewed', 60))
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $form->addElement($textField);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('notifications', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $delete_days_for_viewed = max(1, intval($data['delete_days_for_viewed']));
            $delete_days_for_not_viewed = max(1, intval($data['delete_days_for_not_viewed']));
            OW::getConfig()->saveConfig('notifications', 'delete_days_for_viewed', $delete_days_for_viewed);
            OW::getConfig()->saveConfig('notifications', 'delete_days_for_not_viewed', $delete_days_for_not_viewed);

            OW::getFeedback()->info(OW::getLanguage()->text('notifications', 'admin_changed_success'));
        }

        $this->addForm($form);
    }

}