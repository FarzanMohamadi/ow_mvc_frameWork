<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmftpmanager
 */
class FRMFTPMANAGER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text("admin", "page_title_manage_plugins_ftp_info"));
        $this->setPageTitle($language->text("admin", "page_title_manage_plugins_ftp_info"));
        $this->setPageHeadingIconClass("ow_ic_gear_wheel");
        $ftpAttrs = null;
        if(OW::getSession()->isKeySet("ftpAttrs")){
            $ftpAttrs = OW::getSession()->get("ftpAttrs");
        }
        $form = new Form("ftp");

        $ftpEnabled = new CheckboxField("ftp_enabled");
        $ftpEnabled->setValue(OW::getConfig()->getValue('frmftpmanager', 'ftp_enabled'));
        $ftpEnabled->setLabel($language->text("admin", "btn_label_activate"));
        $form->addElement($ftpEnabled);

        $login = new TextField("host");
        $login->setValue("localhost");
        if($ftpAttrs!=null && isset($ftpAttrs['host'])){
            $login->setValue($ftpAttrs['host']);
        }
        $login->setRequired(true);
        $login->setLabel($language->text("admin", "plugins_manage_ftp_form_host_label"));
        $form->addElement($login);

        $login = new TextField("login");
        $login->setHasInvitation(true);
        $login->setInvitation("login");
        $login->setRequired(true);
        $login->setLabel($language->text("admin", "plugins_manage_ftp_form_login_label"));
        if($ftpAttrs!=null && isset($ftpAttrs['login'])){
            $login->setValue($ftpAttrs['login']);
        }
        $form->addElement($login);

        $password = new PasswordField("password");
        $password->setHasInvitation(true);
        $password->setInvitation("password");
        $password->setRequired(true);
        $password->setLabel($language->text("admin", "plugins_manage_ftp_form_password_label"));
        if($ftpAttrs!=null && isset($ftpAttrs['password'])){
            $password->setValue($ftpAttrs['password']);
        }
        $form->addElement($password);

        $port = new TextField("port");
        $port->setValue(21);
        $port->addValidator(new IntValidator());
        $port->setLabel($language->text("admin", "plugins_manage_ftp_form_port_label"));
        if($ftpAttrs!=null && isset($ftpAttrs['port'])){
            $port->setValue($ftpAttrs['port']);
        }
        $form->addElement($port);

        $submit = new Submit("submit");
        $submit->setValue($language->text("admin", "plugins_manage_ftp_form_submit_label"));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                OW::getConfig()->saveConfig('frmftpmanager', 'ftp_enabled', $data['ftp_enabled']);

                $ftpAttrs = array(
                    "host" => trim($data["host"]),
                    "login" => trim($data["login"]),
                    "password" => trim($data["password"]),
                    "port" => (int) $data["port"]);
                $event = OW_EventManager::getInstance()->trigger(new OW_Event('base.save_ftp_attr', array("ftpAttrs" => $ftpAttrs)));
                OW::getSession()->set("ftpAttrs", $ftpAttrs);
                $this->redirectToAction('index');
            }
        }
    }
}