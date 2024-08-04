<?php
/**
 * frmclamav
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmclamav
 * @since 1.0
 */

class FRMCLAMAV_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function index($params)
    {
        OW::getDocument()->setTitle(OW::getLanguage()->text('frmclamav', 'admin_settings_heading'));
        OW::getDocument()->setHeading(OW::getLanguage()->text('frmclamav', 'admin_settings_heading'));

        $form = new Form("form");
        $configs = OW::getConfig()->getValues('frmclamav');

        $checkFieldPermission = new CheckboxField('unknownFilePermission');
        $checkFieldPermission->setLabel(OW::getLanguage()->text('frmclamav', 'unknown_files_permission'))->setValue($configs['unknown_files_permission']);
        $form->addElement($checkFieldPermission);

        $checkFieldWebSocket = new CheckboxField('webSocketDisableDecision');
        $checkFieldWebSocket->setLabel(OW::getLanguage()->text('frmclamav', 'socket_disable_decision'))->setValue($configs['socket_disable_decision']);
        $form->addElement($checkFieldWebSocket);

        $textFieldHost = new TextField("socketHost");
        $textFieldHost->setLabel(OW::getLanguage()->text('frmclamav', 'host_field'))->setValue($configs['socket_host']);
        $form->addElement($textFieldHost);

        $textFieldPort = new TextField("socketPort");
        $textFieldPort->setLabel(OW::getLanguage()->text('frmclamav', 'port_field'))->setValue($configs['socket_port']);
        $form->addElement($textFieldPort);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmclamav', 'save_btn_label'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $data = $form->getValues();
            OW::getConfig()->saveConfig('frmclamav', 'unknown_files_permission', $data['unknownFilePermission']);
            OW::getConfig()->saveConfig('frmclamav', 'socket_disable_decision', $data['webSocketDisableDecision']);
            OW::getConfig()->saveConfig('frmclamav', 'socket_host', $data['socketHost']);
            OW::getConfig()->saveConfig('frmclamav', 'socket_port', $data['socketPort']);
            OW::getFeedback()->info(OW::getLanguage()->text('frmclamav', 'admin_changed_success'));
        }

        $this->addForm($form);
    }
}