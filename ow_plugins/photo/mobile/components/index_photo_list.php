<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.mobile.components
 * @since 1.7.5
 */

class PHOTO_MCMP_IndexPhotoList extends OW_MobileComponent
{
    protected $visiblePhotoCount = 0;
    
    public function __construct( $params )
    {
        parent::__construct();

        $this->visiblePhotoCount = !empty($params['photoCount']) ? (int) $params['photoCount'] : 8;
        $checkAuth = isset($params['checkAuth']) ? (bool) $params['checkAuth'] : true;
        $wrap = isset($params['wrapBox']) ? (bool) $params['wrapBox'] : true;
        $boxType = isset($params['boxType']) ? $params['boxType'] : '';
        $showTitle = isset($params['showTitle']) ? (bool) $params['showTitle'] : true;
        $uniqId = isset($params['uniqId']) ? $params['uniqId'] : FRMSecurityProvider::generateUniqueId();

        if ( $checkAuth && !OW::getUser()->isAuthorized('photo', 'view') )
        {
            $this->setVisible(false);

            return;
        }

        $photoService = PHOTO_BOL_PhotoService::getInstance();

        $latest = $photoService->findPhotoList('latest', 1, $this->visiblePhotoCount, NULL, PHOTO_BOL_PhotoService::TYPE_PREVIEW);
        $this->assign('latest', $latest);

        $featured = $photoService->findPhotoList('featured', 1, $this->visiblePhotoCount, NULL, PHOTO_BOL_PhotoService::TYPE_PREVIEW);
        $this->assign('featured', $featured);

        $toprated = $photoService->findPhotoList('toprated', 1, $this->visiblePhotoCount, NULL, PHOTO_BOL_PhotoService::TYPE_PREVIEW);
        $this->assign('toprated', $toprated);

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_PHOTO, array('listtype' =>'photo_friends','page'=>1, 'photosPerPage'=>$this->visiblePhotoCount)));
        if(isset($resultsEvent->getData()['result'])){
            $photo_friends= $resultsEvent->getData()['result'];
            $this->assign('photo_friends', $photo_friends);
        }


        $items = array('latest', 'toprated');
        if ( $featured )
        {
            $items[] = 'featured';
        }
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_VALID_LIST_FOR_PHOTO, array('validLists' =>$items)));
        if(isset($resultsEvent->getData()['validLists'])){
            $items= $resultsEvent->getData()['validLists'];
        }
        $menuItems = $this->getMenuItems($items, $uniqId);
        $this->assign('items', $menuItems);

        $this->assign('wrapBox', $wrap);
        $this->assign('boxType', $boxType);
        $this->assign('showTitle', $showTitle);
        $this->assign('url', OW::getEventManager()->call('photo.getAddPhotoURL', array('')));
        $this->assign('uniqId', $uniqId);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('photo')->getMobileCmpViewDir() . 'index_photo_list.html');
    }

    public function getMenuItems( array $keys, $uniqId )
    {
        $lang = OW::getLanguage();
        $menuItems = array();
        
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        
        if ( in_array('latest', $keys) )
        {
            $count = $photoService->countPhotos('latest');
            
            $menuItems['latest'] = array(
                'label' => $lang->text('photo', 'menu_latest'),
                'id' => 'photo-cmp-menu-latest-'.$uniqId,
                'contId' => 'photo-cmp-latest-'.$uniqId,
                'active' => true,
                'visibility' => ($count > $this->visiblePhotoCount) ? true : false
            );
        }

        if ( in_array('featured', $keys) )
        {
            $count = $photoService->countPhotos('featured');
            
            $menuItems['featured'] = array(
                'label' => $lang->text('photo', 'menu_featured'),
                'id' => 'photo-cmp-menu-featured-'.$uniqId,
                'contId' => 'photo-cmp-featured-'.$uniqId,
                'active' => false,
                'visibility' => ($count > $this->visiblePhotoCount) ? true : false
            );
        }

        if ( in_array('toprated', $keys) )
        {
            $count = $photoService->countPhotos('toprated');
            
            $menuItems['toprated'] = array(
                'label' => $lang->text('photo', 'menu_toprated'),
                'id' => 'photo-cmp-menu-toprated-'.$uniqId,
                'contId' => 'photo-cmp-toprated-'.$uniqId,
                'active' => false,
                'visibility' => ($count > $this->visiblePhotoCount) ? true : false
            );
        }
        $validListsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_LIST_TYPE_TO_PHOTO,array('menuItems' => $menuItems,'isCmp' =>true , 'uniqId'=>$uniqId)));
        if(isset($validListsEvent->getData()['menuItems'])){
            $menuItems = $validListsEvent->getData()['menuItems'];
        }

        return $menuItems;
    }
    
    public static function getToolbar($uniqId)
    {
        $lang = OW::getLanguage();
        
        $items = array('latest', 'featured', 'toprated');
        $toolbars = array();
        foreach ( $items as $tbItem )
        {
            $toolbars[$tbItem] = array(
                'href' => OW::getRouter()->urlForRoute('view_photo_list', array('listType' => $tbItem)),
                'label' => $lang->text('base', 'view_all'),
                'id' => "toolbar-photo-{$tbItem}-".$uniqId
            );
                
            if ( in_array($tbItem, array('featured', 'toprated')) )
            {
                $toolbars[$tbItem]['display'] = 'none';
            }
        }
        
        return $toolbars;
    }
}