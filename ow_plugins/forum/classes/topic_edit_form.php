<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum
 * @since 1.7.2
 */
class FORUM_CLASS_TopicEditForm extends Form
{
    /**
     * Class constructor
     * 
     * @param string $name
     * @param string $uid
     * @param FORUM_BOL_Topic $topicDto
     * @param FORUM_BOL_Post $postDto
     * @param boolean $mobileWysiwyg
     */
    public function __construct( $name, $uid, FORUM_BOL_Topic $topicDto, FORUM_BOL_Post $postDto, $mobileWysiwyg = false ) 
    {
        parent::__construct($name);

        $lang = OW::getLanguage();

        $topicIdField = new HiddenField('topic-id');
        $topicIdField->setValue($topicDto->id);
        $this->addElement($topicIdField);

        $postIdField = new HiddenField('post-id');
        $postIdField->setValue($postDto->id);
        $this->addElement($postIdField);

        $attachmentUid = new HiddenField('attachmentUid');
        $attachmentUid->setValue($uid);
        $this->addElement($attachmentUid);

        // title
        $titleField = new TextField('title');
        $titleField->setValue($topicDto->title);
        $titleField->setLabel($lang->text('forum', 'new_topic_subject'));
        $titleField->setRequired(true);
        $sValidator = new StringValidator(FORUM_CLASS_TopicAddForm::MIN_TITLE_LENGTH, FORUM_CLASS_TopicAddForm::MAX_TITLE_LENGTH);
        $sValidator->setErrorMessage($lang->
                text('forum', 'chars_limit_exceeded', array('limit' => FORUM_CLASS_TopicAddForm::MAX_TITLE_LENGTH)));

        $titleField->addValidator($sValidator);
        $this->addElement($titleField);

        // post
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

        $textField->setValue($postDto->text);
        $textField->setLabel($lang->text('forum', 'new_topic_body'));
        $textField->setRequired(true);
        $sValidator = new StringValidator(FORUM_CLASS_TopicAddForm::MIN_POST_LENGTH, FORUM_CLASS_TopicAddForm::MAX_POST_LENGTH);
        $sValidator->setErrorMessage($lang->text('forum', 'chars_limit_exceeded', array('limit' => FORUM_CLASS_TopicAddForm::MAX_POST_LENGTH)));
        $textField->addValidator($sValidator);
        $this->addElement($textField);

        $submit = new Submit('save');
        $submit->setValue($lang->text('base', 'edit_button'));
        $this->addElement($submit);
    }
}