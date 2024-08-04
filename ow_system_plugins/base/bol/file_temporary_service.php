<?php
/**
 * Temporary File Service Class.
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.7.5
 * 
 */
final class BOL_FileTemporaryService
{
    CONST TMP_FILE_PREFIX = 'tmp_photo_';
    CONST TEMPORARY_FILE_LIVE_LIMIT = 86400;
    
    /**
     * @var BOL_FileTemporaryDao
     */
    private $fileTemporaryDao;
    /**
     * Class instance
     *
     * @var BOL_FileTemporaryService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->fileTemporaryDao = BOL_FileTemporaryDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return BOL_FileTemporaryService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function addTemporaryFile( $source, $filename, $userId, $order = 0 )
    {
        if ( !OW::getStorage()->fileExists($source) || !$userId )
        {
            return false;
        }
        
        $tmpFile = new BOL_FileTemporary();
        $tmpFile->filename = $filename;
        $tmpFile->userId = $userId;
        $tmpFile->addDatetime = time();
        $tmpFile->order = $order;
        $this->fileTemporaryDao->save($tmpFile);

        OW::getStorage()->copyFile($source, $this->getTemporaryFilePath($tmpFile->id));

        return $tmpFile->id;
    }

    public function findUserTemporaryFiles( $userId, $orderBy = 'timestamp' )
    {
        $list = $this->fileTemporaryDao->findByUserId($userId, $orderBy);
        
        $result = array();
        if ( $list )
        {
            foreach ( $list as $file )
            {
                $result[$file->id]['dto'] = $file;
                $result[$file->id]['src'] = $this->getTemporaryFileUrl($file->id, 1);
            }
        }
        
        return $result;
    }
    
    public function deleteUserTemporaryFiles( $userId )
    {
        $list = $this->fileTemporaryDao->findByUserId($userId);
        
        if ( !$list )
        {
            return true;
        }

        foreach ( $list as $file )
        {
            OW::getStorage()->removeFile($this->getTemporaryFilePath($file->id), true);
            $this->fileTemporaryDao->delete($file);
        }

        return true;
    }
    
    public function deleteTemporaryFile( $fileId )
    {
        $file = $this->fileTemporaryDao->findById($fileId);
        if ( !$file )
        {
            return false;
        }

        OW::getStorage()->removeFile($this->getTemporaryFilePath($fileId), true);
        $this->fileTemporaryDao->delete($file);
        
        return true;
    }
    
    public function deleteLimitedFiles()
    {   
        foreach ( $this->fileTemporaryDao->findLimitedFiles(self::TEMPORARY_FILE_LIVE_LIMIT) as $id )
        {
            $this->deleteTemporaryFile($id);
        }
    }

    public function moveTemporaryFile( $tmpId, $desc )
    {
        $tmp = $this->fileTemporaryDao->findById($tmpId);

        if ( !$tmp )
        {
            return false;
        }

        $tmpFilePath = $this->getTemporaryFilePath($tmp->id);

        $fileService = BOL_FileService::getInstance();

        $file = new BOL_File();
        $file->description = htmlspecialchars(trim($desc));
        $file->addDatetime = time();
        $file->filename = $tmp->filename;
        $file->userId = $tmp->userId;
        BOL_FileDao::getInstance()->save($file);

        try
        {
            OW::getStorage()->copyFile($tmpFilePath, $fileService->getFilePath($file->id));
        }
        catch ( Exception $e )
        {
            $photo = null;
        }

        return $file;
    }

    /**
     * Get temporary file URL
     *
     * @param int $id
     *
     * @return string
     */
    public function getTemporaryFileUrl( $id )
    {
        $userfilesUrl = OW::getPluginManager()->getPlugin('base')->getUserFilesUrl();
        $file = $this->fileTemporaryDao->findById($id);
        return $userfilesUrl . self::TMP_FILE_PREFIX . $id . $file->filename;
    }

    /**
     * Get path to temporary file in file system
     *
     * @param int $id
     *
     * @return string
     */
    public function getTemporaryFilePath( $id )
    {
        $userfilesDir = OW::getPluginManager()->getPlugin('base')->getUserFilesDir();
        $file = $this->fileTemporaryDao->findById($id);
        return $userfilesDir . self::TMP_FILE_PREFIX . $id . $file->filename;
    }
}