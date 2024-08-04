<?php
/**
 * Feed Item component
 *
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_MCMP_FeedItem extends NEWSFEED_CMP_FeedItem
{
    protected $itemPermalink = null;

    public function getContextMenu($data) 
    {
        $items = array();
        
        $order = 1;
        
        foreach( $data['contextMenu'] as $action )
        {
            $items[] = array_merge(array(
                "group" => "newsfeed",
                'label' => null,
                'order' => $order,
                'class' => null,
                'url' => null,
                'id' => null,
                'attributes' => array()
            ), $action);

            $order++;
        }

        $contextMenuCMPEvent = OW::getEventManager()->trigger(new OW_Event('on.before.context.menu.render', array('items' => $items)));
        if(isset($contextMenuCMPEvent->getData()['cmp'])){
            return $contextMenuCMPEvent->getData()['cmp']->render();
        }
        
        $menu = new BASE_MCMP_ContextAction($items);
        
        return $menu->render();
    }

    public function generateJs( $data )
    {
        $js = UTIL_JsGenerator::composeJsString('
            window.ow_newsfeed_feed_list[{$feedAutoId}].actions[{$uniq}] = new NEWSFEED_MobileFeedItem({$autoId}, window.ow_newsfeed_feed_list[{$feedAutoId}]);
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
                'displayType' => $this->displayType
            )
        ));

        OW::getDocument()->addOnloadScript($js, 50);
    }

    protected function getFeatures( $data )
    {
        $configs = $this->sharedData['configs'];
        $cache = array();
        if (isset($this->sharedData['cache'])) {
            $cache = $this->sharedData['cache'];
        }
        $feturesData = $this->getFeaturesData($data);
        
        $featureDefaults = array(
            "uniqId" => FRMSecurityProvider::generateUniqueId("nf-feature-"),
            "class" => "",
            "active" => false,
            "count" => null,
            "error" => null,
            "url" => "javascript://",
            "hideButton" => false,
            "innerHtml" => null,
            "html" => null
        );

        $features = array();
        $js = UTIL_JsGenerator::newInstance();
        $isChannel=false;
        $hideCommentFeatures=false;
        $hideLikeFeatures=false;
        $channelEvent = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.load',
            array('action'=>$this->action, 'cache' => $cache)));
        if ((isset($channelEvent->getData()['isChannel']) && $channelEvent->getData()['isChannel']==true)) {
            $isChannel = true;
        }
        if ((isset($channelEvent->getData()['hideCommentFeatures']) && $channelEvent->getData()['hideCommentFeatures']==true)) {
            $hideCommentFeatures = true;
        }
        if ((isset($channelEvent->getData()['hideLikeFeatures']) && $channelEvent->getData()['hideLikeFeatures']==true)) {
            $hideLikeFeatures = true;
        }

        if( !$isChannel) {
            // Likes
            if (!$hideLikeFeatures) {
                if (!empty($feturesData["system"]["likes"])) {
                    $feature = $feturesData["system"]["likes"];
                    $likeCmp = new NEWSFEED_MCMP_Likes($feature["entityType"], $feature["entityId"], $feature["likes"]);
                    $likeString = false;
                    if (isset($likeCmp->assignedVars["string"])) {
                        $likeString = $likeCmp->assignedVars["string"];
                    }
                    $features["likes"] = array_merge($featureDefaults, array(
                        "uniqId" => FRMSecurityProvider::generateUniqueId("nf-feature-"),
                        "class" => "owm_newsfeed_control_like",
                        "active" => $feature["liked"],
                        "count" => $feature["count"],
                        "likes" => $likeString,
                        "likeStringUniqId" => FRMSecurityProvider::generateUniqueId("nf-feature-"),
                        "error" => $feature["error"],
                        "url" => "javascript://",
                        "uri" => urlencode(OW::getRequest()->getRequestUri()),
                        "ownerId" => $this->action->getUserId(),
                        "currentUserId" => OW::getUser()->getId()
                    ),
                    $feature);
                    $js->newObject("likeFeature", "NEWSFEED_MobileFeatureLikes", array(
                        $feature["entityType"], $feature["entityId"], $features["likes"]
                    ));
                }
            }
            // Comments
            if (!$hideCommentFeatures) {
                if (!empty($feturesData["system"]["comments"])) {
                    $feature = $feturesData["system"]["comments"];

                    $comments = array_merge($featureDefaults, array(
                        "uniqId" => FRMSecurityProvider::generateUniqueId("nf-feature-"),
                        "class" => "owm_newsfeed_control_comment",
                        "active" => false,
                        "count" => $feature["count"],
                        "url" => OW::getRequest()->buildUrlQueryString($this->itemPermalink, array(), "comments")
                    ));

                    if ($this->displayType == NEWSFEED_MCMP_Feed::DISPLAY_TYPE_PAGE) {
                        $comments["hideButton"] = true;

                        $commentsParams = new BASE_CommentsParams($feature["authGroup"], $feature["entityType"]);
                        $commentsParams->setEntityId($feature["entityId"]);
                        $commentsParams->setCommentCountOnPage($configs['comments_count']);
                        $commentsParams->setLoadMoreCount(100);
                        $commentsParams->setBatchData($feature["comments"]);
                        //$commentsParams->setDisplayType(BASE_CommentsParams::DISPLAY_TYPE_BOTTOM_FORM_WITH_PARTIAL_LIST_AND_MINI_IPC);
                        $commentsParams->setOwnerId($data['action']['userId']);
                        $commentsParams->setWrapInBox(false);

                        if (!empty($feature['error'])) {
                            $commentsParams->setErrorMessage($feature['error']);
                        }

                        if (isset($feature['allow'])) {
                            $commentsParams->setAddComment($feature['allow']);
                        }
                        $commentCmp = new BASE_MCMP_Comments($commentsParams);
                        $comments['html'] = $commentCmp->render();
                    }

                    $features[] = $comments;
                }
            }
        }
        
        $jsString = $js->generateJs();
        if ( trim($jsString) )
        {
            OW::getDocument()->addOnloadScript($js);
        }
        
        foreach ( $feturesData["custom"] as $customFeature )
        {
            $features[] = array_merge($featureDefaults, $customFeature);
        }
        
        $visibleCount = 0;
        foreach ( $features as $f )
        {
            if ( empty($f["hideButton"]) )
            {
                $visibleCount++;
            }
        }

        $plugin_frmmenu = BOL_PluginService::getInstance()->findPluginByKey("frmmenu");
        if (isset($plugin_frmmenu) && $plugin_frmmenu->isActive())
            $this->assign("frmmenu_active", true);

        return array(
            "items" => $features,
            "buttonsCount" => $visibleCount
        );
    }
    
    protected function applyRespond( $data, $activity )
    {
        if ( empty($activity["data"]["string"]) )
        {
            return $data;
        }
        
        $userId = empty($activity["data"]["action"]["userId"])
                ? $activity["userId"]
                : $activity["data"]["action"]["userId"];
        
        $data["respond"] = array(
            "user" => $this->getUserInfo($userId),
            "text" => $this->getLocalizedText($activity["data"]["string"])
        );
        
        return $data;
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
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content']['vars']['description'])));
            if (isset($stringRenderer->getData()['string'])) {
                $data['content']['vars']['description'] = $stringRenderer->getData()['string'];
            }
        }
        /* replace CR for br tags */
        if(isset($data['content']['vars']['description'])) {
            $data['content']['vars']['description'] = str_replace("\r\n\r\n", '<br />', $data['content']['vars']['description']);
            $data['content']['vars']['description'] = str_replace("\r\n", '<br />', $data['content']['vars']['description']);
        }

        $permalink = empty($data['permalink'])
            ? NEWSFEED_BOL_Service::getInstance()->getActionPermalink($action->getId(), $this->sharedData['feedType'], $this->sharedData['feedId'])
            : $data['permalink'];
        
        $this->itemPermalink = $permalink;

        $userId = (int) $data['action']['userId'];

        $content = null;
        if ( is_array($data["content"]) && !empty($data["content"]["format"]) )
        {
            $vars = empty($data["content"]["vars"]) ? array() : $data["content"]["vars"];
            $content = $this->renderFormat($data["content"]["format"], $vars);
        }
        
        $respond = empty($data["respond"]) ? array() : $data["respond"];
        $creatorsInfo = $this->getActionUsersInfo($data);
        
        $desktopUrl = $permalink;
        
        if ( strpos($permalink, OW_URL_HOME) === 0 )
        {
            $permalinkUri = str_replace(OW_URL_HOME, "", $permalink);
            
            $desktopUrl = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute("base.desktop_version"), array(
                "back-uri" => urlencode($permalinkUri)
            ));
        }

        $localizedString = $this->getLocalizedText($data['string']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $localizedString)));
        if(isset($stringRenderer->getData()['string'])){
            $localizedString = $stringRenderer->getData()['string'];
        }
        $item = array(
            'id' => $action->getId(),
            'view' => $data['view'],
            'toolbar' => $data['toolbar'],
            'string' => $localizedString,
            'line' => $this->getLocalizedText($data['line']),
            'content' => $content,
            'headline' => empty(UTIL_HtmlTag::stripTagsAndJs($content))?',':UTIL_HtmlTag::stripTagsAndJs($content),
            'context' => $data['context'],
            'entityType' => $data['action']['entityType'],
            'entityId' => $data['action']['entityId'],
            'createTime' => UTIL_DateTime::formatDate($data['action']['createTime']),
            'createDate' => date('Y-m-d', $data['action']['createTime']),
            'updateTime' => $action->getUpdateTime(),
            'respond' => $respond,
            "responded" => !empty($respond),

            "user" => reset($creatorsInfo),
            'users' => $creatorsInfo,
            'permalink' => $permalink,

            'cycle' => $cycle,
            "disabled" => !empty($data["disabled"]) && $data["disabled"],
            "desktopUrl" => $desktopUrl
        );
        
        $item['autoId'] = $this->autoId;

        $item['features'] = $this->getFeatures($data);
        $item['contextActionMenu'] = $this->getContextMenu($data);

        if (!empty($data['reply_to'])){
            $reply_action_id = $data['reply_to'];
            $original_action = NEWSFEED_BOL_Service::getInstance()->findActionById($reply_action_id);
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

        $event = new OW_Event(FRMEventManager::ON_FEED_ITEM_RENDERER,array('data' => $data), $item);
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
    
    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        // Switch to mobile template
        $plugin = OW::getPluginManager()->getPlugin("newsfeed");
        $this->setTemplate($plugin->getMobileCmpViewDir() . "feed_item.html");
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("newsfeed")->getStaticCssUrl() . 'newsfeed.css');
    }
}