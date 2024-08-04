<?php
/**
 * AJAX Upload photo component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.8.1
 */
class PHOTO_CMP_AlbumNameList extends OW_Component
{
    /**
     * @param int $userId
     */
    public function __construct( $userId, $exclude )
    {
        parent::__construct();

        if ( empty($userId) )
        {
            $this->setVisible(false);

            return;
        }

        $this->assign('albumNameList', PHOTO_BOL_PhotoAlbumService::getInstance()->findAlbumNameListByUserId($userId, $exclude));
    }
}