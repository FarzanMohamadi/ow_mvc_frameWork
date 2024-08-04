<?php
/**
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
final class BOL_RateService
{
    const CONFIG_MAX_RATE = 'max_rate';

    /**
     * @var BOL_RateDao
     */
    private $rateDao;
    /**
     * @var array
     */
    private $configs = array();
    /**
     * Singleton instance.
     *
     * @var BOL_RateService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_RateService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->rateDao = BOL_RateDao::getInstance();
        $this->configs[self::CONFIG_MAX_RATE] = 5;
    }

    /**
     * @return array
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Returns config value.
     *
     * @param string $name
     * @return mixed
     */
    public function getConfig( $name )
    {
        return $this->configs[trim($name)];
    }

    /**
     * Saves and updates rate items.
     *
     * @param BOL_Rate $rateItem
     */
    public function saveRate( BOL_Rate $rateItem )
    {
        $this->rateDao->save($rateItem);
    }

    /**
     * Deletes rate item by id.
     *
     * @param integer $rateId
     */
    public function deleteRate( $rateId )
    {
        $this->rateDao->deleteById($rateId);
    }

    /**
     * Returns rate item for provided entity id, entity type and user id.
     *
     * @param integer $entityId
     * @param string $entityType
     * @param integer $userId
     * @return BOL_Rate
     */
    public function findRate( $entityId, $entityType, $userId )
    {
        return $this->rateDao->findRate($entityId, $entityType, $userId);
    }

    /**
     * Returns rate info for provided entity id and entity type.
     * Example: array( 'avg_rate' => 5, 'rates_count' => 35 ).
     *
     * @param integer $entityId
     * @param integer $entityType
     * @return array
     */
    public function findRateInfoForEntityItem( $entityId, $entityType )
    {
        return $this->rateDao->findEntityItemRateInfo($entityId, $entityType);
    }

    /**
     * Returns rate info for provided entity id and entity type.
     * Example: array( 'entity_id' => array( 'avg_score' => 5, 'rates_count' => 35 ) ).
     *
     * @param array<integer> $entityIdList
     * @param integer $entityType
     * @return array
     */
    public function findRateInfoForEntityList( $entityType, $entityIdList )
    {
        $result = $this->rateDao->findRateInfoForEntityList($entityType, $entityIdList);

        $resultArray = array();

        foreach ( $result as $item )
        {
            $resultArray[$item['entityId']] = $item;
        }

        foreach ( $entityIdList as $id )
        {
            if ( !isset($resultArray[$id]) )
            {
                $resultArray[$id] = array('rates_count' => 0, 'avg_score' => 0);
            }
        }

        return $resultArray;
    }

    public function findMostRatedEntityList( $entityType, $first, $count, $exclude = null )
    {
        $arr = $this->rateDao->findMostRatedEntityList($entityType, $first, $count, $exclude);

        $resultArray = array();

        foreach ( $arr as $value )
        {
            $resultArray[$value['id']] = $value;
        }

        return $resultArray;
    }

    public function findMostRatedEntityCount( $entityType, $exclude = null )
    {
        return $this->rateDao->findMostRatedEntityCount($entityType, $exclude);
    }

    public function setEntityStatus( $entityType, $entityId, $status = true )
    {
        $status = $status ? 1 : 0;

        $this->rateDao->updateEntityStatus($entityType, $entityId, $status);
    }

    /**
     * Removes all user rates.
     *
     * @param integer $userId
     */
    public function deleteUserRates( $userId )
    {
        $this->rateDao->deleteUserRates($userId);
    }

    /**
     * Removes all entity item rates.
     *
     * @param integer $entityId
     * @param string $entityType
     */
    public function deleteEntityRates( $entityId, $entityType )
    {
        $this->rateDao->deleteEntityItemRates($entityId, $entityType);
    }

    public function deleteEntityTypeRates( $entityType )
    {
        $this->rateDao->deleteByEntityType($entityType);
    }

    public function findUserSocre( $userId, $entityType, array $entityIdList )
    {
        $score = $this->rateDao->findUserScore($userId, $entityType, $entityIdList);
        $result = array();

        foreach ( $score as $val )
        {
            $result[$val[BOL_RateDao::ENTITY_ID]] = $val[BOL_RateDao::SCORE];
        }

        foreach ( array_diff($entityIdList, array_keys($result)) as $id )
        {
            $result[$id] = 0;
        }

        return $result;
    }

    public function processUpdateRate($entityId, $entityType, $rate, $userId) {
        if ($userId == null) {
            return array('valid' => false);
        }

        $rate = (int) $rate;
        if ($rate > 5) {
            $rate = 5;
        } else if ($rate < 0) {
            $rate = 0;
        }

        $canRate = BOL_RateService::getInstance()->canUserRate($entityId, $entityType);
        if ($canRate['valid'] == false) {
            return $canRate;
        }

        $rateItem = $this->findRate($entityId, $entityType, $userId);

        if ( $rateItem === null )
        {
            $rateItem = new BOL_Rate();
            $rateItem->setEntityId($entityId)->setEntityType($entityType)->setUserId($userId)->setActive(true);
        }

        $rateItem->setScore($rate)->setTimeStamp(time());

        $this->saveRate($rateItem);

        $ownerId = $canRate['ownerId'];

        BOL_RateService::getInstance()->addToNotificationList($entityId, $entityType, $ownerId);

        return array('valid' => true, 'rateItem' => $rateItem);
    }

    public function canUserRate($entityId, $entityType) {
        if (!OW::getUser()->isAuthenticated()) {
            return array('valid' => false);
        }
        $ownerId = null;
        $canRate = false;
        $userId = OW::getUser()->getId();

        if($entityType=='photo_rates' && FRMSecurityProvider::checkPluginActive('photo', true)) {
            $photoService = PHOTO_BOL_PhotoService::getInstance();
            $photo = $photoService->findPhotoById($entityId);
            if ($photo != null) {
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                if ($album != null) {
                    $ownerId = $album->userId;
                }
                if (OW::getUser()->isAuthorized('photo', 'view') &&
                    OW::getUser()->isAuthorized('photo', 'add_comment'))
                {
                    if ($ownerId != null && $photoService->canUserSeePhoto($entityId)) {
                        $canRate = true;
                    }
                }
                else
                {
                    return array('valid' => false, 'reason' => 'no_access');
                }
            }
        }
        else if($entityType=='video_rates' && FRMSecurityProvider::checkPluginActive('video', true)) {
            $clipService = VIDEO_BOL_ClipService::getInstance();
            $clip = $clipService->findClipById($entityId);
            if ($clip != null) {
                $ownerId = $clip->userId;
                if (OW::getUser()->isAuthorized('video', 'view') &&
                    OW::getUser()->isAuthorized('video', 'add_comment'))
                {
                    $canSeeVideo = $clipService->canUserSeeVideoOfUserId($userId, $entityId);
                    if ($canSeeVideo) {
                        $canRate = true;
                    }
                }
                else
                {
                    return array('valid' => false, 'reason' => 'no_access');
                }
            }
        }
        else if($entityType=='blog-post' && FRMSecurityProvider::checkPluginActive('blogs', true)) {
            $blogService = PostService::getInstance();
            $blog = $blogService->findById($entityId);
            if ($blog != null) {
                $ownerId = $blog->authorId;
                if (OW::getUser()->isAuthorized('blogs', 'view') &&
                    OW::getUser()->isAuthorized('blogs', 'add_comment'))
                {
                    /* Check privacy permissions */
                    $eventParams = array(
                        'action' => PostService::PRIVACY_ACTION_VIEW_BLOG_POSTS,
                        'ownerId' => $userId,
                        'viewerId' => OW::getUser()->getId()
                    );
                    $canRate = true;

                    try
                    {
                        OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                    }
                    catch ( RedirectException $ex )
                    {
                        $canRate = false;
                    }
                }
                else
                {
                    return array('valid' => false, 'reason' => 'no_access');
                }
            }
        }
        else if($entityType=='news-entry' && FRMSecurityProvider::checkPluginActive('frmnews', true)) {
            $newsService = EntryService::getInstance();
            $news = $newsService->findById($entityId);
            if ($news != null) {
                $ownerId = $news->authorId;
                if (OW::getUser()->isAuthorized('frmnews', 'view') &&
                    OW::getUser()->isAuthorized('frmnews', 'add_comment'))
                {
                    /* Check privacy permissions */
                    $eventParams = array(
                        'action' => 'view_my_feed',
                        'ownerId' => $ownerId,
                        'viewerId' => $userId
                    );
                    $canRate = true;

                    try
                    {
                        OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
                    }
                    catch ( RedirectException $ex )
                    {
                        $canRate = false;
                    }
                }
                else
                {
                    return array('valid' => false, 'reason' => 'no_access');
                }
            }
        }

        if ($ownerId != null && $userId == $ownerId) {
            return array('valid' => false, 'reason' => 'same_user');
        }

        if ($ownerId != null && BOL_UserService::getInstance()->isBlocked($userId, $ownerId)) {
            return array('valid' => false, 'reason' => 'user_block');
        }

        return array('valid' => $canRate, 'ownerId' => $ownerId);
    }

    public function addToNotificationList($entityId,$entityType, $ownerId = null)
    {
        $userId = OW::getUser()->getId();
        if ($ownerId == null && isset($_POST['ownerId'])) {
            $ownerId = (int) $_POST['ownerId'];
        }
        if ($ownerId == null) {
            throw new InvalidArgumentException('`ownerId` in addToNotificationList function are required');
        }
        $userService = BOL_UserService::getInstance();
        if($entityType=='photo_rates')
        {
            $photoService = PHOTO_BOL_PhotoService::getInstance();
            $url = OW::getRouter()->urlForRoute('view_photo', array('id' => $entityId));
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $e = new OW_Event('notifications.add', array(
                'pluginKey' => 'photo',
                'entityType' => 'photo-add_rate',
                'entityId' => $entityId,
                'action' => 'photo-add_rate',
                'userId' => $ownerId,
                'time' => time()
            ), array(
                'avatar' => $avatars[$userId],
                'string' => array(
                    'key' => 'photo+email_notifications_rate',
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'photoUrl' => $url
                    )
                ),
                'url' => $url,
                'contentImage' => $photoService->getPhotoUrlByPhotoInfo($entityId, PHOTO_BOL_PhotoService::TYPE_SMALL, array(), true)
            ));
            OW::getEventManager()->trigger($e);
        }
        else if($entityType=='video_rates')
        {
            $clipService = VIDEO_BOL_ClipService::getInstance();
            $clip = $clipService->findClipById($entityId);
            $url = OW::getRouter()->urlForRoute('view_clip', array('id' => $entityId));
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId));
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'video',
                'entityType' => 'video-add_rate',
                'entityId' => $entityId,
                'action' => 'video-add_rate',
                'userId' => $ownerId,
                'time' => time()
            ), array(
                'avatar' => $avatars[$userId],
                'string' => array(
                    'key' => 'video+email_notifications_rate',
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'videoUrl' => $url,
                        'videoTitle' => strip_tags($clip->title)
                    )
                ),
                'url' => $url
            ));
            OW::getEventManager()->trigger($event);

        }
        else
            return;

    }
}