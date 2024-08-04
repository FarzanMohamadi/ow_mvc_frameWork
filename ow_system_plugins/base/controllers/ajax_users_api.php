<?php
/**
 * API Responder
 *
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_AjaxUsersApi extends OW_ActionController
{
    private function checkAdmin()
    {
        if ( !OW::getUser()->isAuthorized('base') )
        {
            throw new Exception("Not authorized action");
        }
    }

    private function checkAuthenticated()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new Exception("Not authenticated user");
        }
    }

    public function rsp()
    {
        if (!OW::getRequest()->isAjax()) {
            throw new Redirect404Exception();
        }

        $command = trim($_GET['command']);
        $whitelist = array('suspend', 'deleteUser', 'unsuspend', 'block', 'unblock', 'feature', 'unfeature');
        if (!in_array($command, $whitelist)) {
            throw new Redirect404Exception();
        }

        $query = json_decode($_GET['params'], true);

        $response = call_user_func(array($this, $command), $query);

        $response = empty($response) ? array() : $response;
        echo json_encode($response);
        exit;
    }

    private function suspend( $params )
    {
        $this->checkAdmin();
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $params["message"]=OW::getLanguage()->text('base','suspend_notification_subject');
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'userSuspend_core')));
        }
        BOL_UserService::getInstance()->suspend($params["userId"], $params["message"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_suspended')
        );
    }
    
    private function deleteUser( $params )
    {
        $this->checkAdmin();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'userDelete_core')));
        }
        BOL_UserService::getInstance()->deleteUser($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_deleted_page_message')
        );
    }

    private function unsuspend( $params )
    {
        $this->checkAdmin();
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => ow::getUser()->getId(), 'code'=>$code,'activityType'=>'userUnSuspend_core')));
        }
        BOL_UserService::getInstance()->unsuspend($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_unsuspended')
        );
    }

    private function block( $params )
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'userBlock_core')));
        }
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->block($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_blocked')
        );
    }

    private function unblock( $params )
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'userUnBlock_core')));
        }
        $this->checkAuthenticated();
        BOL_UserService::getInstance()->unblock($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_profile_unblocked')
        );
    }

    private function feature( $params )
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'userFeature_core')));
        }
        $this->checkAdmin();
        BOL_UserService::getInstance()->markAsFeatured($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_marked_as_featured')
        );
    }

    private function unfeature( $params )
    {
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'userUnFeature_core')));
        }
        $this->checkAdmin();
        BOL_UserService::getInstance()->cancelFeatured($params["userId"]);

        return array(
            "info" => OW::getLanguage()->text('base', 'user_feedback_unmarked_as_featured')
        );
    }

}