<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceContactUs
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

    private function __construct()
    {
    }


    public function processSendContactUsMessage(){
        $pluginActive = FRMSecurityProvider::checkPluginActive('frmcontactus', true);

        if(!$pluginActive){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if ( !OW::getUser()->isAuthenticated())
        {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $data=array();

        if(!isset($_POST['subject'])){
            return array('valid' => false, 'message' => 'input_error');
        }
        if(!isset($_POST['message'])){
            return array('valid' => false, 'message' => 'input_error');
        }

        $frmcontactus = FRMCONTACTUS_BOL_Service::getInstance();
        $contacts = $frmcontactus->getDepartmentList();
        $lable = 'native_contact';
        if (isset($contacts) && sizeof($contacts) > 0) {
            $lable = $contacts[0]->label;
        }

        $subject=$_POST['subject'];
        $message=$_POST['message'];
        $receiverEmail = OW::getConfig()->getValue('base', 'site_email');
        $mail = OW::getMailer()->createMail();
        $mail->addRecipientEmail($receiverEmail);
        $mail->setSender(OW::getUser()->getEmail());
        $mail->setSenderSuffix(false);
        $mail->setSubject($subject);
        $mail->setTextContent($message);
        $mail->setHtmlContent($message);
        $frmcontactus->addUserInformation($subject, OW::getUser()->getEmail(), $lable,$message);
        OW::getMailer()->addToQueue($mail);
        return array('valid' => true);
    }
    
}