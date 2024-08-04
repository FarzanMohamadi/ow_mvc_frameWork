<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MediaPanelFileDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_MediapFileDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MediaPanelFileDao
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
     * @see BOL_MediaPanelFileDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_MediaPanelFile';
    }

    /**
     * @see BOL_MediaPanelFileDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_media_panel_file';
    }

    public function findImages( $plugin, $userId=null, $first, $count )
    {
        $ex = new OW_Example();
            $ex->andFieldEqual('plugin', $plugin);

        if ( $userId !== null && intval($userId) > 0 )
        {
            $ex->andFieldEqual('userId', $userId);
        }

        $ex->setLimitClause($first, $count)->setOrder('stamp DESC');

        return $this->findListByExample($ex);
    }

    public function findImage( $imageId )
    {
        return $this->findById($imageId);
    }

    public function countGalleryImages( $plugin, $userId=null )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('plugin', $plugin)
            ->andFieldEqual('type', 'image');

        if ( $userId !== null && intval($userId) > 0 )
        {
            $ex->andFieldEqual('userId', $userId);
        }

        return $this->countByExample($ex);
    }

    public function deleteImages( $plugin, $count )
    {
        $images = $this->findImages($plugin, null, 0, $count);

        foreach ( $images as $image )
        {
            $data = $image->getData();
            $filename = isset($data->filename)?$data->filename:$data->name;
            $this->deleteById($image->id);

            $storage = OW::getStorage();

            $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $filename);
        }
    }

    public function deleteImagesByUserId( $userId )
    {
        $ex = new OW_Example();

        $ex->andFieldEqual('userId', (int)$userId);

        $images = $this->findListByExample($ex);

        foreach ( $images as $image )
        {
            $data = $image->getData();
            $filename = isset($data->filename)?$data->filename:$data->name;
            $storage = OW::getStorage();

            $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $filename);

            $this->deleteById($image->id);
        }
    }

    public function deleteImageById( $id )
    {
        $image = $this->findById((int)$id);

        $data = $image->getData();
        $filename = isset($data->filename)?$data->filename:$data->name;
        $storage = OW::getStorage();

        $storage->removeFile(OW::getPluginManager()->getPlugin('base')->getUserFilesDir() . $image->id . '-' . $filename);

        $this->deleteById($image->id);
    }
}