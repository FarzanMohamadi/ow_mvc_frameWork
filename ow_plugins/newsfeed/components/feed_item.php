<?php
/**
 * Feed Item component
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_FeedItem extends OW_Component
{
    /**
     *
     * @var NEWSFEED_CLASS_Action
     */
    protected $action;
    protected $autoId;
    protected $displayType;

    protected $remove = false;

    protected $sharedData = array();

    public function __construct( NEWSFEED_CLASS_Action $action, $sharedData )
    {
        parent::__construct();

        $this->displayType = NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTION;
        $this->action = $action;
        $this->sharedData = $sharedData;
        if (isset($this->sharedData['feedAutoId'])) {
            $this->autoId = 'action-' . $this->sharedData['feedAutoId'] . '-' . $action->getId();
        }else{
            $this->autoId = null;
        }

    }

    public function setDisplayType( $type )
    {
        $this->displayType = $type;
    }

    protected function mergeData( $data, NEWSFEED_CLASS_Action $_action )
    {
        $data = empty($data) ? array() : $data;

        $action = array(
            'userId' => $_action->getUserId(),
            'createTime' => $_action->getCreateTime(),
            'entityType' => $_action->getEntity()->type,
            'entityId' => $_action->getEntity()->id,
            'pluginKey' => $_action->getPluginKey(),
            'format' => $_action->getFormat()
        );

        $view = array( 'iconClass' => 'ow_ic_info', 'class' => '', 'style' => '' );
        $defaults = array(
            'line' => null, 'string' => null, 'content' => null, 'toolbar' => array(), 'context' => array(),
            'features' => array( 'comments', 'likes' ), 'contextMenu' => array()
        );

        foreach ( $defaults as $key => $value )
        {
            if ( !isset($data[$key]) )
            {
                $data[$key] = $value;
            }
        }
        
        if ( !isset($data['view']) || !is_array($data['view']) )
        {
            $data['view'] = array();
        }

        $data['view'] = array_merge($view, $data['view']);

        if ( !isset($data['action']) || !is_array($data['action']) )
        {
            $data['action'] = array();
        }

        $data['action'] = array_merge($action, $data['action']);
        
        $data['action']["userIds"] = empty($data['action']["userIds"]) 
                ? array($data['action']["userId"])
                : $data['action']["userIds"];
        if(isset($data['lastActivity']) && $data['lastActivity']!=null && $this->displayType != NEWSFEED_CMP_Feed::DISPLAY_TYPE_PAGE ) {
            $data['actionData'] = $_action->getData();
        }
        return $data;
    }

    protected function getActionData( NEWSFEED_CLASS_Action $action )
    {
        $activity = array();
        $createActivity = $action->getCreateActivity();
        $lastActivity = null;

        $group = null;
        $cacheData = array();
        if (isset($this->sharedData['cache'])) {
            $cacheData = $this->sharedData['cache'];
        }
        $isChannel = false;
        if (isset($this->sharedData['additionalParamList']['group'])) {
            $group = $this->sharedData['additionalParamList']['group'];
        }
        if (isset($this->action)) {
            if (isset($this->action->getData()['contextFeedType'])) {
                $entityType = $this->action->getData()['contextFeedType'];
                $entityId = $this->action->getData()['contextFeedId'];
                if ($entityType == 'groups' && isset($this->sharedData['cache']['groups'][$entityId])) {
                    $group = $this->sharedData['cache']['groups'][$entityId];
                }
            } else {
                $feeds = $this->action->getFeedList();
                if (!empty($feeds)) {
                    $feed = $feeds[0];
                    if ($feed->feedType == 'groups' && isset($this->sharedData['cache']['groups'][$feed->feedId])) {
                        $group = $this->sharedData['cache']['groups'][$feed->feedId];
                    }
                }
            }
        }

        if (isset($this->sharedData['additionalParamList']['isChannel'])) {
            $isChannel = $this->sharedData['additionalParamList']['isChannel'];
        }
        $additionalInfo = array();
        if (isset($this->sharedData['additionalParamList'])) {
            $additionalInfo = $this->sharedData['additionalParamList'];
        }

        foreach ( $action->getActivityList() as $a )
        {
            /* @var $a NEWSFEED_BOL_Activity */
            $activity[$a->id] = array(
                'activityType' => $a->activityType,
                'activityId' => $a->activityId,
                'id' => $a->id,
                'data' => json_decode($a->data, true),
                'timeStamp' => $a->timeStamp,
                'privacy' => $a->privacy,
                'userId' => $a->userId,
                'visibility' =>$a->visibility
            );

            if ( ($lastActivity === null || $activity[$a->id]['timeStamp'] > $lastActivity['timeStamp']) && !in_array($activity[$a->id]['activityType'], NEWSFEED_BOL_Service::getInstance()->SYSTEM_ACTIVITIES) )
            {
                $lastActivity = $activity[$a->id];
            }
        }

        $creatorIdList = $action->getCreatorIdList();
        $data = $this->mergeData($action->getData(), $action);

        $sameFeed = false;
        $feedList = array();
        foreach ( $action->getFeedList() as $feed )
        {
            if ( !$sameFeed )
            {
                $sameFeed = $this->sharedData['feedType'] == $feed->feedType
                        && $this->sharedData['feedId'] == $feed->feedId;
            }
            
            $feedList[] = array(
                "feedType" => $feed->feedType,
                "feedId" => $feed->id
            );
        }
        
        $eventParams = array(
            'action' => array(
                'id' => $action->getId(),
                'entityType' => $action->getEntity()->type,
                'entityId' => $action->getEntity()->id,
                'pluginKey' => $action->getPluginKey(),
                'createTime' => $action->getCreateTime(),
                'userId' => $action->getUserId(), // backward compatibility with desktop version
                "userIds" => $creatorIdList,
                'format' => $action->getFormat(),
                'data' => $data,
                "feeds" => $feedList,
                "onOriginalFeed" => $sameFeed
            ),
            'group' => $group,
            'isChannel' => $isChannel,
            'additionalInfo' => $additionalInfo,
            'activity' => $activity,
            'createActivity' => $createActivity,
            'lastActivity' => $lastActivity,
            'feedType' => $this->sharedData['feedType'],
            'feedId' => $this->sharedData['feedId'],
            'feedAutoId' => $this->sharedData['feedAutoId'],
            'autoId' => $this->autoId,
            'cache' => $cacheData,
        );
        
        $data['action'] = array(
            'userId' => $action->getUserId(), // backward compatibility with desktop version
            "userIds" => $creatorIdList,
            'createTime' => $action->getCreateTime()
        );
 
        $shouldExtend = $this->displayType == NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY && $lastActivity !== null;
 
        if ( $shouldExtend )
        {
            if ( !empty($lastActivity['data']['string']) || !empty($lastActivity['data']['line']) )
            {
                $data = $this->applyRespond($data, $lastActivity);
            }
        }
        
        if ( $lastActivity !== null )
        {
            $data = $this->extendAction($data, $lastActivity);
            $data = $this->extendActionData($data, $lastActivity);
        }
        $event = new OW_Event('feed.on_item_render', $eventParams, $data);
        OW::getEventManager()->trigger($event);
        
        $outData = $event->getData();
        $outData["lastActivity"] = $lastActivity;
         
        return $this->mergeData( $outData, $action );
    }
    
    protected function applyRespond( $data, $respondActivity )
    {
        $data['action'] = array(
            'userId' => $respondActivity['userId'],
            'userIds' => empty($respondActivity['userIds']) ? array($respondActivity['userId']) : $respondActivity['userIds'], // backward compatibility with desktop version
            'createTime' => empty($respondActivity["data"]['timeStamp'])
                ? $respondActivity['timeStamp']
                : $respondActivity["data"]['timeStamp']
        );
        
        if ( isset($respondActivity["data"]["string"]) )
        {
            $data["string"] = $respondActivity["data"]["string"];
        }
        
        if ( isset($respondActivity["data"]["line"]) )
        {
            $data["line"] = $respondActivity["data"]["line"];
        }
        
        return $data;
    }
    
    protected function extendAction( $data, $activity )
    {
        $actionOverride = $activity['data'];
        $action = empty($actionOverride['action']) ? array() : $actionOverride['action'];
        
        if ( !empty($actionOverride['params']) )
        {
            $action = $actionOverride['params'];
        }
                
        if ( !empty($action["userId"]) && empty($action["userIds"]) )
        {
            $action["userIds"] = array($action["userId"]); // backward compatibility with desktop version
        }
        
        $data["action"] = array_merge($data["action"], $action);
        
        return $data;
   }
    
    protected function extendActionData( $data, $activity )
    {
        $actionOverride = $activity['data'];
        
        foreach ( $actionOverride as $key => $value )
        {
            if ( $key == 'view' )
            {
                if ( is_array($value) )
                {
                    $data[$key] = array_merge($data[$key], $value);
                }
            }
            else if ( $key == 'content' && is_array($value) )
            {
                $newContent = array_merge($data["key"], $value);
                
                if ( isset($value["vars"]) )
                {
                    $newContent["vars"] = array_merge($data[$key]["vars"], $value["vars"]);
                }
            }                
            else if ( !in_array($key, array("action", "string", "line")) )
            {
                $data[$key] = $value;
            }
        }
        
        return $data;
    }
 
    public function generateJs( $data )
    {
        $js = UTIL_JsGenerator::composeJsString('
            window.ow_newsfeed_feed_list[{$feedAutoId}].actions[{$uniq}] = new NEWSFEED_FeedItem({$autoId}, window.ow_newsfeed_feed_list[{$feedAutoId}]);
            window.ow_newsfeed_feed_list[{$feedAutoId}].actions[{$uniq}].construct({$data});
        ', array(
            'uniq' => $data['entityType'] . '.' . $data['entityId'],
            'feedAutoId' => $this->sharedData['feedAutoId'],
            'autoId' => $this->autoId,
            'id' => $this->action->getId(),
            'data' => array(
                'entityType' => $data['entityType'],
                'entityId' => $data['entityId'],
                'id' => $data['id'],
                'updateStamp' => $this->action->getUpdateTime(),
                'currentUserId' => OW::getUser()->getId(),
                'likes' => isset($data['features']['system']['likes']['likescount']) ? $data['features']['system']['likes']['likescount'] : 0,
                'dislikes' => isset($data['features']['system']['likes']['dislikescount']) ? $data['features']['system']['likes']['dislikescount'] : 0,
                'ownerId' => $this->action->getUserId(),
                'userVote' => isset($data['features']['system']['likes']['userVote']) ? $data['features']['system']['likes']['userVote'] : 0,
                'total' => isset($data['features']['system']['likes']['total']) ? $data['features']['system']['likes']['total'] : 0,
                'upUserId' => !empty($data['features']['system']['likes']['upUserId']) ? $data['features']['system']['likes']['upUserId'] : array(),
                'downUserId' => !empty($data['features']['system']['likes']['downUserId']) ? $data['features']['system']['likes']['downUserId'] : array(),
                'comments' => !empty($data['features']['system']['comments']) ? $data['features']['system']['comments']['count'] : 0,
                'uri' => urlencode(OW::getRequest()->getRequestUri()),
                'cycle' => $data['cycle'],
                'displayType' => $this->displayType,
                'liked_user_list_label' => OW::getLanguage()->text("newsfeed", "liked_user_list"),
                'disliked_user_list_label' => OW::getLanguage()->text("newsfeed", "disliked_user_list"),
                'total_user_list_label' => OW::getLanguage()->text("newsfeed", "total_user_list")
            )
        ));
 
        OW::getDocument()->addOnloadScript($js, 50);
    }
 
    protected function processAssigns( $content, $assigns )
    {
        if (is_array($content)) {
            return '';
        }
        $search = array();
        $values = array();
 
        foreach ( $assigns as $key => $item )
        {
            $search[] = '[ph:' . $key . ']';
            $values[] = $item;
        }
 
        $result = str_replace($search, $values, $content);
        $result = preg_replace('/\[ph\:\w+\]/', '', $result);
 
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $result)));
        if(isset($stringRenderer->getData()['string'])){
            $result = $stringRenderer->getData()['string'];
        }
        return $result;
    }
 
    protected function renderTemplate( $tplFile, $vars )
    {
        $template = new NEWSFEED_CMP_Template();
        $template->setTemplate($tplFile);
 
        foreach ( $vars as $k => $v )
        {
            $template->assign($k, $v);
        }
 
        return $template->render();
    }
    
    protected function renderFormat( $format, $vars )
    {
        return NEWSFEED_CLASS_FormatManager::getInstance()->renderFormat($format, $vars);
    }
    
    function renderContent( $content )
    {
        if ( !is_array($content) )
        {
            return $content;
        }
 
        $vars = empty($content['vars']) || !is_array($content['vars']) ? array() : $content['vars'];
        
        $template = null;
        
        if ( !empty($content['templateFile']) )
        {
            $template = $content['templateFile'];
        }
        else if ( !empty($content['template']) )
        {
            $template = OW::getPluginManager()->getPlugin('newsfeed')->getViewDir() . 'templates' . DS . trim($content['template']) . '.html';
        }
        
        if ( $template !== null )
        {
            return $this->renderTemplate($template, $vars);
        }
 
        if ( empty($content["format"]) )
        {
            return "";
        }
        
        return $this->renderFormat($content["format"], $vars);
    }
 
    protected function getUserInfo( $userId )
    {
        $usersInfo = $this->sharedData['usersInfo'];
 
        if ( !in_array($userId, $this->sharedData['usersIdList']) )
        {
            $userInfo = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
 
            $usersInfo['avatars'][$userId] = $userInfo[$userId]['src'];
            $usersInfo['urls'][$userId] = $userInfo[$userId]['url'];
            $usersInfo['names'][$userId] = $userInfo[$userId]['title'];
            $usersInfo['roleLabels'][$userId] = array(
                'label' => $userInfo[$userId]['label'],
                'labelColor' => $userInfo[$userId]['labelColor']
            );
        }

        $userAvatarImageSource = $usersInfo['avatars'][$userId];
        $user = array(
            'id' => $userId,
            'avatarUrl' => $userAvatarImageSource,
            'imageInfo' => BOL_AvatarService::getInstance()->getAvatarInfo($userId, $userAvatarImageSource),
            'url' => $usersInfo['urls'][$userId],
            'name' => $usersInfo['names'][$userId],
            'roleLabel' => empty($usersInfo['roleLabels'][$userId])
                ? array('label' => '', 'labelColor' => '')
                : $usersInfo['roleLabels'][$userId]
        );
        $user['imageInfo'] = BOL_AvatarService::getInstance()->getAvatarInfo($userId, $user['avatarUrl']);
 
        return $user;
    }
    
    protected function getActionUsersInfo( $data )
    {
        $userIds = $data['action']['userIds'];
        
        if ( !empty($data['action']['avatars']) )
        {
            return array($data['action']['avatars']);
        }
        
        if ( !empty($data['action']['avatar']) )
        {
            return array($data['action']['avatar']);
        }
        
        $out = array();
 
        foreach ( $userIds as $userId )
        {
            $out[$userId] = $this->getUserInfo($userId);
        }
        
        return $out;
    }
 
    protected function getContextMenu( $data )
    {
        $contextActionMenu = new BASE_CMP_ContextAction();
 
        $contextParentAction = new BASE_ContextAction();
        $contextParentAction->setKey('newsfeed_context_menu_' . $this->autoId);
        $contextParentAction->setClass('ow_newsfeed_context');
        $contextActionMenu->addAction($contextParentAction);
 
        $order = 1;
        foreach( $data['contextMenu'] as $action )
        {
            $action = array_merge(array(
                'label' => null,
                'order' => $order,
                'class' => null,
                'url' => null,
                'id' => null,
                'key' => FRMSecurityProvider::generateUniqueId($this->autoId . '_'),
                'attributes' => array()
            ), $action);
 
            $contextAction = new BASE_ContextAction();
            $contextAction->setParentKey($contextParentAction->getKey());
 
            $contextAction->setLabel($action['label']);
            $contextAction->setClass($action['class']);
            $contextAction->setUrl($action['url']);
            $contextAction->setId($action['id']);
            $contextAction->setKey($action['key']);
            $contextAction->setOrder($action['order']);
 
            foreach ( $action['attributes'] as $key => $value )
            {
                $contextAction->addAttribute($key, $value);
            }
 
            $contextActionMenu->addAction($contextAction);
            $order++;
        }
 
        return $contextActionMenu->render();
    }
 
    protected function getFeaturesData( $data )
    {
        $configs = $this->sharedData['configs'];
 
        $customFeatures = array();
        $systemFeatures = array();
        foreach ( $data['features'] as $key => $feature )
        {
            if ( is_string($feature) )
            {
                $systemFeatures[$feature] = array();
            }
            else if ( in_array($key, array('comments', 'likes'), true) )
            {
                $systemFeatures[$key] = $feature;
            }
            else if ( is_array($feature) )
            {
                $customFeatures[$key] = $feature;
            }
        }
        
        $features = array();
        $commentView = true;
        $authComment = OW::getUser()->isAuthorized('frmsecurityessentials', 'user-can-view-comments');
        if(isset($authComment)){
            $commentView = $authComment;
        }
        if ( $configs['allow_comments'] && key_exists('comments', $systemFeatures) && $commentView )
        {
            $commentsFeature = array();
            
            $featureData = $systemFeatures['comments'];
 
            $commentsFeature["authGroup"] = empty($featureData['pluginKey']) ? $data['action']['pluginKey'] : $featureData['pluginKey'];
            $commentsFeature["entityType"] = empty($featureData['entityType']) ? $data['action']['entityType'] : $featureData['entityType'];
            $commentsFeature["entityId"] = empty($featureData['entityId']) ? $data['action']['entityId'] : $featureData['entityId'];
 
            $authActionDto = BOL_AuthorizationService::getInstance()->findAction($commentsFeature["authGroup"], 'add_comment', true);
 
            if ( $authActionDto === null )
            {
                $commentsFeature["authGroup"] = 'newsfeed';
            }
 
            $commentsFeature['count'] = $this->sharedData['commentsData'][$commentsFeature["entityType"]][$commentsFeature["entityId"]]['commentsCount'];
            $commentsFeature['allow'] = OW::getUser()->isAuthorized($commentsFeature["authGroup"], 'add_comment') || OW::getUser()->isAuthorized($commentsFeature["authGroup"]) || OW::getUser()->isAdmin();
            $commentsFeature['expanded'] = $configs['features_expanded'] && $commentsFeature['count'] > 0;
            $commentsFeature["comments"] = $this->sharedData['commentsData'];
            
            $features["comments"] = $commentsFeature;
        }
        
        if ( $configs['allow_likes'] && key_exists('likes', $systemFeatures) )
        {
           $likesFeature = array();
            
           $featureData = $systemFeatures['likes'];
 
           $likesFeature["entityType"] = empty($featureData['entityType']) ? $data['action']['entityType'] : $featureData['entityType'];
           $likesFeature["entityId"] = empty($featureData['entityId']) ? $data['action']['entityId'] : $featureData['entityId'];
 
           $likesData = $this->sharedData['likesData'];
           $likes = empty($likesData[$likesFeature["entityType"]][$likesFeature["entityId"]])
                ? array() : $likesData[$likesFeature["entityType"]][$likesFeature["entityId"]];
 
           $userLiked = false;
            $dislikes = array();
            $userVote = 0;
            $upUserId = array();
            $downUserId = array();
            /** @var BOL_Vote $like */
            foreach ($likes as $key => $like )
           {
                if ( $like->userId == OW::getUser()->getId() )
                {
                    $userVote = $like->vote;
                }

                if ($like->vote == -1) {
                    $dislikes[] = $like;
                    $downUserId[] = $like->getUserId();
                } else if ($like->vote == 1) {
                    $upUserId[] = $like->getUserId();
                }

                if($like->vote != 1) {
                    unset($likes[$key]);
                }
           }
 
           $likesFeature['count'] = count($likes);
           $likesFeature['liked'] = $userLiked;
           $likesFeature['userVote'] = $userVote;
           $likesFeature['upUserId'] = $upUserId;
           $likesFeature['downUserId'] = $downUserId;
           $likesFeature['likescount'] = count($upUserId);
           $likesFeature['dislikescount'] = count($downUserId) * -1;
           $likesFeature["likes"] = $likes;
           $likesFeature['dislikes'] = $dislikes;
           $likesFeature['total'] = count($likes) - count($dislikes);
           $likesFeature['allow'] = true;

           if ( empty($featureData['error']) )
           {
                $likesFeature['error'] = OW::getUser()->isAuthenticated()
                    ? null
                    : OW::getLanguage()->text('newsfeed', 'guest_like_error');
           }
           else
           {
               $likesFeature['error'] = $featureData['error'];
           }
           
           $features["likes"] = $likesFeature;
        }
        

        $authComment = OW::getUser()->isAuthorized('frmsecurityessentials', 'user-can-view-comments');
        $this->assign('viewComments',$authComment);

        return array(
            "system" => $features,
            "custom" => $customFeatures
        );
    }
    
    protected function getFeatures( $data )
    {
        $configs = $this->sharedData['configs'];
        
        $featuresData = $this->getFeaturesData($data);
        
        $out = array(
            'system' => array(
                'comments' => false,
                'likes' => false
            ),
            'custom' => array()
        );
 
        $out['custom'] = $featuresData["custom"];
        $systemFeatures = $featuresData["system"];

        /*
         * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
         * when a newsfeed is created in group
         * it can be seen in dashboards of all members of that group and they must not be able to write comment or like for
         * a newsfeed of a channel group. So is channel must be check for every newsfeed item in dashboard or mainpage or anywhere that newsfeed of a group might be shown
         */
        $isChannel=false;
        $group = null;
        if (isset($this->sharedData['additionalParamList']['group'])) {
            $group = $this->sharedData['additionalParamList']['group'];
        }
        $cache = array();
        if (isset($this->sharedData['cache'])) {
            $cache = $this->sharedData['cache'];
        }
        if (isset($this->action)) {
            if (isset($this->action->getData()['contextFeedType'])) {
                $entityType = $this->action->getData()['contextFeedType'];
                $entityId = $this->action->getData()['contextFeedId'];
                if ($entityType == 'groups' && isset($cache['groups'][$entityId])) {
                    $group = $cache['groups'][$entityId];
                }
            } else {
                $feeds = $this->action->getFeedList();
                if (!empty($feeds)) {
                    $feed = $feeds[0];
                    if ($feed->feedType == 'groups' && isset($cache['groups'][$feed->feedId])) {
                        $group = $cache['groups'][$feed->feedId];
                    }
                }
            }
        }
        $hideCommentFeatures = false;
        $hideLikeFeatures = false;
        if (isset($this->sharedData['additionalParamList']['isChannel']) &&
            isset($this->sharedData['additionalParamList']['hideCommentFeatures']) &&  isset($this->sharedData['additionalParamList']['hideLikeFeatures'])) {
            $isChannel = $this->sharedData['additionalParamList']['isChannel'];
            $hideCommentFeatures = $this->sharedData['additionalParamList']['hideCommentFeatures'];
            $hideLikeFeatures = $this->sharedData['additionalParamList']['hideLikeFeatures'];
        } else {
            $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load', array('action'=>$this->action, 'group' => $group, 'cache' => $cache)));
            if (isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel']==true) {
                $isChannel = true;
            }
            if ((isset($channelEvent->getData()['hideCommentFeatures']) && $channelEvent->getData()['hideCommentFeatures']==true)) {
                $hideCommentFeatures = true;
            }
            if ((isset($channelEvent->getData()['hideLikeFeatures']) && $channelEvent->getData()['hideLikeFeatures']==true)) {
                $hideLikeFeatures = true;
            }
        }
        $this->assign('hideCommentFeatures', $hideCommentFeatures);
        $this->assign('hideLikeFeatures', $hideLikeFeatures);
        $this->assign('isChannel', $isChannel);

        if(!$isChannel)
        {
            if(!$hideCommentFeatures) {
                if (!empty($systemFeatures["comments"])) {
                    $feature = $systemFeatures["comments"];

                    $commentsParams = new BASE_CommentsParams($feature["authGroup"], $feature["entityType"]);
                    $commentsParams->setEntityId($feature["entityId"]);
                    $commentsParams->setInitialCommentsCount($configs['comments_count']);
                    $commentsParams->setLoadMoreCount(100);
                    $commentsParams->setBatchData($feature["comments"]);

                    $commentsParams->setOwnerId($this->action->getUserId());
                    $commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_WITH_LOAD_LIST_MINI);
                    $commentsParams->setWrapInBox(false);
                    $commentsParams->setShowEmptyList(false);

                    if (!empty($feature['error'])) {
                        $commentsParams->setErrorMessage($feature['error']);
                    }

                    if (isset($feature['allow'])) {
                        $commentsParams->setAddComment($feature['allow']);
                    }
                    if (isset($data['cache'])) {
                        $commentsParams->cachedParams = $data['cache'];
                    }
                    $commentCmp = new BASE_CMP_Comments($commentsParams);
                    $out['system']['comments']['cmp'] = $commentCmp->render();

                    $out['system']['comments']['count'] = $feature["count"];
                    $out['system']['comments']['allow'] = $feature["allow"];
                    $out['system']['comments']['expanded'] = $feature["expanded"];
                }
            }
            if(!$hideLikeFeatures) {
                if (!empty($systemFeatures["likes"])) {
                    $feature = $systemFeatures['likes'];
                    $out['system']['likes']['count'] = $feature["count"];
                    $out['system']['likes']['liked'] = $feature["liked"];
                    $out['system']['likes']['userVote'] = $feature["userVote"];
                    $out['system']['likes']['likescount'] = count($feature["likes"]);
                    $out['system']['likes']['dislikescount'] = count($feature["dislikes"]) * -1;
                    $out['system']['likes']['upUserId'] = $feature["upUserId"];
                    $out['system']['likes']['downUserId'] = $feature["downUserId"];
                    $out['system']['likes']['allow'] = $feature["allow"];
                    $out['system']['likes']['total'] = $feature["total"];
                    $out['system']['likes']['error'] = $feature["error"];

                    $likeCmp = new NEWSFEED_CMP_Likes($feature["entityType"], $feature["entityId"], $feature["likes"]);
                    $out['system']['likes']['cmp'] = $likeCmp->render();
                }
            }
        }
        return $out;
    }
    
    protected function getLocalizedText( $textData )
    {
        if ( !is_array($textData) )
        {
            return $textData;
        }

        if(!isset($textData["key"]))
        {
            return $textData;
        }
        $keyData = explode("+", $textData["key"]);
        $vars = empty($textData["vars"]) ? array() : $textData["vars"];
        
        return OW::getLanguage()->text($keyData[0], $keyData[1], $vars);
    }

    /***
     * Get HTML to render replied text
     *
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $text
     * @return mixed|string
     */
    protected static function getReplyTextForView($text){
        if(strpos($text, '<br') !== false){
            $text = str_replace("\n","", $text);
            $text = str_replace("<br />","\n", $text);
            $text = str_replace("<br/>","\n", $text);
            $text = str_replace("<br>","\n", $text);
            $text = str_replace("</br>","\n", $text);
        }
        $text = UTIL_HtmlTag::stripTagsAndJs($text);
        $lines = explode("\n",$text);
        $count = count($lines);
        for($i=3;$i<$count;$i++){
            unset($lines[$i]);
        }
        $text = implode('<br />', $lines);
        $text = UTIL_String::truncate_html($text, 100);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event('emoji.before_render_string', array('string' => $text)));
        if (isset($stringRenderer->getData()['string'])) {
            $text = ($stringRenderer->getData()['string']);
        }
        return $text;
    }
 
    public function getTplData( $cycle = null )
    {
        $action = $this->action;
        $data = $this->getActionData($action);
        if(isset($data['status']))
        {
            if(isset($data['content']['vars'])) {
                $data['content']['vars']['status'] =  nl2br($data['status']);
            }
        }
        if (isset($this->sharedData['cache'])) {
            $data['cache'] = $this->sharedData['cache'];
        }
        if(isset($data['content']['vars']['status'])) {
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['status'], 'data' => $data)));
            if (isset($stringRenderer->getData()['string'])) {
                $data['content']['vars']['status'] = ($stringRenderer->getData()['string']);
            }
        }
        if($action->getPluginKey()=="video" && $action->getEntity()->type== "video_comments" && isset($data['content']['vars']['description'] )) {
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['description'], 'data' => $data)));
            if (isset($stringRenderer->getData()['string'])) {
                $data['content']['vars']['description'] = $stringRenderer->getData()['string'];
            }
        }
        $usersInfo = $this->sharedData['usersInfo'];
 
        $configs = $this->sharedData['configs'];
 
        $userNameEmbed = '<a href="' . $usersInfo['urls'][$action->getUserId()] . '"><b>' . $usersInfo['names'][$action->getUserId()] . '</b></a>';
        $assigns = empty($data['assign']) ? array() : $data['assign'];
        $replaces = array_merge(array(
            'user' => $userNameEmbed
        ), $assigns);
 
        /* render frmnews feed: remove br tags for old actions */
        if(isset($data['action']['entityType']) && $data['action']['entityType'] == 'news-entry'){
            if(isset($data['content']['vars']['description'])) {
                $data['content']['vars']['description'] = str_replace('<br />',"\r\n", $data['content']['vars']['description']);
            }
        }

        /* replace CR for br tags */
        if(isset($data['content']['vars']['description'])) {
            $data['content']['vars']['description'] = str_replace("\r\n\r\n", '<br />', $data['content']['vars']['description']);
            $data['content']['vars']['description'] = str_replace("\r\n", '<br />', $data['content']['vars']['description']);
        }

        // Here user role data is added to content
        if(is_array($data['content']) && isset($data['content']['vars']) && is_array($data['content']['vars'])){
            $event = new OW_Event('on.feed.item.add.role.data', array('usersInfo' => $usersInfo, 'userId' => $action->getUserId()));
            OW::getEventManager()->trigger($event);

            if(isset($event->getData()['roleLabel'])){
                $data['content']['vars']['roleLabel'] = $event->getData()['roleLabel'];
            }else{
                if ($usersInfo != null &&
                    isset($usersInfo["roleLabels"]) &&
                    $action != null &&
                    isset($usersInfo["roleLabels"][$action->getUserId()])) {
                    $data['content']['vars']['roleLabel'] = $usersInfo["roleLabels"][$action->getUserId()];
                }
            }
        }

        $data['content'] = $this->renderContent($data['content']);
 
        foreach ( $assigns as & $item )
        {
            $item = $this->renderContent($item);
        }

        $permalink = empty($data['permalink'])
            ? NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId(), $this->sharedData['feedType'], $this->sharedData['feedId'])
            : null;
 
        $string = $this->getLocalizedText($data['string']);
        $line = $this->getLocalizedText($data['line']);

        $creatorsInfo = $this->getActionUsersInfo($data);

/*        if (!empty($data['likeId']) && !empty($data['string'])){
            $data['context'] = false;
        }*/

        $item = array(
            'id' => $action->getId(),
            'view' => $data['view'],
            'toolbar' => $data['toolbar'],
            'string' => $this->processAssigns($string, $assigns),
            'line' => $this->processAssigns($line, $assigns),
            'content' => $this->processAssigns($data['content'], $assigns),
            'headline' => empty(UTIL_HtmlTag::stripTagsAndJs($data['content']))?',':UTIL_HtmlTag::stripTagsAndJs($data['content']),
            'context' => $data['context'],
            'entityType' => $data['action']['entityType'],
            'entityId' => $data['action']['entityId'],
            'createTime' => UTIL_DateTime::formatDate($data['action']['createTime']),
            'createDate' => date('Y-m-d', $data['action']['createTime']),
            'updateTime' => $action->getUpdateTime(),
            "user" => reset($creatorsInfo),
            'users' => $creatorsInfo,
            'permalink' => $permalink,
            'cycle' => $cycle,
            'activity' => $data['lastActivity'],
        );
 
        $item['autoId'] = $this->autoId;

        $item['features'] = $this->getFeatures($data);
        $item['contextActionMenu'] = $this->getContextMenu($data);

        if(OW::getConfig()->configExists('frmsecurityessentials','newsFeedShowDefault')){
            $newsFeedShowDefault=OW::getConfig()->getValue('frmsecurityessentials','newsFeedShowDefault');
        }
        if (isset($data['actionData']) && $data['actionData']!=null &&
            (!isset($newsFeedShowDefault) || $newsFeedShowDefault!=1 )) {
            if(isset($data['actionData']['string']['vars']['albumUrl'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $data['actionData']['string']['vars']['albumUrl'])));
                if (isset($stringRenderer->getData()['string'])) {
                    $data['actionData']['string']['vars']['albumUrl'] = $stringRenderer->getData()['string'];
                }
            }
            if ($this->displayType == NEWSFEED_CMP_Feed::DISPLAY_TYPE_ACTIVITY && $data['lastActivity'] !== null){
                $item['hasLastAvtivity']=true;
            }
            if(isset($data['actionData']['string'])) {
                $item['actionString'] = $this->getLocalizedText($data['actionData']['string']);
            }
            if(isset($data['actionData']['ownerId'])) {
                $item['ownerInfo'] = $this->getUserInfo($data['actionData']['ownerId']);
            }else  if(isset($data['actionData']['data']['userId'])) {
                $item['ownerInfo'] = $this->getUserInfo($data['actionData']['data']['userId']);
            }else if(isset($data['ownerId'])){
                $user = BOL_UserService::getInstance()->findUserById($data['ownerId']);
                if($user!=null) {
                    $item['ownerInfo'] = $this->getUserInfo($user->getId());
                }
            }
            if(isset($data['actionData']['time'])) {
                $item['actionTime'] = UTIL_DateTime::formatDate($data['actionData']['time']);
            }else{
                $item['actionTime'] = UTIL_DateTime::formatDate($data['action']['createTime']);
            }
        }
        if (!empty($data['reply_to'])){
            $reply_action_id = $data['reply_to'];
            $original_action = null;
            if (isset($data['cache']['actions'][$reply_action_id])) {
                $original_action = $data['cache']['actions'][$reply_action_id];
            }
            if ($original_action == null) {
                $original_action = NEWSFEED_BOL_Service::getInstance()->findActionById($reply_action_id);
            }
            if(isset($original_action)) {
                $actionData = json_decode($original_action->data, true);
                if ( !empty($actionData['data']['userId']) ) {
                    $author = BOL_UserService::getInstance()->getDisplayName($actionData['data']['userId']);
                    $link = BOL_UserService::getInstance()->getUserUrl($actionData['data']['userId']);
                    $text = '';
                    if(!empty($actionData['data']['status'])) {
                        $text = self::getReplyTextForView($actionData['data']['status']);
                    }
                    $reply_html = '<div class="ow_newsfeed_content_reply_to" data-reply-action-id="'.$reply_action_id.'">'
                        . OW::getLanguage()->text('newsfeed', 'in_reply_to', ['author' => $author, 'link' => $link, 'text' => $text])
                        . '</div>';
                    $item['replyToHTML'] = $reply_html;
                }
            }
        }
        $additionalClass = '';
        if($item['entityType'] == 'birthday'){
            $additionalClass = 'ow_birthday_newsfeed_item';
        }
        $item['additionalClass'] = $additionalClass;
        $item['content'] = '<div class="ow_newsfeed_content_status" >'.$item['content'].'</div>';
        $group = null;
        if (isset($this->sharedData['additionalParamList']['group'])) {
            $group = $this->sharedData['additionalParamList']['group'];
        }
        if (isset($this->sharedData['additionalParamList'])) {
            $data['additionalInfo'] = $this->sharedData['additionalParamList'];
        }
        if (isset($this->sharedData['cache'])) {
            $data['additionalInfo']['cache'] = $this->sharedData['cache'];
        }
        if (isset($this->action)) {
            if (isset($this->action->getData()['contextFeedType'])) {
                $entityType = $this->action->getData()['contextFeedType'];
                $entityId = $this->action->getData()['contextFeedId'];
                if ($entityType == 'groups' && isset($this->sharedData['cache']['groups'][$entityId])) {
                    $group = $this->sharedData['cache']['groups'][$entityId];
                }
            } else {
                $feeds = $this->action->getFeedList();
                if (!empty($feeds)) {
                    $feed = $feeds[0];
                    if ($feed->feedType == 'groups' && isset($this->sharedData['cache']['groups'][$feed->feedId])) {
                        $group = $this->sharedData['cache']['groups'][$feed->feedId];
                    }
                }
            }
        }
        $event = new OW_Event(FRMEventManager::ON_FEED_ITEM_RENDERER,array('data' => $data, 'group' => $group), $item);
        OW::getEventManager()->trigger($event);
        $item = $event->getData();
        if(isset($item['replyToHTML']))
        {
            $item['content']= $item['replyToHTML'] . $item['content'];
            unset($item['replyToHTML']);
        }
        if(isset($item['photoHTML']))
        {
            $item['content']=   $item['content'].$item['photoHTML'];
            unset($item['photoHTML']);
        }
        if(isset($item['attachmentPreviewHTML']))
        {
            $item['content']=   $item['content'].$item['attachmentPreviewHTML'];
            unset($item['attachmentPreviewHTML']);
        }
        if(isset($item['attachmentHTML']))
        {
            $item['content']=   $item['content'].$item['attachmentHTML'];
            unset($item['attachmentHTML']);
        }
        if(isset($data['sourceUser']))
        {
            $item['sourceUser']= $data['sourceUser'];
        }
        return $item;
    }
 
    public function renderMarkup( $cycle = null )
    {
        $item = $this->getTplData($cycle);
        $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_GET_TPL_DATA,array('item' => $item)));
        if(isset($event->getData()['hasMobileVersion']) && $event->getData()['hasMobileVersion']==false){
            return;
        }
        $this->generateJs($item);
        $commentlikeEvent= OW::getEventManager()->trigger(new OW_Event('add.newsfeed.comment.like.component',array('entityType'=> $this->action->getEntity()->type, 'entityId' => $this->action->getEntity()->id, 'value' => $this->action)));

        $cmItemArray = null;
        if(isset($commentlikeEvent->getData()['cmItemArray']))
        {
            $cmItemArray=$commentlikeEvent->getData()['cmItemArray'];
        }

        $this->assign('voteCmp', $cmItemArray);


        $dislikePostActivate = (boolean)OW::getConfig()->getValue('frmlike','dislikePostActivate');
        $this->assign('dislikePostActivate',$dislikePostActivate);
        $this->assign('item', $item);
        $this->assign("displayType", $this->displayType);

        $dislikePostActivate = (boolean)OW::getConfig()->getValue('frmlike','dislikePostActivate');
        $this->assign('isDislikeActivate', $dislikePostActivate);

        // Only for the item view page
        if ( $this->displayType == NEWSFEED_CMP_Feed::DISPLAY_TYPE_PAGE )
        {
            $content = null;
            if ( !empty($item["content"]) && is_array($item["content"]) )
            {
                if ( !empty($item["content"]["text"]) )
                {
                    $content = $item["content"]["text"];
                }
                else if ( !empty($item["content"]["status"]) )
                {
                    $content = empty($item["content"]["status"]);
                }
            }
            else if ( !empty($item["content"]) )
            {
                $content = $item["content"];
            }
            
            $description = empty($item["string"]) ? $content : $item["string"];
            OW::getDocument()->setDescription($item['user']['name'] . " " . strip_tags($description));
        }
        
        return $this->render();
    }
}