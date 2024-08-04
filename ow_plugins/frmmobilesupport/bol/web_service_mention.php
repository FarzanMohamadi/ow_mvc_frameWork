<?php

/**
 *
 *
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceMention
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

    public function getMentionSuggestion() {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if(!FRMSecurityProvider::checkPluginActive('frmmention', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        $username = false;
        if(isset($_GET['username']))
        {
            $username = urldecode($_GET['username']);
        }

        $max_count = OW::getConfig()->getValue('frmmention','max_count');

        $userPrioritizedIds = FRMMENTION_BOL_Service::getInstance()->findPrioritizedUsers($username, $max_count);

        $data = FRMMENTION_BOL_Service::getInstance()->getUserInfoForUserIdList(array_unique($userPrioritizedIds));

        return $data;
    }

}