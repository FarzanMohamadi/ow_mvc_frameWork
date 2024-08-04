<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.6.1
 */
class PHOTO_CMP_CreateAlbum extends OW_Component
{
    public function __construct( $fromAlbum, $photoIdList )
    {
        parent::__construct();
        
        $form = new PHOTO_CLASS_AlbumAddForm();
        $form->getElement('from-album')->setValue($fromAlbum);
        $form->getElement('photos')->setValue($photoIdList);
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER,array('this' => $this,'form' => $form)));
        $this->addForm($form);
    }
}
