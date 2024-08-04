<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmsmtpcheck.bol
 * @since 1.0
 */
class FRMSMTPCHECK_BOL_Service
{

    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function SmtpDisableTLS(OW_Event $event){
        $event->setData(array('disable' => true));
    }

    public function beforeEmailCreate(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['adminNotificationUser'])){
            $suffix = OW::getConfig()->getValue('frmsmtpcheck', 'suffix');
            if(strpos($params['adminNotificationUser'], '@') === false && $suffix!=null && $suffix!=""){
                $adminNotificationUser = $params['adminNotificationUser'] . '@' . $suffix;
                $event->setData(array('adminNotificationUser' => $adminNotificationUser));
            }
        }
        else if(isset($params['mailState'])){
            $mailState = $params['mailState'];
            $senderEmail = $mailState['sender'][0];
            $suffix = OW::getConfig()->getValue('frmsmtpcheck', 'suffix');
            if(strpos($senderEmail, '@') === false && $suffix!=null && $suffix!=""){
                $mailState['sender'][0] = $mailState['sender'][0] . '@' . $suffix;
                $event->setData(array('mailState' => $mailState));
            }
        }
    }
}
