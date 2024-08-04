<?php
/**
 * Singleton. 'InsertLink' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_MCMP_InsertLink extends OW_MobileComponent
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

        $title = !empty($params['linkText']) 
            ? trim(strip_tags($params['linkText'])) 
            : null;

        // add a form
        $form = new InsertLinkForm();
        $form->setValues(array(
           'title' => $title  
        ));

        $this->addForm($form);       
    }
}

class InsertLinkForm extends Form
{
    /**
     * Min title length
     */
    const MIN_TITLE_LENGTH = 1;

    /**
     * Max title length
     */
    const MAX_TITLE_LENGTH = 255;
    
    /**
     * Min link length
     */
    const MIN_LINK_LENGTH = 3;

    /**
     * Max link length
     */
    const MAX_LINK_LENGTH = 255;

    public function __construct()
    {
        parent::__construct('insertLink');
 
        // title
        $titleField = new TextField('title');
        $titleField->setRequired(true)->setHasInvitation(true)->setInvitation(OW::getLanguage()->text('base', 'ws_link_text_label'));

        $sValidator = new StringValidator(self::MIN_TITLE_LENGTH, self::MAX_TITLE_LENGTH);
        $sValidator->setErrorMessage(OW::getLanguage()->
                text('base', 'chars_limit_exceeded', array('limit' => self::MAX_TITLE_LENGTH)));

        $titleField->addValidator($sValidator);
        $this->addElement($titleField);

        // link
        $linkField = new TextField('link');
        $linkField->setRequired(true)->setHasInvitation(true)->setInvitation(OW::getLanguage()->text('base', 'ws_link_url_label'));
        $sValidator = new StringValidator(self::MIN_LINK_LENGTH, self::MAX_LINK_LENGTH);
        $sValidator->setErrorMessage(OW::getLanguage()->
                text('base', 'chars_limit_exceeded', array('limit' => self::MAX_LINK_LENGTH)));

        $linkField->addValidator($sValidator);
        $linkField->addValidator(new UrlValidator());
        $this->addElement($linkField);

        // submit
        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('base', 'ws_insert_label'));
        $this->addElement($submit);
    }
}