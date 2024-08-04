<?php
class PHOTO_MCMP_CreateAlbum extends OW_Component
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
