<?php
class FRMWIDGETPLUS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('frmwidgetplus', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmwidgetplus', 'index_page_heading'));
        $form = new Form('rateWidgetView');
        $language = OW::getLanguage();

        $whoCanView = new RadioField('rate_widget_display');
        $whoCanView->addOptions(array('1' => $language->text('frmwidgetplus', 'all_users'), '2' => $language->text('frmwidgetplus', 'only_login_users')));
        $whoCanView->setLabel($language->text('frmwidgetplus', 'display_rate_widget'));
        $whoCanView->setValue(OW::getConfig()->getValue('frmwidgetplus', 'displayRateWidget'));
        $form->addElement($whoCanView);

        $select2CheckboxField = new CheckboxField('activate_select2_checkbox');
        $select2CheckboxField->setLabel($language->text('frmwidgetplus', 'activate_select2_checkbox_label'));
        $select2CheckboxField->setValue(OW::getConfig()->getValue('frmwidgetplus', 'add_select2'));
        $form->addElement($select2CheckboxField);

        $submit = new Submit('save');
        $submit->setValue($language->text('admin', 'save_btn_label'));
        $form->addElement($submit);

        $this->addForm($form);
        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();
            OW::getConfig()->saveConfig('frmwidgetplus', 'displayRateWidget', $values["rate_widget_display"]);
            OW::getConfig()->saveConfig('frmwidgetplus', 'add_select2', isset($values["activate_select2_checkbox"])? 1 : 0);
            OW::getFeedback()->info($language->text('frmwidgetplus', 'settings_updated'));
        }
    }
}