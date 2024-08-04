<?php
/**
 * AJAX Upload photo component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.7.6
 */
class PHOTO_CMP_AjaxUpload extends OW_Component
{
    public function __construct( $albumId = null, $albumName = null, $albumDescription = null, $url = null, $data = null )
    {
        parent::__construct();

        if ( !OW::getUser()->isAuthorized('photo', 'upload') && !OW::getUser()->isAuthorized('photo') && !OW::getUser()->isAdmin())
        {
            $this->setVisible(false);
            
            return;
        }
        
        $userId = OW::getUser()->getId();
        $document = OW::getDocument();
        
        PHOTO_BOL_PhotoTemporaryService::getInstance()->deleteUserTemporaryPhotos($userId);

        $plugin = OW::getPluginManager()->getPlugin('photo');

        $document->addStyleSheet($plugin->getStaticCssUrl() . 'photo_upload.css');
        $document->addScript($plugin->getStaticJsUrl() . 'codemirror.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'upload.js');
        
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.ajaxPhotoUploadParams = Object.freeze({$params});',
                array(
                    'params' => array(
                        'actionUrl' => OW::getRouter()->urlForRoute('photo.ajax_upload'),
                        'maxFileSize' => PHOTO_BOL_PhotoService::getInstance()->getMaxUploadFileSize(),
                        'deleteAction' => OW::getRouter()->urlForRoute('photo.ajax_upload_delete')
                    )
                )
            )
        );
        $document->addOnloadScript(';window.ajaxPhotoUploader.init();');

        $form = new PHOTO_CLASS_AjaxUploadForm('user', $userId, $albumId, $albumName, $albumDescription, $url, $data);
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER,array('this' => $this,'form' => $form , 'albumId' => $albumId, 'data' => $data)));
        $this->addForm($form);
        $this->assign('extendInputs', $form->getExtendedElements());
        $this->assign('albumId', $albumId);
        $this->assign('userId', $userId);

        $newsfeedAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getNewsfeedAlbum($userId);
        $exclude = !empty($newsfeedAlbum) ? array($newsfeedAlbum->id) : array();
        $this->addComponent('albumNames', OW::getClassInstance('PHOTO_CMP_AlbumNameList', $userId, $exclude));
        
        $language = OW::getLanguage();
        $language->addKeyForJs('photo', 'not_all_photos_uploaded');
        $language->addKeyForJs('photo', 'size_limit');
        $language->addKeyForJs('photo', 'type_error');
        $language->addKeyForJs('photo', 'dnd_support');
        $language->addKeyForJs('photo', 'dnd_not_support');
        $language->addKeyForJs('photo', 'drop_here');
        $language->addKeyForJs('photo', 'please_wait');
        $language->addKeyForJs('photo', 'create_album');
        $language->addKeyForJs('photo', 'album_name');
        $language->addKeyForJs('photo', 'album_desc');
        $language->addKeyForJs('photo', 'describe_photo');
        $language->addKeyForJs('photo', 'photo_upload_error');
        $language->addKeyForJs('base', 'upload_bad_request_error');
    }
}
