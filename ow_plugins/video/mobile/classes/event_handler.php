<?php
/**
 * Mobile video event handler
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.mobile.classes
 * @since 1.6.0
 */
class VIDEO_MCLASS_EventHandler
{
    /**
     * @var VIDEO_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return VIDEO_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct() { }

    public function init()
    {
        VIDEO_CLASS_EventHandler::getInstance()->genericInit();
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        OW::getEventManager()->bind('base.mobile_top_menu_add_options', array($this, 'onMobileTopMenuAddLink'));
    }

    public function onMobileTopMenuAddLink( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('video', 'add') )
        {
            $event->add(array(
                'prefix' => 'video',
                'key' => 'video_mobile',
                'url' => OW::getRouter()->urlFor('VIDEO_MCTRL_Add', 'index')
            ));
        }
    }

    public function onNotificationRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $params['data'];

        if (empty($params['entityType']) || ($params['entityType'] !== 'video-add_comment' && $params['entityType'] != 'video-add_rate')) {
            return;
        }
        if ($params['entityType'] == 'video-add_rate') {
            $event->setData($data);
            return;
        }
        $commentId = $params['entityId'];
        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        if (!$comment) {
            return;
        }
        $commEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
        if (!$commEntity) {
            return;
        }

        $entityId = $commEntity->entityId;
        $userId = $comment->userId;

        $clipService = VIDEO_BOL_ClipService::getInstance();
        $userService = BOL_UserService::getInstance();

        $clip = $clipService->findClipById($entityId);
        if (OW::getUser()->getId() != $clip->userId)
        {
            $data = $params['data'];
            $event->setData($data);
        }
        else
        {
            $url = OW::getRouter()->urlForRoute('view_clip', array('id' => $entityId));
            $langVars = array(
                'userName' => $userService->getDisplayName($userId),
                'userUrl' => $userService->getUserUrl($userId),
                'videoUrl' => $url,
                'videoTitle' => UTIL_String::truncate(strip_tags($clip->title), 60, '...' ),
                'comment' => UTIL_String::truncate( $comment->getMessage(), 120, '...' )
            );

            $data['string'] = array('key' => 'video+email_notifications_comment', 'vars' => $langVars);
            $data['url'] = $url;
            $event->setData($data);
        }
    }
}