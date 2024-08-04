<?php
/**
 * Photo albums mobile component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.6
 */
class PHOTO_MCMP_AlbumList extends OW_MobileComponent
{
    /**
     * @var PHOTO_BOL_PhotoAlbumService
     */
    private $photoAlbumService;

    public function __construct( $userId, $limit, $exclude )
    {
        parent::__construct();

        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        $user = BOL_UserService::getInstance()->findUserById($userId);
        $this->assign('username', $user->getUsername());

        $albums = $this->photoAlbumService->findUserAlbumList($user->id, 1, $limit, $exclude, true);
        $this->assign('albums', $albums);

        foreach ( $albums as $album )
        {
            array_push($exclude, $album['dto']->id);
        }
        $loadMore = $this->photoAlbumService->countUserAlbums($userId, $exclude);
        if ( !$loadMore )
        {
            $script = "OWM.trigger('photo.hide_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }
    }
}