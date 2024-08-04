<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.classes
 * @since 1.6.1
 */
class PHOTO_CLASS_AlbumOwnerValidator extends OW_Validator
{
    public function isValid( $albumId )
    {
        return !empty($albumId) && (PHOTO_BOL_PhotoAlbumService::getInstance()->isAlbumOwner($albumId, OW::getUser()->getId()) || OW::getUser()->isAuthorized('photo'));
    }
}
