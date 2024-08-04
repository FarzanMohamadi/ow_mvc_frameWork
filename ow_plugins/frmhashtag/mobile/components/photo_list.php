<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_MCMP_PhotoList extends OW_MobileComponent
{

    public function __construct( $tag, $count, $exclude = null )
    {
        parent::__construct();

        $service = FRMHASHTAG_BOL_Service::getInstance();

        $idList = $service->findEntitiesByTag($tag,"photo_comments");
        $ids = array();
        foreach ($idList as $element)
            $ids[] = $element['id'];
        $photos = array();
        if(is_array($idList) && sizeof($idList)>0){
            $photos = PHOTO_BOL_PhotoDao::getInstance()->getPhotoList('latest', 0, count($idList), null, false, $ids);

            $photos = array_slice($photos, count($exclude), $count);

            foreach ( $photos as $key => $photo )
            {
                $photos[$key]['url'] = PHOTO_BOL_PhotoService::getInstance()->getPhotoUrlByPhotoInfo($photo['id'], PHOTO_BOL_PhotoService::TYPE_PREVIEW, $photo['hash'], !empty($photo['dimension']) ? $photo['dimension'] : FALSE);
            }
        }

        $this->assign('photos', $photos);

        foreach ( $photos as $photo )
        {
            array_push($exclude, $photo['id']);
        }

        if ( count($photos)<=$count )
        {
            $script = "OWM.trigger('photo.hide_load_more', {});";
            OW::getDocument()->addOnloadScript($script);
        }
    }
}