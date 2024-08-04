<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_MediaPanelService
{
    /*
     * @var BOL_MediaPanelFileDao
     */
    private $dao;

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        $this->dao = BOL_MediaPanelFileDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_MediaPanelService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_MediaPanelService
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function add( $plugin, $type, $userId, $data, $stamp=null )
    {
        $o = new BOL_MediaPanelFile();

        $this->dao->save(
                $o->setPlugin($plugin)
                ->setType($type)
                ->setUserId($userId)
                ->setData($data)
                ->setStamp(empty($stamp) ? time() : $stamp)
        );

        return $o->getId();
    }

    public function findGalleryImages( $plugin, $userId=null, $first, $count )
    {
        return $this->dao->findImages($plugin, $userId, $first, $count);
    }

    public function findImage( $imageId )
    {
        return $this->dao->findImage($imageId);
    }

    public function countGalleryImages( $plugin, $userId=null )
    {
        return $this->dao->countGalleryImages($plugin, $userId);
    }

    public function deleteImages( $plugin, $count )
    {
        $this->dao->deleteImages($plugin, $count);
    }
    
    public function deleteById($id)
    {
    	$this->dao->deleteImageById($id);
    }
    
    public function findAll()
    {
         return $this->dao->findAll();       
    }

    public function deleteImagesByUserId($userId)
    {
        return $this->dao->deleteImagesByUserId($userId);
    }
    public function initMenu($params)
    {
        $language = OW::getLanguage();
        $router = OW::getRouter();

        $menu = new BASE_CMP_ContentMenu();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'upload'));
        $item->setOrder(0);
        $item->setIconClass('tf_img_upload ow_dynamic_color_icon');
        $item->setKey('upload');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'index', $params));
        $menu->addElement($item);

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_from_url'));
        $item->setOrder(1);
        $item->setKey('url');
        $item->setIconClass('tf_img_from_url ow_dynamic_color_icon');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'fromUrl', $params));
        $menu->addElement($item);

        $count = BOL_MediaPanelService::getInstance()->countGalleryImages($params['pluginKey'], OW::getUser()->getId());

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('base', 'tf_img_gal') . ($count == 0 ? '' : " ({$count})" ));
        $item->setOrder(1);
        $item->setKey('gallery');
        $item->setIconClass('tf_img_gal ow_dynamic_color_icon');
        $item->setUrl($router->urlFor('BASE_CTRL_MediaPanel', 'gallery', $params));
        $menu->addElement($item);

        $event = new OW_Event('media.panel.init.menu',array('menu' => $menu,'id'=>$params['id'],'pluginKey'=>$params['pluginKey']));
        OW::getEventManager()->trigger($event);

        return $menu;
    }
}