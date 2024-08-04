<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmftpmanager.bol
 * @since 1.0
 */

class FRMFTPMANAGER_BOL_Service
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function __construct(){
    }

    public function isFtpNotExist(OW_Event $event)
    {
        $config = OW::getConfig();
        $ftpAttrsIsNotExist = false;
        if($config->getValue("frmftpmanager", "host") == "" ||
            $config->getValue("frmftpmanager", "username") == "" ||
            $config->getValue("frmftpmanager", "password") == "" ||
            $config->getValue("frmftpmanager", "port") == ""
        ){
            $ftpAttrsIsNotExist = true;
        }
        $event->setData(array("ftpAttrsIsNotExist" => $ftpAttrsIsNotExist));
    }

    public function getFtpAttrs(OW_Event $event)
    {
        $config = OW::getConfig();
        $host = $config->getValue("frmftpmanager", "host");
        $username = $config->getValue("frmftpmanager", "username");
        $password = $config->getValue("frmftpmanager", "password");
        $port = $config->getValue("frmftpmanager", "port");
        $event->setData(array("getFtpAttrs" => array("host" => $host, "login" => $username, "password" => $password, "port" => $port)));
    }

    public function saveFtpAttrs(OW_Event $event){
        $ftpAttr = $event->getParams();
        if(isset($ftpAttr['ftpAttrs'])){
            $ftpAttr = $ftpAttr['ftpAttrs'];
            $config = OW::getConfig();
            if(isset($ftpAttr['login'])){
                $config->saveConfig('frmftpmanager', 'username', $ftpAttr['login']);
            }
            if(isset($ftpAttr['host'])){
                $config->saveConfig('frmftpmanager', 'host', $ftpAttr['host']);
            }
            if(isset($ftpAttr['password'])){
                $config->saveConfig('frmftpmanager', 'password', $ftpAttr['password']);
            }
            if(isset($ftpAttr['port'])){
                $config->saveConfig('frmftpmanager', 'port', $ftpAttr['port']);
            }
        }
    }

    public function onBeforeFtp(OW_Event $event){
        OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmftpmanager_admin_setting'));
    }

    public function onBeforeGetFtpConnection(OW_Event $event){
        if(!OW::getConfig()->getValue('frmftpmanager', 'ftp_enabled')){
            $event->setData(['ftpConnection' => FRMFTPMANAGER_CLASS_Ftp::getConnection([])]);
        }
    }
}
