<?php
/**
 * Photo list mobile component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.6
 */
class PHOTO_MCMP_PhotoList extends OW_MobileComponent
{
    /**
     * @var PHOTO_BOL_PhotoService 
     */
    private $photoService;

    /**
     * @var PHOTO_BOL_PhotoAlbumService
     */
    private $photoAlbumService;

    public function __construct( $listType, $count, $exclude = null, $albumId = null )
    {
        parent::__construct();

        $this->photoService = PHOTO_BOL_PhotoService::getInstance();
        $this->photoAlbumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        if ( $albumId )
        {
            $photos = $this->photoService->getAlbumPhotos($albumId, 1, $count, $exclude);
        }
        else
        {
            $photos = $this->photoService->findPhotoList($listType, 1, $count, $exclude, PHOTO_BOL_PhotoService::TYPE_PREVIEW);
        }

        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_PHOTO, array('listtype' =>$listType,'page'=>1, 'photosPerPage'=>$count,'exclude'=>$exclude, PHOTO_BOL_PhotoService::TYPE_PREVIEW)));
        if(isset($resultsEvent->getData()['result'])){
            $photos= $resultsEvent->getData()['result'];
        }
        $this->assign('photos', $photos);
        $this->assign('noContent', sizeof($photos) == 0);

        foreach ( $photos as $photo )
        {
            array_push($exclude, $photo['id']);
        }

        if ( $albumId )
        {
            $loadMore = $this->photoAlbumService->countAlbumPhotos($albumId, $exclude);
        }
        else
        {
            $loadMore = $this->photoService->countPhotos($listType, FALSE, $exclude);
            $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_PHOTO, array('listtype' =>$listType,'page'=>1, 'photosPerPage'=>$count,'exclude'=>$exclude,PHOTO_BOL_PhotoService::TYPE_PREVIEW,'onlyCount'=>true)));
            if(isset($resultsEvent->getData()['count'])){
                $loadMore= $resultsEvent->getData()['count'];
            }
        }

        if ( !$loadMore )
        {
            $script = "OWM.trigger('photo.hide_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('photo')->getStaticJsUrl() . 'masonry.pkgd.min.js');
        $js = "
        var \$grid = $('.photo_section').masonry({
              itemSelector: '.owm_photo_list_item',
              originLeft: false,
              transitionDuration: '0s'
            });
        \$grid.masonry()
        setInterval(function(){ \$grid.masonry() }, 100);
        ";
        OW::getDocument()->addOnloadScript($js);
    }
}