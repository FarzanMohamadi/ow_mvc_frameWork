<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.frmnews.classes
 * @since 1.6.0
 */
class FRMNEWS_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMNEWS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMNEWS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        $service = EntryService::getInstance();
        OW::getEventManager()->bind('feed.on_item_render', array($service, 'feedOnItemRenderActivity'));
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ($params['pluginKey'] != 'frmnews' || ($params['entityType'] != 'news-add_comment' && $params['entityType'] != 'news-add_news')) {
            return;
        }

        $data = $params['data'];

        if (!isset($data['avatar']['urlInfo']['vars']['username'])) {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        if (!$user) {
            return;
        }
        $entryService = EntryService::getInstance();

        if ($params['entityType'] == 'news-add_comment') {
            $commentId = $params['entityId'];
            $comment = BOL_CommentService::getInstance()->findComment($commentId);
            if (!$comment) {
                return;
            }
            $commEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
            if (!$commEntity) {
                return;
            }
            $entry = $entryService->findById($commEntity->entityId);
            $stringKey = 'frmnews+comment_notification_string';
        }
        if ($params['entityType'] == 'news-add_news') {
            $entry = EntryService::getInstance()->findById($params['entityId']);
            $stringKey = 'frmnews+news_notification_string';
        }
        if ($entry == null) {
            return;
        }
        if (OW::getUser()->getId() != $entry->authorId) {
            $data = $params['data'];
            $e->setData($data);
        } else {
            $langVars = array(
                'actorUrl' => $userService->getUserUrl($user->id),
                'actor' => $userService->getDisplayName($user->id),
                'url' => OW::getRouter()->urlForRoute('entry', array('id' => $entry->getId())),
                'title' => UTIL_String::truncate( $entry->getTitle(), 60, '...' )
            );
            if ($params['entityType'] == 'news-add_comment') {
                $langVars['comment'] =  UTIL_String::truncate( $comment->getMessage(), 120, '...' );
            }

            $data['string'] = array('key' => $stringKey, 'vars' => $langVars);

            $e->setData($data);
        }

        if (empty($data["contentImage"]["src"])){
            $data["contentImage"]["src"]=EntryService::getInstance()->generateImageUrl();
            $data["contentImage"]["newsImageInfo"]= BOL_AvatarService::getInstance()->getAvatarInfo($params['entityId'], $data["contentImage"]["src"],'news');

            $e->setData($data);
        }
    }
}