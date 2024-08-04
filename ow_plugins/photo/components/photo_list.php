<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.6.1
 */
class PHOTO_CMP_PhotoList extends OW_Component
{
    public function __construct( array $params )
    {
        parent::__construct();
        
        $plugin = OW::getPluginManager()->getPlugin('photo');
        
        $hasSideBar = OW::getThemeManager()->getCurrentTheme()->getDto()->getSidebarPosition() != 'none';
        $photoParams = array(
            'classicMode' => (bool)OW::getConfig()->getValue('photo', 'photo_list_view_classic')
        );
        $contParams = array(
            'isClassic' => $photoParams['classicMode'],
            'isModerator' => OW::getUser()->isAuthorized('photo')
        );
        
        switch ( $params['type'] )
        {
            case 'albums':
                $photoParams = array(
                    'userId' => $params['userId'],
                    'action' => 'getAlbumList',
                    'level' => ($hasSideBar ? 3 : 4),
                    'classicMode' => (bool)OW::getConfig()->getValue('photo', 'album_list_view_classic'),
                    'isOwner' => $params['userId'] == OW::getUser()->getId() || OW::getUser()->isAuthorized('photo')
                );
                $contParams['isOwner'] = $photoParams['isOwner'];
                $contParams['isClassic'] = $photoParams['classicMode'];
                break;
            case 'albumPhotos':
                $photoParams['albumId'] = $params['albumId'];
                $photoParams['isOwner'] = PHOTO_BOL_PhotoAlbumService::getInstance()->isAlbumOwner($params['albumId'], OW::getUser()->getId());
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 4 : 5) : 4);
                
                $contParams['isOwner'] = $photoParams['isOwner'];
                $contParams['albumId'] = $params['albumId'];
                break;
            case 'userPhotos':
                $photoParams['userId'] = $params['userId'];
                $photoParams['isOwner'] = $params['userId'] == OW::getUser()->getId();
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 4 : 5) : 4);
                
                $contParams['isOwner'] = $photoParams['isOwner'];
                break;
            case 'tag':
                $photoParams['searchVal'] = $params['tag'];
            case 'photo_friends':
                if($params['view'] == 'photos'){
                    $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 4 : 5) : 4);
                }else{
                    $photoParams = array(
                        'userId' => $params['userId'],
                        'action' => 'getAlbumList',
                        'level' => ($hasSideBar ? 3 : 4),
                        'classicMode' => (bool)OW::getConfig()->getValue('photo', 'album_list_view_classic'),
                        'isOwner' => $params['userId'] == OW::getUser()->getId() || OW::getUser()->isAuthorized('photo')
                    );
                    $contParams['isOwner'] = $photoParams['isOwner'];
                    $contParams['isClassic'] = $photoParams['classicMode'];
                }
                break;
            default:
                $photoParams['level'] = ($photoParams['classicMode'] ? ($hasSideBar ? 4 : 5) : 4);
                break;
        }
        
        $photoDefault = array(
            'getPhotoURL' => OW::getRouter()->urlFor('PHOTO_CTRL_Photo', 'ajaxResponder'),
            'listType' => $params['type'],
            'rateUserId' => OW::getUser()->getId(),
            'urlHome' => OW_URL_HOME,
            'tagUrl' => OW::getRouter()->urlForRoute('view_tagged_photo_list', array('tag' => '-tag-')),
            'listView' => $params['view']
        );
        
        $contDefault = array(
            'downloadAccept' => (bool)OW::getConfig()->getValue('photo', 'download_accept'),
            'downloadUrl' => OW_URL_HOME . 'photo/download-photo/:id',
            'actionUrl' => $photoDefault['getPhotoURL'],
            'listType' => $params['type'],
            'listView' => $params['view']
        );
        
        $document = OW::getDocument();
        
        if($params['view'] == "albums"){
            $photoParams['action'] = "getAlbumList";
        }

        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.browsePhotoParams = Object.freeze({$params});', array(
                'params' => array_merge($photoDefault, $photoParams)
            ))
        );
        $document->addOnloadScript(';window.browsePhoto.init();');

        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.photoContextActionParams = Object.freeze({$params});', array(
                'params' => array_merge($contDefault, $contParams)
            ))
        );
        $document->addOnloadScript(';window.photoContextAction.init();');
        
        $this->assign('isClassicMode', $photoParams['classicMode']);
        $this->assign('hasSideBar', $hasSideBar);
        $this->assign('type', $params['view']);
        if(OW::getPluginManager()->isPluginActive('frmwidgetplus') && OW::getConfig()->getValue('frmwidgetplus', 'displayRateWidget')==2 && !OW::getUser()->isAuthenticated())
            $this->assign('displayRate', false);
        else
            $this->assign('displayRate', true);
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'browse_photo.css');
        $document->addScript($plugin->getStaticJsUrl() . 'utils.js');
        $document->addScript($plugin->getStaticJsUrl() . 'browse_photo.js');
        
        $language = OW::getLanguage();
        
        if ( $params['view'] == 'photos' )
        {
            $event = new OW_Event(PHOTO_CLASS_EventHandler::EVENT_INIT_FLOATBOX, $photoParams);
            OW::getEventManager()->trigger($event);

            $language->addKeyForJs('photo', 'tb_edit_photo');
            $language->addKeyForJs('photo', 'confirm_delete');
            $language->addKeyForJs('photo', 'mark_featured');
            $language->addKeyForJs('photo', 'remove_from_featured');
            $language->addKeyForJs('photo', 'no_photo');

            $language->addKeyForJs('photo', 'rating_total');
            $language->addKeyForJs('photo', 'rating_your');
            $language->addKeyForJs('base', 'rate_cmp_owner_cant_rate_error_message');

            $language->addKeyForJs('photo', 'download_photo');
            $language->addKeyForJs('photo', 'delete_photo');
            $language->addKeyForJs('photo', 'save_as_avatar');
            $language->addKeyForJs('photo', 'save_as_cover');
            
            $language->addKeyForJs('photo', 'search_invitation');
            $language->addKeyForJs('photo', 'set_as_album_cover');
            $language->addKeyForJs('photo', 'search_result_empty');
        }
        else
        {
            $language->addKeyForJs('photo', 'edit_album');
            $language->addKeyForJs('photo', 'delete_album');
            $language->addKeyForJs('photo', 'album_delete_not_allowed');
            $language->addKeyForJs('photo', 'newsfeed_album');
            $language->addKeyForJs('photo', 'are_you_sure');
            $language->addKeyForJs('photo', 'album_description');
        }
        
        $language->addKeyForJs('photo', 'no_items');
    }
}
