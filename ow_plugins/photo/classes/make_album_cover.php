<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.classes
 * @since 1.6.1
 */
class PHOTO_CLASS_MakeAlbumCover extends Form
{
    public function __construct()
    {
        parent::__construct('album-cover-maker');
        
        $this->setAjax(TRUE);
        $this->setAction(OW::getRouter()->urlForRoute('photo.ajax_album_cover'));
        $this->setAjaxResetOnSuccess(TRUE);

        $coords = new HiddenField('coords');
        $this->addElement($coords);

        $albumIdField = new HiddenField('albumId');
        $albumIdField->setRequired();
        $albumIdField->addValidator(new PHOTO_CLASS_AlbumOwnerValidator());
        $this->addElement($albumIdField);

        $photoIdField = new HiddenField('photoId');
        $this->addElement($photoIdField);

        $submit = new Submit('save');
        $submit->setValue(OW::getLanguage()->text('photo', 'btn_edit'));
        $this->addElement($submit);
    }
}
