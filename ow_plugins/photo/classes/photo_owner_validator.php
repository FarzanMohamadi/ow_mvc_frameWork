<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.classes
 * @since 1.6.1
 */
class PHOTO_CLASS_PhotoOwnerValidator extends OW_Validator
{
    public function __construct()
    {
        $this->errorMessage = OW::getLanguage()->text('photo', 'no_photo_found');
    }

    public function isValid( $photoId )
    {
        return !empty($photoId) &&
            ($photo = PHOTO_BOL_PhotoService::getInstance()->findPhotoById($photoId)) !== NULL &&
            ($album = PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumById($photo->albumId)) !== NULL &&
            ($album->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized('photo'));
    }
}
