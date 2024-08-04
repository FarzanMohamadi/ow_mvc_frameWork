<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.6.1
 */
class PHOTO_CMP_MakeAlbumCover extends OW_Component
{
    public function __construct( $albumId, $photoId = NULL, $userId = NULL )
    {
        parent::__construct();
        
        if ( empty($userId) )
        {
            $userId = OW::getUser()->getId();
        }
        
        if ( empty($userId) || ($album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($albumId)) === null ||
            ($album->userId != $userId || !OW::getUser()->isAuthorized('photo', 'view')) )
        {
            $this->setVisible(FALSE);
            
            return;
        }
        
        if ( $photoId === NULL && !PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->isAlbumCoverExist($albumId) )
        {
            $this->setVisible(FALSE);
            
            return;
        }
        
        $storage = OW::getStorage();

        if ( empty($photoId) )
        {
            if ( $storage instanceof BASE_CLASS_FileStorage )
            {
                $photoPath = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverPathByAlbumId($albumId);
            }
            else
            {
                $photoPath = PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($albumId, true);
            }

            $info = getimagesize($photoPath);

            if ( $info['0'] < 330 || $info['1'] < 330 )
            {
                $this->assign('imgError', OW::getLanguage()->text('photo', 'to_small_cover_img'));

                return;
            }

            $this->assign('coverUrl', PHOTO_BOL_PhotoAlbumCoverDao::getInstance()->getAlbumCoverUrlByAlbumId($albumId, TRUE));
        }
        else
        {
            $photo = PHOTO_BOL_PhotoDao::getInstance()->findById($photoId);
            $this->assign('coverUrl', PHOTO_BOL_PhotoDao::getInstance()->getPhotoUrl($photo->id, $photo->hash, FALSE));

            if ( !empty($photo->dimension) )
            {
                $info = json_decode($photo->dimension, true);

                if ( $info[PHOTO_BOL_PhotoService::TYPE_ORIGINAL]['0'] < 330 || $info[PHOTO_BOL_PhotoService::TYPE_ORIGINAL]['1'] < 330 )
                {
                    $this->assign('imgError', OW::getLanguage()->text('photo', 'to_small_cover_img'));

                    return;
                }
            }
            else
            {
                if ( $storage instanceof BASE_CLASS_FileStorage )
                {
                    $photoPath = PHOTO_BOL_PhotoDao::getInstance()->getPhotoPath($photo->id, $photo->hash, PHOTO_BOL_PhotoService::TYPE_ORIGINAL);
                }
                else
                {
                    $photoPath = PHOTO_BOL_PhotoDao::getInstance()->getPhotoUrl($photo->id, $photo->hash, FALSE);
                }
        
                $info = getimagesize($photoPath);
                
                if ( $info['0'] < 330 || $info['1'] < 330 )
                {
                    $this->assign('imgError', OW::getLanguage()->text('photo', 'to_small_cover_img'));
            
                    return;
                }
            }
        }
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'jquery.Jcrop.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.Jcrop.js');
        
        $form = new PHOTO_CLASS_MakeAlbumCover();
        $form->getElement('albumId')->setValue($albumId);
        $form->getElement('photoId')->setValue($photoId);
        $this->addForm($form);
    }
}
