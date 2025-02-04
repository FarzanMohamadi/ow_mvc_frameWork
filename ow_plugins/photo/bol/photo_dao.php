<?php
/**
 * Data Access Object for `photo` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoDao extends OW_BaseDao
{
    CONST PHOTO_PREFIX = 'photo_';
    CONST PHOTO_PREVIEW_PREFIX = 'photo_preview_';
    CONST PHOTO_ORIGINAL_PREFIX = 'photo_original_';
    CONST PHOTO_SMALL_PREFIX = 'photo_small_';
    CONST PHOTO_FULLSCREEN_PREFIX = 'photo_fullscreen_';
    
    const CACHE_TAG_PHOTO_LIST = 'photo.list';
    
    CONST PHOTO_ENTITY_TYPE = 'photo';
    
    CONST PRIVACY = 'privacy';
    CONST PRIVACY_EVERYBODY = 'everybody';
    CONST PRIVACY_FRIENDS_ONLY = 'friends_only';
    CONST PRIVACY_ONLY_FOR_ME = 'only_for_me';

    CONST STATUS_APPROVAL = 'approval';
    CONST STATUS_APPROVED = 'approved';
    CONST STATUS_BLOCKED = 'blocked';

    const ENTITY_TYPE_USER = 'user';
    
    private $typeToPrefix;
    
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class.
     *
     * @return PHOTO_BOL_PhotoDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    protected function __construct()
    {
        parent::__construct();
        
        $this->typeToPrefix = array(
            PHOTO_BOL_PhotoService::TYPE_ORIGINAL => self::PHOTO_ORIGINAL_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_FULLSCREEN => self::PHOTO_FULLSCREEN_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_MAIN => self::PHOTO_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_PREVIEW => self::PHOTO_PREVIEW_PREFIX,
            PHOTO_BOL_PhotoService::TYPE_SMALL => self::PHOTO_SMALL_PREFIX
        );
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_Photo';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo';
    }

    /**
     * Get photo/preview URL
     *
     * @param int $id
     * @param string $type
     * @param string $hash
     * @param $dimension
     * @param $returnPath
     * @return string
     */

    public function getPhotoUrlByType( $id, $type, $hash, $dimension = NULL, $returnPath = false )
    {
        if ( !isset($this->typeToPrefix[$type]) )
        {
            return NULL;
        }
        
        $storage = OW::getStorage();
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        
        if ( in_array($type, array(PHOTO_BOL_PhotoService::TYPE_FULLSCREEN, PHOTO_BOL_PhotoService::TYPE_PREVIEW, PHOTO_BOL_PhotoService::TYPE_SMALL)) )
        {
            if ( $dimension === NULL )
            {
                $photo = $this->findById($id);
                $dimension = !empty($photo->dimension) ? $photo->dimension : NULL;
            }
            
            if ( empty($dimension) )
            {
                switch ( $type )
                {
                    case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                    case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                        $ext = '.jpg';
                        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . $this->typeToPrefix[$type] . $id . $hashSlug)));
                        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
                            $ext = $checkAnotherExtensionEvent->getData()['ext'];
                        }
                        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . $ext);
                    case PHOTO_BOL_PhotoService::TYPE_SMALL:
                        $ext = '.jpg';
                        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug)));
                        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
                            $ext = $checkAnotherExtensionEvent->getData()['ext'];
                        }
                        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug . $ext);
                }
            }
        }
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . $this->typeToPrefix[$type] . $id . $hashSlug)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $storage->getFileUrl($userfilesDir . $this->typeToPrefix[$type] . $id . $hashSlug . $ext, $returnPath);
    }

    /**
     * Get photo/preview URL
     *
     * @param int $id
     * @param $hash
     * @param boolean $preview
     * @return string
     */
    public function getPhotoUrl( $id, $hash, $preview = false, $dimension = NULL )
    {
        $storage = OW::getStorage();
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        
        if ( $preview )
        {
            if ( $dimension === NULL )
            {
                $photo = $this->findById($id);
                $dimension = !empty($photo->dimension) ? $photo->dimension : NULL;
            }
            
            if ( empty($dimension) )
            {
                $ext = '.jpg';
                $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug)));
                if(isset($checkAnotherExtensionEvent->getData()['ext'])){
                    $ext = $checkAnotherExtensionEvent->getData()['ext'];
                }
                return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . $ext);
            }
            else
            {
                $ext = '.jpg';
                $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug)));
                if(isset($checkAnotherExtensionEvent->getData()['ext'])){
                    $ext = $checkAnotherExtensionEvent->getData()['ext'];
                }
                return $storage->getFileUrl($userfilesDir . self::PHOTO_PREVIEW_PREFIX . $id . $hashSlug . $ext);
            }
        }
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $storage->getFileUrl($userfilesDir . self::PHOTO_PREFIX . $id . $hashSlug . $ext);
    }

    public function getPhotoFullsizeUrl( $id, $hash )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $storage = OW::getStorage();
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $userfilesDir . self::PHOTO_ORIGINAL_PREFIX . $id . $hashSlug)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $storage->getFileUrl($userfilesDir . self::PHOTO_ORIGINAL_PREFIX . $id . $hashSlug . $ext);
    }

    /**
     * Get directory where 'photo' plugin images are uploaded
     *
     * @return string
     */
    public function getPhotoUploadDir()
    {
        return OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
    }

    /**
     * Get path to photo in file system
     *
     * @param int $photoId
     * @param $hash
     * @param string $type
     * @return string
     */
    public function getPhotoPath( $photoId, $hash, $type = '' )
    {
        $hashSlug = !empty($hash) ? '_' . $hash : '';
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('photoId' => $photoId, 'hash' => $hash, 'type' => $type)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        switch ( $type )
        {
            case PHOTO_BOL_PhotoService::TYPE_MAIN:
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                return $this->getPhotoUploadDir() . self::PHOTO_PREVIEW_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_ORIGINAL:
                return $this->getPhotoUploadDir() . self::PHOTO_ORIGINAL_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_SMALL:
                return $this->getPhotoUploadDir() . self::PHOTO_SMALL_PREFIX . $photoId . $hashSlug . $ext;
            case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                return $this->getPhotoUploadDir() . self::PHOTO_FULLSCREEN_PREFIX . $photoId . $hashSlug . $ext;
            default:
                return $this->getPhotoUploadDir() . self::PHOTO_PREFIX . $photoId . $hashSlug . $ext;
        }
    }

    public function getPhotoPluginFilesPath( $photoId, $type = '' )
    {
        $dir = $this->getPhotoPluginFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('photoId' => $photoId, 'type' => $type, 'dir' => $dir)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        switch ( $type )
        {
            case PHOTO_BOL_PhotoService::TYPE_MAIN:
                return $dir . self::PHOTO_PREFIX . $photoId . $ext;
            case PHOTO_BOL_PhotoService::TYPE_PREVIEW:
                return $dir . self::PHOTO_PREVIEW_PREFIX . $photoId . $ext;
            case PHOTO_BOL_PhotoService::TYPE_ORIGINAL:
                return $dir . self::PHOTO_ORIGINAL_PREFIX . $photoId . $ext;
            case PHOTO_BOL_PhotoService::TYPE_SMALL:
                return $dir . self::PHOTO_SMALL_PREFIX . $photoId . $ext;
            case PHOTO_BOL_PhotoService::TYPE_FULLSCREEN:
                return $dir . self::PHOTO_FULLSCREEN_PREFIX . $photoId . $ext;
            default:
                return $dir . self::PHOTO_PREFIX . $photoId . $ext;
        }
    }

    public function getPhotoPluginFilesDir()
    {
        return OW::getPluginManager()->getPlugin('photo')->getPluginFilesDir();
    }

    /**
     * Find photo owner
     *
     * @param int $id
     * @return int
     */
    public function findOwner( $id )
    {
        if ( !$id )
            return null;

        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `a`.`userId`       
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            WHERE `p`.`id` = :id
            LIMIT 1
        ";

        $qParams = array('id' => $id);

        $owner = $this->dbo->queryForColumn($query, $qParams);

        return $owner;
    }

    /**
     * Get photo list (featured|latest|toprated)
     *
     * @param string $listType
     * @param int $first
     * @param int $limit
     * @param bool $checkPrivacy
     * @param null $exclude
     * @param $includeIds
     * @param $justReturnAlbumId
     * @return array
     */
    public function getPhotoList( $listType, $first, $limit, $exclude = NULL, $checkPrivacy = NULL, $includeIds = array(), $justReturnAlbumId = false)
    {
        $selectQuery = ' `p`.*, `a`.`userId` ';
        if ($justReturnAlbumId) {
            $selectQuery = ' DISTINCT `p`.albumId, `a`.`userId`, `p`.`id` ';
        }
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType,
            array(
                'photo' => 'p',
                'featured' => 'f',
                'album' => 'a'
            ),
            array(
                'listType' => $listType,
                'first' => $first,
                'limit' => $limit,
                'exclude' => $exclude,
                'checkPrivacy' => $checkPrivacy
            ));

        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $privacyConditionWhere = '';
        $idsFilteringCondition = '';
        if(is_array($includeIds) && sizeof($includeIds)>0){
            $idsFilteringCondition = ' AND `p`.id in ('.OW::getDbo()->mergeInClause($includeIds).') ';
        }
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('photo')){
            $privacyConditionWhere = ' and 1 ';
        }else {
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => $listType, 'objectType' => 'photo')));
            if (isset($privacyConditionEvent->getData()['where'])) {
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        switch ( $listType )
        {
            case 'featured':
                $photoFeaturedDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = 'SELECT ' . $selectQuery  . '
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                        INNER JOIN `' . $photoFeaturedDao->getTableName() . '` AS `f` ON (`f`.`photoId`=`p`.`id`)
                        ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $excludeCond . ' AND `f`.`id` IS NOT NULL
                        AND `a`.`entityType` = :user  AND
                        ' . $condition['where'] . $idsFilteringCondition . $privacyConditionWhere . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
            case 'latest':
            default:
                $query = 'SELECT ' . $selectQuery  . '
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
                        ' . $condition['join'] . '
                    WHERE `a`.`entityType` = :user AND `p`.`status` = :status'  . $excludeCond . 
                        ' AND ' . $condition['where'] . $idsFilteringCondition . $privacyConditionWhere . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
        }
        
        $params = array('user' => 'user', 'first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved');
        
//        if ( $checkPrivacy !== NULL )
//        {
//            switch ( $checkPrivacy )
//            {
//                case TRUE:
//                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
//                case FALSE:
//                    $params['everybody'] = self::PRIVACY_EVERYBODY;
//            }
//        }

        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }

        return $this->dbo->queryForList($query, array_merge($params, $condition['params']));
    }

    /**
     * Find latest public photos authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicPhotosAuthorsIds($first, $count)
    {
        $sql = 'SELECT
                `a`.`userId`
            FROM
                `' . $this->getTableName() . '` AS `p`
            INNER JOIN
                `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
            ON
                `p`.`albumId` = `a`.`id`
            WHERE
                `p`.`status` = :status
                    AND
                `p`.`privacy` = :privacy
            GROUP BY
                `a`.`userId`
            ORDER BY 
                max(`p`.`addDatetime`) DESC
            LIMIT
                :f, :c';

        return $this->dbo->queryForColumnList($sql, array(
            'status' => self::STATUS_APPROVED,
            'privacy' => self::PRIVACY_EVERYBODY,
            'f' => (int) $first,
            'c' => (int) $count
        ));
    }

    public function findAlbumPhotoList( $albumId, $listType, $offset, $limit, $privacy = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition(sprintf('findAlbumPhotoList.%s', $listType),
            array(
                'photo' => 'p',
                'featured' => 'f',
                'album' => 'a'
            ),
            array(
                'albumId' => $albumId,
                'listType' => $listType,
                'offset' => $offset,
                'limit' => $limit,
                'privacy' => $privacy
            ));

        if($privacy === null ){
            $privacySql = "1";
        }else{
            $privacySql = "`p`.`privacy`=:privacyItem";
            $condition['params']['privacyItem'] = $privacy;
        }

        switch ( $listType )
        {
            case 'featured':
                $query = 'SELECT `p`.*
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f` ON (`f`.`photoId`=`p`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . '
                    AND `f`.`id` IS NOT NULL AND ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
            case 'toprated':
                $query = 'SELECT `p`.*, `r`.`' . BOL_RateDao::ENTITY_ID . '`, COUNT(`r`.id) as `ratesCount`, AVG(`r`.`score`) as `avgScore`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                        INNER JOIN ' . BOL_RateDao::getInstance()->getTableName() . ' AS `r` ON (`r`.`entityId`=`p`.`id`
                            AND `r`.`' . BOL_RateDao::ENTITY_TYPE . '` = "photo_rates" AND `r`.`' . BOL_RateDao::ACTIVE . '` = 1)
                        ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . ' AND ' . $condition['where'] . '
                    GROUP BY `p`.`id`
                    ORDER BY `avgScore` DESC, `ratesCount` DESC
                    LIMIT :first, :limit';
                break;
            case 'latest':
            default:
                $query = 'SELECT `p`.*
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId AND ' . $privacySql . ' AND ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :first, :limit';
                break;
        }

        return $this->dbo->queryForList($query, array_merge(
            array(
                'albumId' => $albumId,
                'first' => $offset,
                'limit' => $limit,
                'status' => self::STATUS_APPROVED
            ),
            $condition['params']
        ));
    }
    
    public function getAlbumPhotoList( $albumId, $offset, $limit, $checkPrivacy = NULL, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'objectType' => 'photo', 'listType' => 'latest')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
            WHERE `p`.`albumId` = :albumId AND `p`.`status` = :status' . $privacyConditionWhere .
                (count($exclude) !== 0 ? ' AND `p`.`id` NOT IN (' . implode(',', array_map('intval', array_unique($exclude))) . ')' : '') . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array('albumId' => $albumId, 'first' => (int)$offset, 'limit' => (int)$limit, 'status' => 'approved');
        
//        if ( $checkPrivacy !== NULL )
//        {
//            switch ( $checkPrivacy )
//            {
//                case TRUE:
//                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
//                case FALSE:
//                    $params['everybody'] = self::PRIVACY_EVERYBODY;
//            }
//        }
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, $params);
    }

    public function findPhotoInfoListByIdList( $idList, $listType = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType,
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'idList' => $idList,
                'listType' => $listType
            )
        );

        $query = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`id` IN (' . $this->dbo->mergeInClause($idList) . ') AND `p`.`status` = :status AND ' . $condition['where'] . '
            ORDER BY `id` DESC';

        return $this->dbo->queryForList($query, array_merge(
            array('status' => self::STATUS_APPROVED),
            $condition['params']
        ));
    }

    /**
     * Count photos
     *
     * @param string $listType
     * @param boolean $checkPrivacy
     * @param null $exclude
     * @return int
     */
    public function countPhotos( $listType, $checkPrivacy = true, $exclude = null )
    {
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countPhotos',
            array(
                'photo' => 'p',
                'album' => 'a',
                'featured' => 'f'
            ),
            array(
                'listType' => $listType,
                'checkPrivacy' => $checkPrivacy,
                'exclude' => $exclude
            )
        );

//        $privacyCond = $checkPrivacy ? " AND `p`.`privacy` = 'everybody' " : "";
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        switch ( $listType )
        {
            case 'featured':
                $featuredDao = PHOTO_BOL_PhotoFeaturedDao::getInstance();

                $query = 'SELECT COUNT(`p`.`id`)
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . $albumDao->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    INNER JOIN `' . $featuredDao->getTableName() . '` AS `f` ON ( `p`.`id` = `f`.`photoId` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $privacyConditionWhere . $excludeCond . ' AND `f`.`id` IS NOT NULL
                    AND `a`.`entityType` = :entityType AND ' . $condition['where'];
                break;

            case 'latest':
            default:
                $query = 'SELECT COUNT(`p`.`id`)
                    FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . $albumDao->getTableName() . '` AS `a` ON ( `p`.`albumId` = `a`.`id` )
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status ' . $privacyConditionWhere . $excludeCond . '
                    AND `a`.`entityType` = :entityType AND ' . $condition['where'];
                break;
        }
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumn($query, array_merge(
            array(
                'status' => self::STATUS_APPROVED,
                'entityType' => self::ENTITY_TYPE_USER
            ),
            $condition['params']
        ));
    }

    public function countFullsizePhotos()
    {
        $example = new OW_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->countByExample($example);
    }

    public function deleteFullsizePhotos()
    {
        $photos = $this->getFullsizePhotos();

        $storage = OW::getStorage();

        foreach ( $photos as $photo )
        {
            $photo->hasFullsize = 0;
            $this->save($photo);

            $path = $this->getPhotoPath($photo->id, $photo->hash, 'original');

            if ( $storage->fileExists($path) )
            {
                $storage->removeFile($path);
            }
        }

        return true;
    }

    public function getFullsizePhotos()
    {
        $example = new OW_Example();
        $example->andFieldEqual('hasFullsize', 1);

        return $this->findListByExample($example);
    }

    /**
     * Counts album photos
     *
     * @param int $albumId
     * @param $exclude
     * @return int
     */
    public function countAlbumPhotos( $albumId, $exclude )
    {
        if ( !$albumId ) return false;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('countAlbumPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'exclude' => $exclude
            )
        );
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT COUNT(*)
            FROM `%s` AS `p`
                INNER JOIN `%s` AS `a` ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `p`.`albumId` = :albumId AND `p`.`status` = :status AND
                %s AND
                %s'.$privacyConditionWhere;
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName(),
            $condition['join'],
            $condition['where'],
            !empty($exclude) ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1'
        );
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return (int) $this->dbo->queryForColumn($sql, array_merge(
            array(
                'albumId' => $albumId,
                'status' => self::STATUS_APPROVED
            ),
            $condition['params']
        ));
    }
    
    public function countAlbumPhotosForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }
        
        $sql = "SELECT `albumId`, COUNT(*) AS `photoCount` FROM `".$this->getTableName()."` 
            WHERE `status` = 'approved' 
            AND `albumId` IN (".$this->dbo->mergeInClause($albumIdList).")
            GROUP BY `albumId`";
        
        return $this->dbo->queryForList($sql);
    }

    /**
     * Counts photos uploaded by a user
     *
     * @param int $userId
     * @return int
     */
    public function countUserPhotos( $userId )
    {
        if ( !$userId )
            return false;

        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE `a`.`userId` = :user AND `a`.`entityType` = 'user'
        ";

        return $this->dbo->queryForColumn($query, array('user' => $userId));
    }

    /**
     * Returns photos in the album
     *
     * @param int $albumId
     * @param int $page
     * @param int $limit
     * @param $exclude
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumPhotos( $albumId, $page, $limit, $exclude, $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED )
    {
        if ( !$albumId )
        {
            return false;
        }

        $first = ( $page - 1 ) * $limit;

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getAlbumPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'page' => $page,
                'limit' => $limit,
                'exclude' => $exclude,
                'status' => $status
            )
        );

        $sql = 'SELECT `p`.*
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                ' . $condition['join'] . '
            WHERE `p`.`albumId` = :albumId AND
                ' . (!empty($status) ? '`p`.`status` = :status' : '1') . ' AND
                ' . $condition['where'] . ' AND
                ' . (!empty($exclude) ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1') . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array(
            'albumId' => (int)$albumId,
            'first' => (int)$first,
            'limit' => (int)$limit
        );

        if ( !empty($status) )
        {
            $params['status'] = $status;
        }

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            $params,
            $condition['params']
        ));
    }

    /**
     * Returns all photos in the album
     *
     * @param int $albumId
     * @return array of PHOTO_Bol_Photo
     */
    public function getAlbumAllPhotos( $albumId, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('getAlbumAllPhotos',
            array(
                'album' => 'a',
                'photo' => 'p'
            ),
            array(
                'albumId' => $albumId,
                'exclude' => $exclude,
            )
        );

        $sql = 'SELECT `p`.*
            FROM `%s` AS `p`
                INNER JOIN `%s` AS `a` ON(`p`.`albumId` = `a`.`id`)
                %s
            WHERE `p`.`albumId` = :albumId AND %s AND %s
            ORDER BY `p`.`id` DESC';
        $sql = sprintf($sql,
            $this->getTableName(),
            PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName(),
            $condition['join'],
            count($exclude) !== 0 ? '`p`.`id` NOT IN(' . $this->dbo->mergeInClause($exclude) . ')' : '1',
            $condition['where']);

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array_merge(
            array('albumId' => $albumId),
            $condition['params']
        ));
    }
    
    public function getLastPhotoForList( $albumIdList )
    {
        if ( !$albumIdList )
        {
            return array();
        }

        $sql = 'SELECT MIN(`b`.`id`)
            FROM `' . $this->getTableName() . '` AS `b`
            WHERE `b`.`status` = :status AND `b`.`privacy` = :privacy
                    AND `b`.`albumId` IN (' . implode(',', array_unique(array_map('intval', $albumIdList))) . ')
            GROUP BY `b`.`albumId` ';

        $photoIdList = $this->dbo->queryForColumnList($sql, array('status' => 'approved', 'privacy' => 'everybody'));

        if ( !$photoIdList )
        {
            return array();
        }

        $sql = 'SELECT `a`.*
            FROM `' . $this->getTableName() . '` AS `a`
            WHERE `a`.`id` IN (' . implode(',', array_unique($photoIdList)) . ')';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName());
    }

    public function getLastPhoto( $albumId, array $exclude = array() )
    {
        if ( !$albumId )
        {
            return false;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        
        if ( !empty($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }
        
        $example->setOrder('`addDatetime`');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getPreviousPhoto( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThan('id', $id);
        $example->setOrder('`id` ASC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getNextPhoto( $albumId, $id )
    {
        if ( !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldLessThan('id', $id);
        $example->andFieldEqual('status', 'approved');
        $example->setOrder('`id` DESC');
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
    
    public function getPrevPhotoIdList( $listType, $photoId, $checkPrivacy = NULL )
    {
        if ( empty($photoId) )
        {
            return array();
        }
        
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`id` > :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` > :id AND `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` > :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getNextPhotoIdList( $listType, $photoId, $checkPrivacy = NULL )
    {
        if ( empty($photoId) )
        {
            return array();
        }
        
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`id` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`id` < :id AND `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getFirstPhotoIdList( $listType, $checkPrivacy, $photoId )
    {
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where']) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
                $privaceQuery = $privacyConditionEvent->getData()['where'];
                $privacy['params'] = $privacyConditionEvent->getData()['params'];
            }
        }
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id`
                    LIMIT :limit';
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId`
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getLastPhotoIdList( $listType, $checkPrivacy, $photoId )
    {
        $privacy = $this->getPrivacyCondition($checkPrivacy);
        $privaceQuery = $privacy['query'];
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition($listType, array('photo' => 'p', 'album' => 'a', 'featured' => 'f'));
        $params = array_merge($condition['params'], array('status' => 'approved', 'limit' => PHOTO_BOL_PhotoService::ID_LIST_LIMIT), $privacy['params']);
        
        switch ( $listType )
        {
            case 'latest':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`a`.`id` = `p`.`albumId`)
                    ' . $condition['join'] . '
                    WHERE `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                break;
            case 'userPhotos':
                $ownerId = PHOTO_BOL_PhotoService::getInstance()->findPhotoOwner($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`userId` = :userId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['userId'] = $ownerId;
                break;
            case 'entityPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId);
                
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND `a`.`entityId` = :entityId AND `a`.`entityType` = :entityType ' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                $params['entityType'] = $album->entityType;
                $params['entityId'] = $album->entityId;
                break;
            case 'albumPhotos':
                $photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId);
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
                    WHERE `p`.`status` = :status AND  `p`.`albumId` = :albumId' . $privaceQuery . '
                    ORDER BY `p`.`id` DESC
                    LIMIT :limit';
                $params['albumId'] = $photo->albumId;
                break;
            case 'featured':
                $sql = 'SELECT `p`.`id`
                    FROM `' . $this->getTableName() . '` AS `p`
                        INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                            ON(`p`.`albumId` = `a`.`id`)
                        INNER JOIN `' . PHOTO_BOL_PhotoFeaturedDao::getInstance()->getTableName() . '` AS `f`
                            ON(`p`.`id` = `f`.`photoId`)
                    ' . $condition['join'] . '
                    WHERE `f`.`photoId` < :id AND `p`.`status` = :status' . $privaceQuery . ' AND
                    ' . $condition['where'] . '
                    ORDER BY `f`.`photoId` DESC
                    LIMIT :limit';
                $params['id'] = $photoId;
                break;
        }
        
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function getPrivacyCondition( $checkPrivacy = NULL )
    {
        $params = array();
        
        if ( $checkPrivacy !== NULL )
        {
            switch ( $checkPrivacy )
            {
                case TRUE:
                    $query = ' AND (`p`.`' . self::PRIVACY . '` = :everybody OR `p`.`' . self::PRIVACY . '` = :friends)';
                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
                    break;
                case FALSE:
                    $query = ' AND `p`.`' . self::PRIVACY . '` = :everybody';
                    $params['everybody'] = self::PRIVACY_EVERYBODY;
                    break;
            }
        }
        else
        {
            $query = '';
        }
        
        return array('query' => $query, 'params' => $params);
    }

    /**
     * Returns currently viewed photo index
     *
     * @param int $albumId
     * @param int $id
     * @return int
     */
    public function getPhotoIndex( $albumId, $id )
    {
        if ( !$albumId || !$id )
            return false;

        $example = new OW_Example();
        $example->andFieldEqual('albumId', $albumId);
        $example->andFieldEqual('status', 'approved');
        $example->andFieldGreaterThenOrEqual('id', $id);

        return $this->countByExample($example);
    }

    /**
     * Removes photo file
     *
     * @param int $id
     * @param $hash
     * @param string $type
     */
    public function removePhotoFile( $id, $hash, $type )
    {
        $path = $this->getPhotoPath($id, $hash, $type);

        $storage = OW::getStorage();

        if ( $storage->fileExists($path) )
        {
            $photoSizeFile = filesize($path);
            if (OW::getConfig()->configExists('photo', 'totalSize') && $type=='main') {
                $totalSize = OW::getConfig()->getValue('photo', 'totalSize');
                $totalSize = $totalSize - $photoSizeFile;
                OW::getConfig()->saveConfig('photo', 'totalSize', $totalSize);
            }
            $storage->removeFile($path);
        }
    }
    
    public function updatePrivacyByAlbumIdList( $albumIdList, $privacy )
    {
        $albums = implode(',', $albumIdList);

        $sql = "UPDATE `".$this->getTableName()."` SET `privacy` = :privacy 
            WHERE `albumId` IN (".$albums.")";
        
        $this->dbo->query($sql, array('privacy' => $privacy));
    }
    
    // Entity photos methods
    
    public function findEntityPhotoList( $entityType, $entityId, $first, $count, $status = "approved", $privacy = null )
    {
        $limit = (int) $count;
        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findEntityPhotoList',
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'entityType' => $entityType,
                'entityId' => $entityId,
                'first' => $first,
                'count' => $count,
                'status' => $status,
                'privacy' => $privacy
            )
        );

        $qParams = array(
            'first' => $first,
            'limit' => $limit,
            "entityType" => $entityType,
            "entityId" => $entityId
        );

        if($status === null){
            $statusSql = "1";
        }else{
            $statusSql = "`p`.`status` = :status";
            $qParams['status'] = $status;
        }
        if($privacy === null){
            $privacySql = "1";
        }else{
            $privacySql = "`p`.`privacy`=:privacy";
            $qParams['privacy'] = $privacy;
        }

        $albumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();
        $query = "
            SELECT `p`.*
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $albumDao->getTableName() . "` AS `a` ON ( `p`.`albumId` = `a`.`id` )
            " . $condition['join'] . "
            WHERE $statusSql AND $privacySql
            AND `a`.`entityType` = :entityType
            AND `a`.`entityId` = :entityId
            AND " . $condition['where'] . "
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit";

        $cacheLifeTime = $first == 0 ? 24 * 3600 : null;
        $cacheTags = $first == 0 ? array(self::CACHE_TAG_PHOTO_LIST) : null;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array_merge($qParams, $condition['params']), $cacheLifeTime, $cacheTags);
    }
    
    public function countEntityPhotos( $entityType, $entityId, $status = "approved", $privacy = null )
    {
        $photoAlbumDao = PHOTO_BOL_PhotoAlbumDao::getInstance();

        $qParams = array(
            "entityType" => $entityType,
            "entityId" => $entityId
        );

        if($status === null){
            $statusSql = "1";
        }else{
            $statusSql = "`p`.`status` = :status";
            $qParams['status'] = $status;
        }
        if($privacy === null){
            $privacySql = "1";
        }else{
            $privacySql = "`p`.`privacy`=:privacy";
            $qParams['privacy'] = $privacy;
        }

        $query = "
            SELECT COUNT(`p`.`id`)
            FROM `" . $this->getTableName() . "` AS `p`
            LEFT JOIN `" . $photoAlbumDao->getTableName() . "` AS `a` ON ( `a`.`id` = `p`.`albumId` )
            WHERE $statusSql AND $privacySql AND `a`.`entityType` = :entityType AND `a`.`entityId`=:entityId
        ";

        return $this->dbo->queryForColumn($query, $qParams);
    }

    public function findPhotoListByUploadKey( $uploadKey, $exclude = null, $status = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('uploadKey', $uploadKey);

        if ( $status !== null )
        {
            $example->andFieldEqual('status', $status);
        }

        if ( $exclude && is_array($exclude) )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        $example->setOrder('`id` DESC');

        return $this->findListByExample($example);
    }
    
    public function findPhotoIdListByUploadKey( $uploadKey, array $exclude = array() )
    {
        if ( empty($uploadKey) )
        {
            return array();
        }
        
        $sql = 'SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `uploadKey` = :key AND `status` = :status';
        
        if ( !empty($exclude) )
        {
            $sql .= ' AND `id` NOT IN(' . implode(',', array_unique(array_map('intval', $exclude))) . ')';
        }
        
        return $this->dbo->queryForColumnList($sql, array('key' => $uploadKey, 'status' => 'approved'));
    }

    public function movePhotosToAlbum( $photoIdList, $albumId, $newAlbum = FALSE )
    {
        if ( empty($photoIdList) || empty($albumId) )
        {
            return FALSE;
        }
        
        $photoIdList = implode(',', array_map('intval', array_unique($photoIdList)));
        $key = PHOTO_BOL_PhotoService::getInstance()->getPhotoUploadKey($albumId);
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `albumId` = :albumId
            WHERE `id` IN (' . $photoIdList . ')';
        
        if ( ($result = $this->dbo->query($sql, array('albumId' => $albumId))) )
        {
            /*
             * Commented By Mohammad Agha Abbasloo
             * uploadkey for all photos in new album must be updated
             */
            /*if ( $newAlbum )
            {
                return $result;
            }*/
            
            $sql = 'UPDATE `' . $this->getTableName() . '`
                SET `uploadKey` = :key
                WHERE `id` IN(' . $photoIdList . ')';
            $this->dbo->query($sql, array('key' => $key));
            
            return TRUE;
        }
        
        return FALSE;
    }
    
    public function getSearchResultListByTag( $tag, $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        if ( empty($tag) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'photo', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`album`', 'privacyTableName' => 'photo', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `tag`.`label`, COUNT(`entity`.`entityId`) AS `count`, MAX(`tag`.`id`) AS `id`, GROUP_CONCAT(`entity`.`entityId`) AS `ids`
            FROM `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`tagId` = `tag`.`id`)
                INNER JOIN `' . $this->getTableName() . '` AS `photo` ON(`photo`.`id` = `entity`.`entityId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album`
                    ON(`photo`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `tag`.`label` LIKE :label AND `entity`.`entityType` = :type AND `photo`.`status` = :status AND
            ' . $condition['where'] . $privacyConditionWhere . '
            GROUP BY 1
            ORDER BY `tag`.`label`
            LIMIT :limit';
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('label' => '%' . ltrim($tag, '#') . '%', 'type' => self::PHOTO_ENTITY_TYPE, 'limit' => (int)$limit, 'status' => 'approved')));
    }
    
    public function getSearchResultAllListByTag( $tag )
    {
        if ( empty($tag) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'photo', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));
        
        $sql = 'SELECT DISTINCT `tag`.`id`
            FROM `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`tagId` = `tag`.`id`)
                INNER JOIN `' . $this->getTableName() . '` AS `photo` ON(`photo`.`id` = `entity`.`entityId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album` ON(`photo`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `entity`.`entityType` = :entityType AND
            `tag`.`label` LIKE :label AND
            ' . $condition['where'] . '
            ORDER BY `tag`.`label`';
        
        return $this->dbo->queryForColumnList($sql, array_merge($condition['params'], array('entityType' => self::PHOTO_ENTITY_TYPE, 'label' => '%' . ltrim($tag, '#') . '%')));
    }

    public function getPhotoIdListByTagIdList( $tagIdList )
    {
        if ( empty($tagIdList) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'album', 'tag' => 'tag', 'entityTag' => 'entity'));
        $privacyConditionWhere = '';
        $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`album`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
        if(isset($privacyConditionEvent->getData()['where'])){
//            $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
        }
        $sql = 'SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `entity` ON(`entity`.`entityId` = `p`.`id`)
                INNER JOIN `' . BOL_TagDao::getInstance()->getTableName() . '` AS `tag` ON(`tag`.`id` = `entity`.`tagId`)
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() .'` AS `album`
                    ON(`p`.`albumId` = `album`.`id`)
                ' . $condition['join'] . '
            WHERE `entity`.`entityType` = :entityType AND
            `tag`.`id` IN(' . implode(',', array_map('intval', $tagIdList)) . ') AND
            ' . $condition['where'] . $privacyConditionWhere . '
            ORDER BY 1 DESC';
        if(isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
//            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumnList($sql, array_merge($condition['params'], array('entityType' => self::PHOTO_ENTITY_TYPE)));
    }
    
    public function getSearchResultListByUserIdList( $idList, $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByUser', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `a`.`userId` AS `id`, COUNT(`p`.`albumId`) AS `count`, GROUP_CONCAT(DISTINCT `p`.`id`) AS `ids`
            FROM `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                INNER JOIN `' . $this->getTableName() . '` AS `p` ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE `a`.`userId` IN (' . implode(',', array_map('intval', $idList)) . ') AND `p`.`status` = :status AND
            ' . $condition['where'] . $privacyConditionWhere . '
            GROUP BY 1
            LIMIT :limit';
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('limit' => (int)$limit, 'status' => 'approved')));
    }
        
    public function getSearchResultListByDescription( $description, $limit = PHOTO_BOL_SearchService::SEARCH_LIMIT )
    {
        if ( empty($description) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `p`.`description` AS `label`, COUNT(`p`.`id`) AS `count`, GROUP_CONCAT(DISTINCT `p`.`id`) AS `ids`, `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                    ON(`a`.`id` = `p`.`albumId`)
            ' . $condition['join'] . '
            WHERE `p`.`description` LIKE :desc AND `p`.`status` = :status AND
            ' . $condition['where'] . $privacyConditionWhere . '
            GROUP BY 1
            LIMIT :limit';
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('desc' => '%' . $description . '%', 'limit' => $limit, 'status' => 'approved')));
    }
    
    public function getSearchResultAllListByDescription( $description )
    {
        if ( empty($description) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`description` LIKE :desc AND `p`.`status` = :status AND
            ' . $condition['where'] . $privacyConditionWhere . '
            ORDER BY `p`.`description`';
        $params = array_merge($condition['params'], array('desc' => '%' . $description . '%', 'status' => 'approved'));
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumnList($sql, $params);
    }
    
    public function findTaggedPhotosByTagId( $tagId, $first, $limit, $checkPrivacy = NULL, $justReturnAlbumId = false )
    {
        if ( empty($tagId) )
        {
            return array();
        }

        $selectQuery = ' `p`.*, `a`.`userId` ';
        if ($justReturnAlbumId) {
            $selectQuery = ' DISTINCT `p`.albumId, `a`.`userId`, `p`.`id` ';
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT '. $selectQuery .'
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                INNER JOIN `' . BOL_EntityTagDao::getInstance()->getTableName() . '` AS `e` ON(`e`.`entityId` = `p`.`id`)
            ' . $condition['join'] . '
            WHERE `e`.`tagId` = :tagId AND `p`.`status` = :status AND ' . $condition['where'] . $privacyConditionWhere . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
       
//        if ( $checkPrivacy !== NULL )
//        {
//            switch ( $checkPrivacy )
//            {
//                case TRUE:
//                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
//                case FALSE:
//                    $params['everybody'] = self::PRIVACY_EVERYBODY;
//            }
//        }
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($condition['params'], array('tagId' => $tagId, 'first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved')));
    }
    
    public function findPhotoListByUserId( $userId, $first, $limit, $checkPrivacy = NULL, array $exclude = array(), $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED, $justReturnAlbumId = false )
    {
        if ( empty($userId) )
        {
            return array();
        }

        $selectQuery = ' `p`.*, `a`.`userId` ';
        if ($justReturnAlbumId) {
            $selectQuery = ' DISTINCT `p`.albumId, `a`.`userId`, `p`.`id` ';
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('findPhotoListByUserId',
            array(
                'photo' => 'p',
                'album' => 'a'
            ),
            array(
                'userId' => $userId,
                'first' => $first,
                'limit' => $limit,
                'checkPrivacy' => $checkPrivacy,
                'exclude' => $exclude,
                'status' => $status
            )
        );
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT '. $selectQuery .'
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
                ' . $condition['join'] . '
            WHERE `a`.`userId` = :userId AND
                ' . (!empty($status) ? '`p`.`status` = :status' : '1') . ' AND
                ' . (!empty($exclude) ? '`p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '1') . ' AND
                ' . $condition['where'] . $privacyConditionWhere .  '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';

        $params = array(
            'userId' => $userId,
            'first' => (int) $first,
            'limit' => (int) $limit
        );

//        if ( $checkPrivacy !== NULL )
//        {
//            switch ( $checkPrivacy )
//            {
//                case TRUE:
//                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
//                case FALSE:
//                    $params['everybody'] = self::PRIVACY_EVERYBODY;
//            }
//        }

        if ( !empty($status) )
        {
            $params['status'] = $status;
        }
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, array_merge($params, $condition['params']));
    }

    public function findPhotoListByUserIdListCount(array $userIdList, $checkPrivacy = NULL,array $exclude = array())
    {
        if ( count($userIdList) === 0 )
        {
            return array();
        }
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
                INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `u` ON (`u`.`id` = `a`.`userId`)
            WHERE `a`.`userId` IN(' . implode(',', array_map('intval', array_unique($userIdList))) . ') AND `p`.`status` = :status' .$excludeCond. $privacyConditionWhere . '
            ORDER BY `u`.`username`';

        $params = array( 'status' => 'approved');

        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return (int)$this->dbo->queryForColumn($sql, $params);
    }
    public function findPhotoListByUserIdList( array $userIdList, $first, $limit, $checkPrivacy = NULL,array $exclude = array() )
    {
        if ( count($userIdList) === 0 )
        {
            return array();
        }
        $excludeCond = $exclude ? ' AND `p`.`id` NOT IN (' . $this->dbo->mergeInClause($exclude) . ')' : '';
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON (`p`.`albumId` = `a`.`id`)
                INNER JOIN `' . BOL_UserDao::getInstance()->getTableName() . '` AS `u` ON (`u`.`id` = `a`.`userId`)
            WHERE `a`.`userId` IN(' . implode(',', array_map('intval', array_unique($userIdList))) . ') AND `p`.`status` = :status' .$excludeCond. $privacyConditionWhere . '
            ORDER BY `u`.`username`
            LIMIT :first, :limit';
        
        $params = array('first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved');
        
//        if ( $checkPrivacy !== NULL )
//        {
//            switch ( $checkPrivacy )
//            {
//                case TRUE:
//                    $params['friends'] = self::PRIVACY_FRIENDS_ONLY;
//                case FALSE:
//                    $params['everybody'] = self::PRIVACY_EVERYBODY;
//            }
//        }
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, $params);
    }
    
    public function findPhotoListByDescription( $desc, $id, $first, $limit, $justReturnAlbumId = false)
    {
        if ( empty($desc) )
        {
            return array();
        }

        $selectQuery = ' `p`.*, `a`.`userId` ';
        if ($justReturnAlbumId) {
            $selectQuery = ' DISTINCT `p`.albumId, `a`.`userId`, `p`.`id` ';
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByDesc', array('photo' => 'p', 'album' => 'a'));

        $sqlIDs = 'SELECT `a1`.`ids`
            FROM (SELECT `p`.`description`, COUNT(*), GROUP_CONCAT(`p`.`id`) AS `ids`, `p`.`id`
                FROM `' . $this->getTableName() . '` AS `p`
                    INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                        ON(`a`.`id` = `p`.`albumId`)
                ' . $condition['join'] . '
                WHERE `p`.`description` LIKE :desc AND
                ' . $condition['where'] . '
                GROUP BY 1
                HAVING `p`.`id` = :id) AS `a1`';
        
        $ids = $this->dbo->queryForColumn($sqlIDs, array_merge($condition['params'], array('desc' => '%' . $desc . '%', 'id' => $id)));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT ' . $selectQuery .'
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`a`.`id` = `p`.`albumId`)
            WHERE `p`.`id` IN (' . $ids . ') AND `p`.`status` = :status ' . $privacyConditionWhere . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
        $params = array('first' => (int)$first, 'limit' => (int)$limit, 'status' => 'approved');
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, $params);
    }
    
    public function findPhotoListByIdList( array $idList, $first, $limit, $status = PHOTO_BOL_PhotoDao::STATUS_APPROVED, $justReturnAlbumId = false )
    {
        if ( count($idList) === 0 )
        {
            return array();
        }

        $selectQuery = ' `p`.*, `a`.`userId` ';
        if ($justReturnAlbumId) {
            $selectQuery = ' DISTINCT `p`.albumId, `a`.`userId`, `p`.`id` ';
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByHashtag', array('photo' => 'p', 'album' => 'a'));
        
        $params = array_merge($condition['params'], array('first' => $first, 'limit' => $limit));
        $statusSql = "1";
        
        if ( !empty($status) )
        {
            $params["status"] = $status;
            $statusSql = "`p`.`status` = :status";
        }
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = 'SELECT '. $selectQuery .'
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `p`.`id` IN (' . implode(',', array_map('intval', array_unique($idList))) . ') AND ' . $statusSql . ' AND ' . $condition['where'] . $privacyConditionWhere . '
            ORDER BY `p`.`id` DESC
            LIMIT :first, :limit';
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $params = array_merge($params, $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForList($sql, $params);
    }
    
    public function findPhotoList( $type, $page, $limit, $checkPrivacy = true, $exclude = null)
    {
        if ( $type == 'toprated' )
        {
            $first = ( $page - 1 ) * $limit;
            $topRatedList = BOL_RateService::getInstance()->findMostRatedEntityList('photo_rates', $first, $limit, $exclude);

            if ( !$topRatedList )
            {
                return array();
            }
            
            $photoArr = $this->findPhotoInfoListByIdList(array_keys($topRatedList));

            $photos = array();

            foreach ( $photoArr as $key => $photo )
            {
                $photos[$key] = $photo;
                $photos[$key]['score'] = $topRatedList[$photo['id']]['avgScore'];
                $photos[$key]['rates'] = $topRatedList[$photo['id']]['ratesCount'];
            }

            usort($photos, array('PHOTO_BOL_PhotoService', 'sortArrayItemByDesc'));
        }
        else
        {
            $photos = $this->getPhotoList($type, $page, $limit, $checkPrivacy,$exclude);
        }
        
        if ( $photos )
        {
            foreach ( $photos as $key => $photo )
            {
                $photos[$key]['url'] = $this->getPhotoUrl($photo['id'], $photo['hash'], FALSE);
            }
        }

        return $photos;
    }

    public function findPhotosInAlbum( $albumId, array $photoIds = null )
    {
        if ( empty($albumId) )
        {
            return array();
        }

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a`
                    ON(`p`.`albumId` = `a`.`id`)
            WHERE `p`.`albumId` = :albumId';

        if ( count($photoIds) !== 0 )
        {
            $sql .= ' AND `p`.`id` IN(' . $this->dbo->mergeInClause($photoIds) . ')';
        }

        return $this->dbo->queryForList($sql, array('albumId' => $albumId));
    }

    public function countPhotosInAlbumByPhotoIdList( $albumId, array $photoIdList )
    {
        if ( empty($albumId) )
        {
            return 0;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId AND 
                `id` IN (' . implode(',', array_map('intval', array_unique($photoIdList))) . ') AND
                `status` = :status';
        
        return (int)$this->dbo->queryForColumn($sql, array('albumId' => $albumId, 'status' => 'approved'));
    }
    
    public function countPhotosByUploadKey( $uploadKey )
    {
        if ( empty($uploadKey) )
        {
            return 0;
        }
        
        $sql = 'SELECT COUNT(*)
            FROM `' . $this->getTableName() . '`
            WHERE `uploadKey` = :key AND `status` = :status';
        
        return (int)$this->dbo->queryForColumn($sql, array('key' => $uploadKey, 'status' => 'approved'));
    }
    
    public function updateUploadKeyByPhotoIdList( array $photoIdList, $key )
    {
        if ( count($photoIdList) === 0 )
        {
            return 0;
        }
        
        $sql = 'UPDATE `' . $this->getTableName() . '`
            SET `uploadKey` = :key
            WHERE `id` IN (' . implode(',', array_map('intval', array_unique($photoIdList))) . ')';
        
        return $this->dbo->query($sql, array('key' => $key));
    }
    
    public function findDistinctPhotoUploadKeyByAlbumId( $albumId )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        
        $sql = 'SELECT *
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId';
        
        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array('albumId' => $albumId));
    }
    
    public function findPhotoIdListByAlbumId( $albumId, array $exclude = array() )
    {
        if ( empty($albumId) )
        {
            return array();
        }
        
        $sql = ' SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `albumId` = :albumId';
        
        if ( count($exclude) !== 0 )
        {
            $sql .= ' AND `id` NOT IN (' . implode(',', array_map('intval', array_unique($exclude))) . ')';
        }
        
        return $this->dbo->queryForColumnList($sql, array('albumId' => $albumId));
    }
    
    public function findPhotoIdListByUserIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $condition = PHOTO_BOL_PhotoService::getInstance()->getQueryCondition('searchByUsername', array('photo' => 'p', 'album' => 'a'));
        $privacyConditionWhere = '';
        if(!OW::getUser()->isAuthenticated() || !OW::getUser()->isAuthorized('photo')){
            $privacyConditionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_CONTENT_LIST_QUERY_EXECUTE, array('objectTableName' => '`a`', 'privacyTableName' => 'p', 'listType' => 'latest', 'objectType' => 'photo')));
            if(isset($privacyConditionEvent->getData()['where'])){
                $privacyConditionWhere = $privacyConditionEvent->getData()['where'];
            }
        }
        $sql = ' SELECT `p`.`id`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN ' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . ' AS `a` ON(`p`.`albumId` = `a`.`id`)
            ' . $condition['join'] . '
            WHERE `a`.`userId` IN(' . $this->dbo->mergeInClause($idList) . ') AND ' . $condition['where'] . $privacyConditionWhere;
        if(isset($privacyConditionEvent) && isset($privacyConditionEvent->getData()['params']) && is_array($privacyConditionEvent->getData()['params']) && sizeof($privacyConditionEvent->getData()['params'])>0){
            $condition['params'] = array_merge($condition['params'], $privacyConditionEvent->getData()['params']);
        }
        return $this->dbo->queryForColumnList($sql, $condition['params']);
    }

    // Content provider
    public function getPhotoListByIdList( array $idList )
    {
        if ( empty($idList) )
        {
            return array();
        }

        $sql = 'SELECT `p`.*, `a`.`userId`
            FROM `' . $this->getTableName() . '` AS `p`
                INNER JOIN `' . PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName() . '` AS `a` ON(`p`.`albumId` = `a`.`id`)
            WHERE `p`.`id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForList($sql);
    }


    public function findListAllPrivacy( $userId, $extra = null, $showAll = false ,$first = -1, $count = -1)
    {
        if ($first < 0)
        {
            $first = 0;
        }

        if ($count < 0)
        {
            $count = PHP_INT_MAX;
        }

        if (!isset($extra))
            $extra = array('join' => '', 'where' => '', 'select' => array('','','',''), 'aggregate' => '', 'params' => array());

        $data = array(
            '%wholeSelect' => '`photos`',
            '%subSelect' => '`c`',
        );

        $query = "SELECT ".str_replace(array_keys($data), array_values($data),$extra['select'][0])."
            FROM( 
              SELECT ".str_replace(array_keys($data), array_values($data),$extra['select'][1])."
                FROM `" . $this->getTableName() . "` AS `c` LEFT JOIN ".PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName()." AS `a`
                    ON (`c`.`albumId` = `a`.`id`)
                    ".str_replace(array_keys($data), array_values($data),$extra['join'])."
                WHERE `c`.`privacy` = 'everybody' AND (".str_replace(array_keys($data), array_values($data),$extra['where']).")
                
              UNION
                
              SELECT ".str_replace(array_keys($data), array_values($data),$extra['select'][2])."
                FROM `" . $this->getTableName() . "` AS `c` LEFT JOIN ".PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName()." AS `a`
                    ON (`c`.`albumId` = `a`.`id`)
                    ".str_replace(array_keys($data), array_values($data),$extra['join'])."
                WHERE `c`.`privacy` = 'only_for_me' AND (`a`.`userId` = :userId OR :showAll) AND (".str_replace(array_keys($data), array_values($data),$extra['where']).")
                
              UNION
                
              SELECT ".str_replace(array_keys($data), array_values($data),$extra['select'][3])."
                 FROM `" . $this->getTableName() . "` AS `c` LEFT JOIN ".PHOTO_BOL_PhotoAlbumDao::getInstance()->getTableName()." AS `a`
                    ON (`c`.`albumId` = `a`.`id`)
                    ".str_replace(array_keys($data), array_values($data),$extra['join'])."
                    LEFT JOIN " . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . " AS `f1` ON `f1`.`friendId` = `a`.`userId`
                    LEFT JOIN " . FRIENDS_BOL_FriendshipDao::getInstance()->getTableName() . " AS `f2` ON `f2`.`userId` = `a`.`userId`
                WHERE `c`.`privacy` = 'friends_only' AND (`f1`.`userId` = :userId OR `f2`.`friendId` = :userId OR `a`.`userId` = :userId OR :showAll) AND (".str_replace(array_keys($data), array_values($data),$extra['where']).")
            ) AS `clips`
              ".str_replace(array_keys($data), array_values($data),$extra['aggregate'])."
              LIMIT :firstRow, :countRow
            ";

        $count = (int) $count;
        $params = array(
            'userId'=>$userId,
            'firstRow'=>$first,
            'countRow'=> $count,
            'showAll'=>$showAll
        );

        $params = array_merge($params, $extra['params']);

        return $this->dbo->queryForList($query, $params);
    }

}
