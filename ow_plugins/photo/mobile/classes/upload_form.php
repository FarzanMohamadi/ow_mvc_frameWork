<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.mobile.controllers
 * @since 1.6.0
 */
class PHOTO_MCLASS_UploadForm extends Form
{
    public function __construct( )
    {
        parent::__construct('upload-form');

        $language = OW::getLanguage();

        $this->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $fileField = new FileField('photo');
        //$fileField->setRequired(true);
        $this->addElement($fileField);

        // album Field
        $albumField = new TextField('album');
        $albumField->setRequired(true);
        $albumField->setHasInvitation(true);
        $albumField->setId('album_input');
        $albumField->setInvitation($language->text('photo', 'create_album'));
        $this->addElement($albumField);

        // description Field
        $descField = new Textarea('description');
        $descField->setHasInvitation(true);
        $descField->setInvitation($language->text('photo', 'describe_photo'));
        $this->addElement($descField);

        $cancel = new Submit('cancel', false);
        $cancel->setValue($language->text('base', 'cancel_button'));
        $this->addElement($cancel);

        $submit = new Submit('submit', false);
        $this->addElement($submit);
    }
}