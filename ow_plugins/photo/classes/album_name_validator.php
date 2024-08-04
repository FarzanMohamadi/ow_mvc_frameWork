<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.classes
 * @since 1.6.1
 */
class PHOTO_CLASS_AlbumNameValidator extends OW_Validator
{
    private $checkDuplicate;
    private $userId;
    private $albumName;

    public function __construct( $checkDuplicate = TRUE, $userId = NULL, $albumName = NULL )
    {
        $this->errorMessage = OW::getLanguage()->text('photo', 'newsfeed_album_error_msg');
        $this->checkDuplicate = $checkDuplicate;
        $this->albumName = $albumName;
        
        if ( $userId !== NULL )
        {
            $this->userId = (int)$userId;
        }
        else
        {
            $this->userId = OW::getUser()->getId();
        }
    }

    public function isValid( $albumName )
    {
        if ( strcasecmp(trim($this->albumName), OW::getLanguage()->text('photo', 'newsfeed_album')) === 0 )
        {
            return TRUE;
        }
        
        if ( strcasecmp(trim($albumName), OW::getLanguage()->text('photo', 'newsfeed_album')) === 0 )
        {
            return FALSE;
        }
        elseif ( $this->checkDuplicate && strcasecmp($albumName, $this->albumName) !== 0 && PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumByName($albumName, $this->userId) !== NULL )
        {
            $this->setErrorMessage(OW::getLanguage()->text('photo', 'album_name_error'));
            
            return FALSE;
        }
        
        return TRUE;
    }

    public function getJsValidator()
    {
        return UTIL_JsGenerator::composeJsString('{
            validate : function( value )
            {
                if ( {$albumName} && {$albumName}.trim().toLowerCase() == {$newsfeedAlbum}.toString().trim().toLowerCase() )
                {
                    return true;
                }
                    
                if ( value.toString().trim().toLowerCase() == {$newsfeedAlbum}.toString().trim().toLowerCase() )
                {
                    throw {$errorMsg};
                }
            }
        }', array(
            'albumName' => $this->albumName,
            'newsfeedAlbum' => OW::getLanguage()->text('photo', 'newsfeed_album'),
            'errorMsg' => $this->errorMessage
        ));
    }
}
