<?php
/**
 * FRM Update Server
 */

/**
 * Data Access Object for `UpdateInformation` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
class FRMUPDATESERVER_BOL_UpdateInformationDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMUPDATESERVER_BOL_UpdateInformationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_UpdateInformationDao
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
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'FRMUPDATESERVER_BOL_UpdateInformation';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmupdateserver_update_information';
    }

    /***
     * @param $key
     * @param $buildNumber
     * @return bool
     */
    public function hasExist($key, $buildNumber)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('key', $key);
        $ex->andFieldEqual('buildNumber', $buildNumber);
        $updateInformation = $this->findObjectByExample($ex);

        if($updateInformation == null){
            return false;
        }

        return true;
    }

    /***
     * @param null $key
     * @return array
     */
    public function getAllVersion($key = null){
        $ex = new OW_Example();
        if($key!=null) {
            $ex->andFieldEqual('key', $key);
        }
        $ex->setOrder('`buildNumber` DESC');
        return $this->findListByExample($ex);
    }

    /*
     * @param $key
     * @param $buildNumber
     * @return item
     */
    public function getItemByKeyAndBuildNumber($key,$buildNumber)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('key', $key);
        $ex->andFieldEqual('buildNumber', $buildNumber);
        $item = $this->findObjectByExample($ex);
        return $item;
    }

    private static function deleteVersionsFolders($dir){
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                OW::getStorage()->removeFile($file->getRealPath());
            }
        }
        rmdir($dir);
    }
    public function deleteAllVersions(){
        $dir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . 'core';
        if(OW::getStorage()->isDir($dir))
            $this->deleteVersionsFolders($dir);
        $dir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . 'plugins';
        if(OW::getStorage()->isDir($dir))
            $this->deleteVersionsFolders($dir);
        $dir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . 'themes';
        if(OW::getStorage()->isDir($dir))
            $this->deleteVersionsFolders($dir);
        $sql = 'TRUNCATE TABLE ' . $this->getTableName();
        $this->dbo->delete($sql);
        $this->clearCache();
    }

    public function deleteVersion($buildNumber,$key){
        if(!isset($buildNumber) || !isset($key) ){
            return false;
        }
        else{
            $coreMainDir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . $key. DIRECTORY_SEPARATOR .'main'.DIRECTORY_SEPARATOR . $buildNumber ;
            $coreUpdateDir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . $key. DIRECTORY_SEPARATOR .'updates'.DIRECTORY_SEPARATOR . $buildNumber ;
            $themeDir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . 'themes'.DIRECTORY_SEPARATOR .$key.DIRECTORY_SEPARATOR . $buildNumber ;
            $pluginDir = 'ow_pluginfiles' . DIRECTORY_SEPARATOR . 'frmupdateserver' .DIRECTORY_SEPARATOR . 'plugins'.DIRECTORY_SEPARATOR .$key.DIRECTORY_SEPARATOR . $buildNumber ;
            if(OW::getStorage()->isDir($coreMainDir))
            {
                $this->deleteVersionsFolders($coreMainDir);
                if(OW::getStorage()->isDir($coreUpdateDir)) {
                    $this->deleteVersionsFolders($coreUpdateDir);
                }
                return true;
            }
            elseif(OW::getStorage()->isDir($themeDir))
            {
                $this->deleteVersionsFolders($themeDir);
                return true;
            }
            elseif(OW::getStorage()->isDir($pluginDir))
            {
                $this->deleteVersionsFolders($pluginDir);
                return true;
            }
            return false;
        }
    }
    public function deleteItem($item,$buildNumber,$key){
        if(isset($item)) {
            $this->deleteById($item->id);
        }
        $result = $this->deleteVersion($buildNumber,$key);
        return $result;
    }
}