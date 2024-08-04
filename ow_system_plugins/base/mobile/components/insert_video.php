<?php
/**
 * Singleton. 'InsertVideo' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_InsertVideo extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      string linkText
     */
    public function __construct( array $params = array() )
    {
        parent::__construct();

        // add a form
        $form = new InsertVideoForm();
        $this->addForm($form);       
    }
}

class InsertVideoForm extends Form
{
    public function __construct()
    {
        parent::__construct('insertVideo');

        // link
        $linkField = new TextField('link');
        $linkField->setRequired(true)->setHasInvitation(true)->setInvitation(OW::getLanguage()->text('base', 'ws_video_text_label'));
        $linkField->addValidator(new UrlValidator());
        $this->addElement($linkField);

        // submit
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base', 'ws_insert_label'));
        $this->addElement($submit);
    }
}