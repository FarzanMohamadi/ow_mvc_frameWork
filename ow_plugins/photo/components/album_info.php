<?php
/**
 * AJAX Upload photo component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.7.6
 */
class PHOTO_CMP_AlbumInfo extends OW_Component
{
    public function __construct( $params )
    {
        parent::__construct();

        $album = $params['album'];
        $coverEvent = OW::getEventManager()->trigger(
            new OW_Event(PHOTO_CLASS_EventHandler::EVENT_GET_ALBUM_COVER_URL, array('albumId' => $album->id))
        );
        $coverData = $coverEvent->getData();

        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_ALBUM_INFO_RENDERER, array('this' => $this, 'album' => $album)));
        $this->assign('album', $album);
        $this->assign('coverUrl', $coverData['coverUrl']);
        $this->assign('coverUrlOrig', $coverData['coverUrlOrig']);
    }
}
