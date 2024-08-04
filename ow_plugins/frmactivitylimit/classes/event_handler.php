<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

class FRMACTIVITYLIMIT_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function init()
    {
        OW::getEventManager()->bind(FRMEventManager::AFTER_USER_QUERY_EXECUTED, array($this, 'onAfterUserQueryExecuted'));
        OW::getEventManager()->bind(OW_EventManager::ON_APPLICATION_INIT, array($this, 'onApplicationInit'));
    }

    public function onAfterUserQueryExecuted(OW_Event $event)
    {
        $tableName = $event->getParams()['table_name'];
        if(isset($GLOBALS['frmactivitylimit_skip']) && !$GLOBALS['frmactivitylimit_skip'] &&
            !in_array($tableName , [
                'frmactivitylimit_user_requests', 'base_user_online', 'mailbox_user_last_data',
                'base_user', 'newsfeed_action_set', 'frmuserlogin_active_details'
            ])
        ){
            FRMACTIVITYLIMIT_BOL_Service::getInstance()->increaseCountDBForCurrentUser();
            $GLOBALS['frmactivitylimit_skip'] = true;
        }
    }

    public function isUserInWhitelist()
    {
        if (!OW::getUser()->isAuthenticated() || OW::getUser()->isAdmin()) {
            return true;
        }
        return false;
    }

    public function isUrlInWhitelist()
    {
        $requestUri = OW::getRequest()->getRequestUri();
        if (in_array($requestUri, ['sign-out' ,'mobile-version','desktop-version'])||
            strpos($requestUri, 'activitylimit') > -1) {
            return true;
        }

        return false;
    }

    public function onApplicationInit(OW_Event $event)
    {
        if ($this->isUserInWhitelist()) {
            $GLOBALS['frmactivitylimit_skip'] = true;
            return;
        }

        if ($this->isUrlInWhitelist()) {
            $GLOBALS['frmactivitylimit_skip'] = true;
            return;
        }

        $GLOBALS['frmactivitylimit_skip'] = false;
        if(FRMACTIVITYLIMIT_BOL_Service::getInstance()->isLocked()){
            OW::getEventManager()->trigger(new OW_Event('frmactivitylimit.on_before_user_redirect_to_block_page'));
            if (OW::getRequest()->isAjax()){
                exit(json_encode(['result'=>'error', 'message'=>'too much requests!']));
            }
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmactivitylimit.blocked'));
        }
    }
}