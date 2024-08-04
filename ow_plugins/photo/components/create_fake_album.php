<?php
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.7.6
 */
class PHOTO_CMP_CreateFakeAlbum extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $form = new PHOTO_CLASS_CreateFakeAlbumForm();
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER,array('this' => $this,'form' => $form , 'albumId' => null)));
        $this->addForm($form);

        $this->assign('extendInputs', $form->getExtendedElements());
        $this->assign('userId', OW::getUser()->getId());
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('photo')->getStaticCssUrl() . 'photo_upload.css');
    }
}
