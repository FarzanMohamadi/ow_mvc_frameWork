<?php
/**
 * Mobile photo event handler
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.mobile.classes
 * @since 1.6.0
 */
class PHOTO_MCLASS_EventHandler
{
    /**
     * @var PHOTO_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return PHOTO_MCLASS_EventHandler
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

    public function onAddProfileContentMenu( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        $lang = OW::getLanguage();
        $linkId = FRMSecurityProvider::generateUniqueId('photo');

        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();
        $exclude = array();
        $newsfeedAlbum = $albumService->getNewsfeedAlbum($userId);

        if ( $newsfeedAlbum !== null )
        {
            $exclude[] = $newsfeedAlbum->id;
        }

        if ( !$albumService->countUserAlbums($userId, $exclude) )
        {
            return;
        }

        $albumList = $albumService->findUserAlbumList($userId, 1, 1, $exclude);
        $cover = !empty($albumList[0]['cover']) ? $albumList[0]['cover'] : null;

        $username = BOL_UserService::getInstance()->getUserName($userId);
        $url = OW::getRouter()->urlForRoute('photo_user_albums', array('user' => $username));
        $resultArray = array(
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LABEL => $lang->text('photo', 'photos_album'),
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_HREF => $url,
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_ID => $linkId,
            BASE_MCMP_ProfileContentMenu::DATA_KEY_LINK_CLASS => 'owm_profile_nav_photo',
            BASE_MCMP_ProfileContentMenu::DATA_KEY_THUMB => $cover
        );

        $event->add($resultArray);
    }

    public function onMobileTopMenuAddLink( BASE_CLASS_EventCollector $event )
    {
        if ( OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('photo', 'upload') )
        {
            $event->add(array(
                'prefix' => 'photo',
                'key' => 'mobile_photo',
                'url' => OW::getRouter()->urlForRoute('photo_upload')
            ));
        }
    }

    public function onNotificationRender( OW_Event $e )
    {
        $params = $e->getParams();

        if ( $params['pluginKey'] != 'photo' || ($params['entityType'] != 'photo-add_comment' && $params['entityType'] != 'photo-add_rate' && $params['entityType'] != 'photo_like' ))
        {
            return;
        }

        $data = $params['data'];

        if ( !isset($data['avatar']['urlInfo']['vars']['username']) )
        {
            return;
        }

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($data['avatar']['urlInfo']['vars']['username']);
        if ( !$user )
        {
            return;
        }

        if($params['entityType'] == 'photo_like' || $params['entityType'] == 'photo-add_rate')
        {
            $e->setData($data);
            return;
        }

        $commentId = $params['entityId'];
        $comment = BOL_CommentService::getInstance()->findComment($commentId);
        if ( !$comment )
        {
            return;
        }
        $commEntity = BOL_CommentService::getInstance()->findCommentEntityById($comment->commentEntityId);
        if ( !$commEntity )
        {
            return;
        }

        $entityId = $commEntity->entityId;
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $ownerId = $photoService->findPhotoOwner($entityId);
        if (OW::getUser()->getId() != $ownerId)
        {
            $data = $params['data'];
        }
        else {
            $langVars = array(
                'userUrl' => $userService->getUserUrl($user->id),
                'userName' => $userService->getDisplayName($user->id),
                'photoUrl' => OW::getRouter()->urlForRoute('view_photo', array('id' => $commEntity->entityId)),
                'comment' => UTIL_String::truncate($comment->getMessage(), 120, '...')
            );
            $data['string'] = array('key' => 'photo+email_notifications_comment', 'vars' => $langVars);
        }
        $e->setData($data);
    }

    public function init()
    {
        PHOTO_CLASS_EventHandler::getInstance()->genericInit();

        $em = OW::getEventManager();

        $em->bind(BASE_MCMP_ProfileContentMenu::EVENT_NAME, array($this, 'onAddProfileContentMenu'));
        $em->bind('base.mobile_top_menu_add_options', array($this, 'onMobileTopMenuAddLink'));
        $em->bind('mobile.notifications.on_item_render', array($this, 'onNotificationRender'));
        $em->bind(PHOTO_CLASS_EventHandler::EVENT_BEFORE_MULTIPLE_PHOTO_DELETE, array(PHOTO_CLASS_EventHandler::getInstance(), 'onBeforeMultiplePhotoDelete'));
    }
}