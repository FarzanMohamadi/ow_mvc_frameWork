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
class FRMMOBILESUPPORT_BOL_WebServiceBroadcast
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

    public function sendMessageToUserFromOutside() {

        header('HTTP/1.0' . ' ' . '200 OK');
        header('Status' . ' ' . '200 OK');

        if(!BOL_PluginService::getInstance()->findPluginByKey('frmsms')->isActive()){
            return array('valid' => 'false', 'message' => 'plugin frmsms not found');
        }

        $login_result = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->login();

        if($login_result['valid'] == false){
            return array('valid' => $login_result['valid'], 'message' => 'authentication failed', 'auth_message' => $login_result['message']);
        }

        $allowed_to_send_custom_message = BOL_AuthorizationService::getInstance()
            ->isActionAuthorizedForUser(OW::getUser()->getId(), 'broadcast');

        if(!$allowed_to_send_custom_message){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $phoneNumber = null;
        if(isset($_POST['phoneNumber'])){
            $phoneNumber = $_POST['phoneNumber'];
        }
        if(!isset($phoneNumber)){
            return array('valid' => false, 'message' => 'empty_phone_number');
        }

        if(strlen($phoneNumber) != 11){
            return array('valid' => false, 'message' => 'invalid_phone_number');
        }

        $userListByPhoneNumber = BOL_UserService::getInstance()->findUserListByQuestionValues(array('field_mobile' => $phoneNumber), 0, 1, true);

        if(empty($userListByPhoneNumber)){
            return array('valid' => false, 'message' => 'user_not_found');
        }

        $targetUserId = $userListByPhoneNumber[0]->getId();

        if(OW::getUser()->getId() == $targetUserId){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $text = null;
        if(isset($_POST['text'])){
            $text = $_POST['text'];
        }

        if(!isset($text)){
            return array('valid' => false, 'message' => 'empty_text');
        }

        $senderUserId = OW_User::getInstance()->getId();
        return BROADCAST_BOL_Service::getInstance()->sendCustomMessageToUser($senderUserId, $targetUserId, $text);
    }

}
