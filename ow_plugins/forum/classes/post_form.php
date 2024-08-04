<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum
 * @since 1.7.2
 */
class FORUM_CLASS_PostForm extends Form
{
    /**
     * Min text length
     */
    const MIN_TEXT_LENGTH = 1;

    /**
     * Max text length
     */
    const MAX_TEXT_LENGTH = 65535;

    /**
     * Text invitation
     * @var string
     */
    protected $textInvitation;

    /**
     * Class constructor
     * 
     * @param string $name
     * @param string $attachmentUid
     * @param integer $topicId
     * @param boolean $mobileWysiwyg
     */
    public function __construct( $name, $attachmentUid, $topicId, $mobileWysiwyg = false ) 
    {

        parent::__construct($name);
        $lang = OW::getLanguage();

        $topicIdField = new HiddenField('topic');
        $topicIdField->setValue($topicId);
        $this->addElement($topicIdField);

        // attachments
        $attachmentUidField = new HiddenField('attachmentUid');
        $attachmentUidField->setValue($attachmentUid);
        $this->addElement($attachmentUidField);

        // text
        if ( $mobileWysiwyg )
        {
            $textField = new MobileWysiwygTextarea('text','forum');
        }
        else {
            $textField = new WysiwygTextarea('text','forum', array(
                BOL_TextFormatService::WS_BTN_IMAGE, 
                BOL_TextFormatService::WS_BTN_VIDEO, 
                BOL_TextFormatService::WS_BTN_HTML
            ));
        }

        $textField->setRequired(true);
        $sValidator = new StringValidator(self::MIN_TEXT_LENGTH, self::MAX_TEXT_LENGTH);
        $sValidator->setErrorMessage($lang->text('forum', 'chars_limit_exceeded', array('limit' => self::MAX_TEXT_LENGTH)));
        $textField->addValidator($sValidator);
        $this->addElement($textField);

        // submit
        $submit = new Submit('submit');
        $submit->setValue($lang->text('forum', 'send_answer_post_btn'));
        $this->addElement($submit);
    }
}