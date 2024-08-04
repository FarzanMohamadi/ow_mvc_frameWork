<?php
/**
 *
 * @package ow_plugins.newsfeed.controllers
 * @since 1.0
 */
class NEWSFEED_CTRL_Feed extends OW_ActionController
{
    /**
     *
     * @var NEWSFEED_BOL_Service
     */
    protected $service;

    public function __construct()
    {
        $this->service = NEWSFEED_BOL_Service::getInstance();
    }

    /**
     * 
     * @param NEWSFEED_CLASS_Driver $driver
     * @param string $feedType
     * @param string $feedId
     * @return NEWSFEED_CMP_Feed
     */
    protected function getFeed( NEWSFEED_CLASS_Driver $driver, $feedType, $feedId )
    {
        return OW::getClassInstance("NEWSFEED_CMP_Feed", $driver, $feedType, $feedId);
    }
    
    public function viewItem( $params )
    {
        $actionId = (int) $params['actionId'];
        $feedType = empty($_GET['ft']) ? 'site' : $_GET['ft'];
        $feedId = empty($_GET['fi']) ? null : $_GET['fi'];
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('actionId' => $actionId,'feedId' => $feedId)));
        $driverClasses = array(
            "site" => "NEWSFEED_CLASS_SiteDriver",
            "my" => "NEWSFEED_CLASS_UserDriver"
        );
        
        $driverClass = empty($driverClasses[$feedType]) 
                ? "NEWSFEED_CLASS_FeedDriver"
                : $driverClasses[$feedType];
        
        $driver = OW::getClassInstance($driverClass);
        
        $driver->setup(array(
            'feedType' => $feedType,
            'feedId' => $feedId
        ));

        $action = $driver->getActionById($actionId);

        if ( empty($action) )
        {
            throw new Redirect404Exception();
        }

        $feed = $this->getFeed($driver, $feedType, $feedId);
        $feed->setup(array(
            'viewMore' => false
        ));

        $feed->setDisplayType(NEWSFEED_CMP_Feed::DISPLAY_TYPE_PAGE);
        $feed->addAction($action);

        $this->addComponent('action', $feed);
        
        $this->assign("entity", array(
            "type" => $action->getEntity()->type,
            "id" => $action->getEntity()->id
        ));
    }

    public function follow()
    {
        $userId = (int) $_GET['userId'];
        $backUri = htmlspecialchars($_GET['backUri'], ENT_QUOTES, 'UTF-8');

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( empty($userId) )
        {
            throw new InvalidArgumentException('Invalid parameter `userId`');
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['followCode'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['followCode'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'followProfile_newsfeed')));
        }
        $eventParams = array(
            'userId' => OW::getUser()->getId(),
            'feedType' => 'user',
            'feedId' => $userId
        );

        OW::getEventManager()->trigger( new OW_Event('feed.add_follow', $eventParams) );

        $backUrl = OW_URL_HOME . $backUri;
        $username = BOL_UserService::getInstance()->getDisplayName($userId);

        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array(
                'message' => OW::getLanguage()->text('newsfeed', 'follow_complete_message', array('username' => $username))
            )));
        }
        else
        {
            OW::getFeedback()->info(OW::getLanguage()->text('newsfeed', 'follow_complete_message', array('username' => $username)));
            $this->redirect($backUrl);
        }
    }

    public function unFollow()
    {
        $userId = (int) $_GET['userId'];
        $backUri = htmlspecialchars($_GET['backUri'], ENT_QUOTES, 'UTF-8');

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( empty($userId) )
        {
            throw new InvalidArgumentException('Invalid parameter `userId`');
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            if(!isset($_GET['unFollowCode'])){
                throw new Redirect404Exception();
            }
            $code = $_GET['unFollowCode'];
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'unFollowProfile_newsfeed')));
        }


        $this->service->removeFollow(OW::getUser()->getId(), 'user', $userId);

        $backUrl = OW_URL_HOME . $backUri;
        $username = BOL_UserService::getInstance()->getDisplayName($userId);

        if ( OW::getRequest()->isAjax() )
        {
            exit(json_encode(array(
                'message' => OW::getLanguage()->text('newsfeed', 'unfollow_complete_message', array('username' => $username))
            )));
        }
        else
        {
            OW::getFeedback()->info(OW::getLanguage()->text('newsfeed', 'unfollow_complete_message', array('username' => $username)));
            $this->redirect($backUrl);
        }
    }
}