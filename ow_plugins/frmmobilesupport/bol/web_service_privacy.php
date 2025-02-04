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
class FRMMOBILESUPPORT_BOL_WebServicePrivacy
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


    public function userPrivacy(){
        /*
         * soal aslan guest check kardan inja mani mide?
         */
        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if (!$guestAccess) {
            return array('valid' => false, 'message' => 'guest_cant_view');
        }

        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $userId = OW::getUser()->getId();
        return $this->getUserPrivacy($userId);
    }

    public function getUserPrivacy($userId)
    {
        $privacyData = array();
        if (FRMSecurityProvider::checkPluginActive('privacy', true)) {


            $actionList = PRIVACY_BOL_ActionService::getInstance()->findAllAction();

            $actionNameList = array();
            foreach ($actionList as $action) {
                $actionNameList[$action->key] = $action->key;
            }

            $actionValueList = PRIVACY_BOL_ActionService::getInstance()->getActionValueList($actionNameList, $userId);


            foreach ($actionValueList as $key => $value) {
                $privacyData[] = array(
                    'key' => $key,
                    'value' => $value,
                    'label' => $actionList[$key]->label,
                    'description' => $actionList[$key]->description
                );
            }

        }
        return $privacyData;
    }

    public function savePrivacy()
    {
        if(!FRMSecurityProvider::checkPluginActive('privacy', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $userId = OW::getUser()->getId();
        $data=array();
        if(isset($_POST['data'])){
            $data = $_POST['data'];
        }
        if(empty($data) || $userId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $privacyToSave=array();
        foreach ($data as $privacyConfig)
        {
            $privacyToSave[$privacyConfig['key']]=$privacyConfig['value'];
        }
        PRIVACY_BOL_ActionService::getInstance()->saveActionValues($privacyToSave, $userId);
        return array('valid' => true);
    }
}