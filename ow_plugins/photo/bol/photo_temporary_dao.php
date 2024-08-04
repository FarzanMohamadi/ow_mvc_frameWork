<?php

/**
 * Data Access Object for `photo_temporary` table.
 *
 * @package ow.plugin.photo.bol
 * @since 1.0
 */
class PHOTO_BOL_PhotoTemporaryDao extends OW_BaseDao
{
    CONST TMP_PHOTO_ORIGINAL_PREFIX = 'tmp_photo_original_'; // 3
    CONST TMP_PHOTO_FULLSCREEN = 'tmp_photo_fullscreen_';    // 5
    CONST TMP_PHOTO_PREFIX = 'tmp_photo_';                   // 2 main
    CONST TMP_PHOTO_PREVIEW_PREFIX = 'tmp_photo_preview_';   // 1
    CONST TMP_PHOTO_SMALL = 'tmp_photo_small_';              // 4
    
    /**
     * Singleton instance.
     *
     * @var PHOTO_BOL_PhotoTemporaryDao
     */
    private static $classInstance;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return PHOTO_BOL_PhotoTemporaryDao
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
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'PHOTO_BOL_PhotoTemporary';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'photo_temporary';
    }

    /**
     * Get temporary photo URL
     *
     * @param int $id
     * @param int $size
     * 
     * @return string
     */
    public function getTemporaryPhotoUrl( $id, $size = 1 )
    {
        $userfilesUrl = OW::getPluginManager()->getPlugin('photo')->getUserFilesUrl();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('photoId' => $id, 'size' => $size)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        switch ( $size )
        {
            case 1:
                return $userfilesUrl . self::TMP_PHOTO_PREVIEW_PREFIX . $id . $ext;
            case 2:
                return $userfilesUrl . self::TMP_PHOTO_PREFIX . $id . $ext;
            case 3:
                return $userfilesUrl . self::TMP_PHOTO_ORIGINAL_PREFIX . $id . $ext;
            case 4:
                return $userfilesUrl . self::TMP_PHOTO_SMALL . $id . $ext;
            case 5:
                return $userfilesUrl . self::TMP_PHOTO_FULLSCREEN . $id . $ext;
        }

        return '';
    }
    
    /**
     * Get path to temporary photo in file system
     *
     * @param int $id
     * @param int $size
     * 
     * @return string
     */
    public function getTemporaryPhotoPath( $id, $size = 1 )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('photo')->getUserFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('photoId' => $id, 'size' => $size)));
        if(isset($checkAnotherExtensionEvent->getData()['ext'])){
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        switch ( $size )
        {
            case 1:
                return $userfilesDir . self::TMP_PHOTO_PREVIEW_PREFIX . $id . $ext;
            case 2:
                return $userfilesDir . self::TMP_PHOTO_PREFIX . $id . $ext;
            case 3:
                return $userfilesDir . self::TMP_PHOTO_ORIGINAL_PREFIX . $id . $ext;
            case 4:
                return $userfilesDir . self::TMP_PHOTO_SMALL . $id. $ext;
            case 5:
                return $userfilesDir . self::TMP_PHOTO_FULLSCREEN . $id . $ext;
        }

        return '';
    }

    /**
     * Find photos by user Id
     *
     * @param int $userId
     *
     * @param string $orderBy
     * @return array
     */
    public function findByUserId( $userId, $orderBy = 'timestamp' )
    {
        if ( !$userId )
        {
            return null;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        if ( $orderBy == 'timestamp' )
        {
            $example->setOrder('`addDatetime` ASC');
        }
        else 
        {
            $example->setOrder('`order` ASC');
        }

        return $this->findListByExample($example);
    }
    
    public function findLimitedPhotos( $limit = PHOTO_BOL_PhotoTemporaryService::TEMPORARY_PHOTO_LIVE_LIMIT )
    {
        $sql = 'SELECT `id`
            FROM `' . $this->getTableName() . '`
            WHERE `addDatetime` <= :limit';
        
        return $this->dbo->queryForColumnList($sql, array('limit' => $limit));
    }
}