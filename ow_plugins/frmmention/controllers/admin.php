<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */
class FRMMENTION_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function __construct()
    {
        parent::__construct();

        $this->setPageHeading(OW::getLanguage()->text('frmmention', 'admin_settings_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
    }

    /**
     * Default action
     */
    public function index()
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmmention', 'admin_settings_heading'));

        $form = new Form("form");

        $textField = new TextField('max_count');
        $textField->setLabel(OW::getLanguage()->text('frmmention', 'max_count'))
            ->setValue(OW::getConfig()->getValue('frmmention','max_count'))
            ->addValidator(new IntValidator())
            ->setRequired(true);
        $form->addElement($textField);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmmention', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            $max_count = intval($data['max_count']);
            if($max_count>20)
                $max_count = 20;
            if($max_count<1)
                $max_count = 1;
            OW::getConfig()->saveConfig('frmmention', 'max_count', $max_count);
            OW::getFeedback()->info(OW::getLanguage()->text('frmmention', 'admin_changed_success'));
        }

        $this->addForm($form);
    }

}