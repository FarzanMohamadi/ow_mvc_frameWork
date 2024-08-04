<?php

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.friends.mobile.controllers
 * @since 1.6.0
 */
class FRIENDS_MCTRL_Action extends OW_MobileActionController
{
    public function acceptAjax()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $userId = (int) OW::getUser()->getId();
        $requesterId = (int) $_POST['id'];
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_POST['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId, 'code'=>$code,'activityType'=>'accept_friends')));
        }
        $service = FRIENDS_BOL_Service::getInstance();

        $frendshipDto = $service->accept($userId, $requesterId);

        if ( !empty($frendshipDto) )
        {
            $service->onAccept($userId, $requesterId, $frendshipDto);

            exit(json_encode(array('result' => true, 'message' => OW::getLanguage()->text('friends', 'feedback_request_accepted'))));
        }

        exit(json_encode(array('result' => false)));
    }

    public function ignoreAjax()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $userId = (int) OW::getUser()->getId();
        $requesterId = (int) $_POST['id'];
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_POST['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId, 'code'=>$code,'activityType'=>'ignore_friends')));
        }
        $service = FRIENDS_BOL_Service::getInstance();
        $service->ignore($requesterId, $userId);

        exit(json_encode(array('result' => true)));
    }



    /**
     * Request new friendship controller
     *
     * @param array $params
     * @throws Redirect404Exception
     * @throws AuthenticateException
     */
    public function request( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = OW::getUser()->getId();

        $userId = (int) $params['id'];

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), $userId) )
        {
            throw new Redirect404Exception();
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'request_friends')));
        }
        $isAutorise=true;
        if (!OW::getUser()->isAuthorized('friends', 'add_friend') && !OW::getUser()->isAdmin())
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('friends', 'add_friend');
            OW::getFeedback()->error($status['msg']);
            $isAutorise = false;
        }

        $service = FRIENDS_BOL_Service::getInstance();

        if ( $service->findFriendship($requesterId, $userId) === null  )
        {
            if ($isAutorise !== false)
            {
                $service->request($requesterId, $userId);
                $service->onRequest($requesterId, $userId);
                $service->onFriendshipRequestNotification($requesterId,$userId);
                OW::getFeedback()->info(OW::getLanguage()->text('friends', 'feedback_request_was_sent'));
            }
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text('friends', 'feedback_request_already_sent_error_message'));
        }

        if ( isset( $params['backUrl'] ) )
        {
            $this->redirect($params['backUrl']);
        }
        else
        {
            $username = BOL_UserService::getInstance()->getUserName($userId);
            $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
            $this->redirect($backUrl);
        }
    }

    /**
     * Accept new friendship request
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function accept( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = OW::getUser()->getId();

        $service = FRIENDS_BOL_Service::getInstance();

        $frendshipDto = $service->accept($userId, $requesterId);

        if ( !empty($frendshipDto) )
        {
            $service->onAccept($userId, $requesterId, $frendshipDto);

            OW::getFeedback()->info(OW::getLanguage()->text('friends', 'feedback_request_accepted'));
        }

        if ( !empty($params['backUrl']) )
        {
            $this->redirect($params['backUrl']);
        }
        else {
            $username = BOL_UserService::getInstance()->getUserName($requesterId);
            $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
            $this->redirect($backUrl);
        }
    }

    /**
     * Ignore new friendship request
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function ignore( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) OW::getUser()->getId();
        $userId = (int) $params['id'];

        $service = FRIENDS_BOL_Service::getInstance();

        $service->ignore($userId, $requesterId);

        OW::getFeedback()->info(OW::getLanguage()->text('friends', 'feedback_request_ignored'));

        $username = BOL_UserService::getInstance()->getUserName($requesterId);
        $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
        $this->redirect($backUrl);
    }

    /**
     * Cancel friendship
     *
     * @param array $params
     * @throws AuthenticateException
     */
    public function cancel( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = (int) OW::getUser()->getId();

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$params['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => $userId, 'code'=>$code,'activityType'=>'cancel_friends')));
        }

        //Remove notification that is produced due to friendship request
        $service = FRIENDS_BOL_Service::getInstance();
        $service->onCancelFriendshipRequest($requesterId,$userId);

        $event = new OW_Event('friends.cancelled', array(
            'senderId' => $requesterId,
            'recipientId' => $userId
        ));

        OW::getEventManager()->trigger($event);

        OW::getFeedback()->info(OW::getLanguage()->text('friends', 'feedback_cancelled'));

        $username = BOL_UserService::getInstance()->getUserName($requesterId);
        $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
        $this->redirect($backUrl);
    }


    public function activate( $params )
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        $requesterId = (int) $params['id'];
        $userId = (int) OW::getUser()->getId();

        FRIENDS_BOL_Service::getInstance()->activate($userId, $requesterId);

        OW::getFeedback()->info(OW::getLanguage()->text('friends', 'new_friend_added'));

        $username = BOL_UserService::getInstance()->getUserName($requesterId);
        $backUrl = OW::getRouter()->urlForRoute('base_user_profile', array('username'=>$username));
        $this->redirect($backUrl);
    }
}
