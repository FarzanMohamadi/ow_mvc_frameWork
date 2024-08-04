<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.7.6
 */
class PHOTO_CMP_PageHead extends OW_Component
{
    public function __construct( $ownerMode, $album )
    {
        parent::__construct();

        $language = OW::getLanguage();

        $isAuthorized = false;
        $isInAlbumPage = false;
        $handler = OW::getRequestHandler()->getHandlerAttributes();
        $action = $handler[OW_RequestHandler::ATTRS_KEY_ACTION];
        $listView = !empty($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listView']) ? $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listView'] : 'photos';

        if($action == 'userAlbums' || $action =='userPhotos')
        {
            $ownerId= BOL_UserService::getInstance()->findByUsername($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['user'])->id;
            $viewerId = OW::getUser()->getId();
            if($ownerId == $viewerId && (OW::getUser()->isAuthorized('photo','upload')))
                $isAuthorized = true;
        }

       else if (isset($album)){

            $isInAlbumPage = true;

            $ownerId = $album->userId;
            $userId = OW::getUser()->getId();

            if($ownerId == $userId && (OW::getUser()->isAuthorized('photo','upload'))){

                $isAuthorized= true;
            }
        }
        else{

            if (OW::getUser()->isAuthenticated() && (OW::getUser()->isAuthorized('photo','upload'))){

                $isAuthorized=true;
            }
        }

        $this->assign('isInAlbumPage',$isInAlbumPage);
        $this->assign('isAuthorized', $isAuthorized);

        if ( $isAuthorized )
        {
            $language->addKeyForJs('photo', 'album_name');
            $language->addKeyForJs('photo', 'album_desc');
            $language->addKeyForJs('photo', 'create_album');
            $language->addKeyForJs('photo', 'newsfeed_album');
            $language->addKeyForJs('photo', 'newsfeed_album_error_msg');
            $language->addKeyForJs('photo', 'upload_photos');
            $language->addKeyForJs('photo', 'close_alert');
        }
        else
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('photo', 'upload');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $this->assign('isPromo', true);
                $this->assign('promoMsg', json_encode($status['msg']));
            }
        }

        $this->assign('url', OW::getEventManager()->call(PHOTO_CLASS_EventHandler::EVENT_GET_ADDPHOTO_URL, array(
            'albumId' => (!empty($ownerMode) && !empty($album)) ? $album->id : 0
        )));

        if ($action != 'userAlbum'){
            $menu = new BASE_CMP_SortControl();
            $menu->setTemplate(OW::getPluginManager()->getPlugin('photo')->getCmpViewDir() . 'sort_control.html');
            $searchLabel = OW::getLanguage()->text("photo", "search_invitation");
            if (OW::getConfig()->getValue("base", "display_name_question") == "realname") {
                $searchLabel = OW::getLanguage()->text("photo", "search_invitation_real_name");
            }
            $menu->assign('searchLabel', $searchLabel);
        }

        if ( in_array($action, array('viewList', 'viewTaggedList')) ) {

            $menu->addItem(
                'latest',
                $language->text('photo', 'menu_latest'),
                OW::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => 'latest',
                    'listView' => $listView
                ))
            );

            if ($listView != 'albums'){
                if (PHOTO_BOL_PhotoService::getInstance()->countPhotos('featured')) {
                    $menu->addItem(
                        'featured',
                        $language->text('photo', 'menu_featured'),
                        OW::getRouter()->urlForRoute('view_photo_list', array(
                            'listType' => 'featured',
                            'listView' => $listView
                        ))
                    );
                }
                $menu->addItem(
                    'toprated',
                    $language->text('photo', 'menu_toprated'),
                    OW::getRouter()->urlForRoute('view_photo_list', array(
                        'listType' => 'toprated',
                        'listView' => $listView
                    ))
                );
                $menu->addItem(
                    'most_discussed',
                    $language->text('photo', 'menu_most_discussed'),
                    OW::getRouter()->urlForRoute('view_photo_list', array(
                        'listType' => 'most_discussed',
                        'listView' => $listView
                    ))
                );
            }

            if ($action != 'viewTaggedList') {
                $menu->setActive(!empty($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listType']) ? $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listType'] : 'latest');
            }


            $menu->assign('initSearchEngine', TRUE);

            $menu_view = new BASE_CMP_SortControl();
            $menu_view->setTemplate(OW::getPluginManager()->getPlugin('photo')->getCmpViewDir() . 'sort_control.html');

            $menu_view->addItem(
                'photos',
                $language->text('photo', 'menu_photos'),
                OW::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listType'],
                    'listView' => 'photos'
                ))
            );

            $menu_view->addItem(
                'albums',
                $language->text('photo', 'menu_albums'),
                OW::getRouter()->urlForRoute('view_photo_list', array(
                    'listType' => $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['listType'],
                    'listView' => 'albums'
                ))
            );

            if ($listView == 'albums')
            {
                $menu_view->setActive('albums');
            }
            else
            {
                $menu_view->setActive('photos');
            }
            $menu_view->assign("menuForView", TRUE);
            $this->addComponent('subMenuView', $menu_view);
        }
        else
        {
            if ($action != 'userPhotos' && $action != 'userAlbums'){
                $user = BOL_UserService::getInstance()->findByUsername($handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['user']);
                $this->assign('user', $user);

                $avatar = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($user->id));
                $this->assign('avatar', $avatar[$user->id]);

                $onlineStatus = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($user->id));
                $this->assign('onlineStatus', $onlineStatus[$user->id]);
            }

            if ($action != 'userAlbum'){
                $menu->addItem(
                    'userPhotos',
                    $language->text('photo', 'menu_photos'),
                    OW::getRouter()->urlForRoute('photo.user_photos', array(
                        'user' => $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['user']
                    ))
                );

                $menu->addItem(
                    'userAlbums',
                    $language->text('photo', 'menu_albums'),
                    OW::getRouter()->urlForRoute('photo_user_albums', array(
                        'user' => $handler[OW_RequestHandler::ATTRS_KEY_VARLIST]['user']
                    ))
                );

                if ( in_array($action, array('userAlbums', 'userAlbum')) )
                {
                    $menu->setActive('userAlbums');
                }
                else
                {
                    $menu->setActive('userPhotos');
                }
            }
        }
        if ($action != 'userAlbum') {
            $event = OW::getEventManager()->trigger(
                new BASE_CLASS_EventCollector(PHOTO_CLASS_EventHandler::EVENT_COLLECT_PHOTO_SUB_MENU)
            );

            foreach ($event->getData() as $menuItem) {
                $menu->addItem(
                    $menuItem['sortOrder'],
                    $menuItem['label'],
                    $menuItem['url'],
                    isset($menuItem['isActive']) ? (bool)$menuItem['isActive'] : FALSE
                );
            }
            $validListsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_LIST_TYPE_TO_PHOTO, array('menu' => $menu)));
            if (isset($validListsEvent->getData()['menu'])) {
                $menu = $validListsEvent->getData()['menu'];
            }

            $menu->assign('menuForType', TRUE);
            $this->addComponent('subMenu', $menu);
        }
        if ( OW::getUser()->isAuthenticated() )
        {
            $userObj = OW::getUser()->getUserObject();

            if ( in_array($action, array('viewList', 'viewTaggedList', 'userPhotos', 'userAlbums')) )
            {
                $menuItems = array();

                $item = new BASE_MenuItem();
                $item->setKey('menu_explore');
                $item->setLabel($language->text('photo', 'menu_explore'));

                $item->setUrl(OW::getRouter()->urlForRoute('view_photo_list'));
                $item->setIconClass('ow_ic_search ow_dynamic_color_icon');
                $item->setOrder(0);
                $item->setActive(in_array($action, array('viewList', 'viewTaggedList')));
                $menuItems[] = $item;

                $item = new BASE_MenuItem();
                $item->setKey('menu_my_photos');
                $item->setLabel($language->text('photo', 'menu_my_photos'));
                $url = OW::getConfig()->getValue('photo', 'list_view_type') == 'photos' ?
                    OW::getRouter()->urlForRoute('photo.user_photos', array('user' => $userObj->username))
                    : OW::getRouter()->urlForRoute('photo_user_albums', array('user' => $userObj->username));
                $item->setUrl($url);
                $item->setIconClass('ow_ic_my_photo ow_dynamic_color_icon');
                $item->setOrder(1);
                $item->setActive($ownerMode);
                $menuItems[] = $item;

                $validListsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_LIST_TYPE_TO_PHOTO,array('menuItems' => $menuItems)));
                if(isset($validListsEvent->getData()['menuItems'])){
                    $menuItems = $validListsEvent->getData()['menuItems'];
                }


                $this->addComponent('photoMenu', new BASE_CMP_ContentMenu($menuItems));

            }
        }
    }
}
