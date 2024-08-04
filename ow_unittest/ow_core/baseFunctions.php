<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class UnittestBaseFunctions
{
    public static function isSMTPWorking()
    {
        if(OW::getConfig()->getValue('base', 'mail_smtp_enabled') == false)
            return false;

        try
        {
            $result = BOL_MailService::getInstance()->smtpTestConnection();
        }
        catch ( LogicException $e )
        {
            return false;
        }

        return $result;
    }
    public static function setDefaultSMTPSettings($array)
    {
        $config = OW::getConfig();
        $config->saveConfig('base', 'mail_smtp_enabled', true);
        $config->saveConfig('base', 'mail_smtp_host', $array['host']);
        $config->saveConfig('base', 'mail_smtp_port', $array['port']);
        $config->saveConfig('base', 'mail_smtp_user', $array['username']);
        $config->saveConfig('base', 'mail_smtp_password', $array['password']);
        $config->saveConfig('base', 'mail_smtp_connection_prefix', $array['prefix']);
    }
}
