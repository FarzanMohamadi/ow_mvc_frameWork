<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum
 * @since 1.7.2
 */
class FORUM_CLASS_TopicAddForm extends Form
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
     * Min post length
     */
    const MIN_POST_LENGTH = 1;

    /**
     * Max post length
     */
    const MAX_POST_LENGTH = 65535;

    /**
     * Title invitation
     * @var string
     */
    protected $titleInvitation;

    /**
     * Class constructor
     * 
     * @param string $name
     * @param string $attachmentUid
     * @param array $groupSelect
     * @param integer $groupId
     * @param boolean $mobileWysiwyg
     * @param boolean $isSectionHidden
     */
    public function __construct($name, $attachmentUid, 
            array $groupSelect, $groupId = null, $mobileWysiwyg = false, $isSectionHidden  = false) 
    {

        parent::__construct($name);
        $lang = OW::getLanguage();

        // attachments
        $attachmentUidField = new HiddenField('attachmentUid');
        $attachmentUidField->setValue($attachmentUid);
        $this->addElement($attachmentUidField);

        // title
        $titleField = new TextField('title');
        $titleField->setLabel(OW::getLanguage()->text('forum', 'new_topic_subject'));
        $titleField->setRequired(true);
        $sValidator = new StringValidator(self::MIN_TITLE_LENGTH, self::MAX_TITLE_LENGTH);
        $sValidator->setErrorMessage($lang->
                text('forum', 'chars_limit_exceeded', array('limit' => self::MAX_TITLE_LENGTH)));

        $titleField->addValidator($sValidator);
        $this->addElement($titleField);
        $forumGroup = FORUM_BOL_ForumService::getInstance()->findGroupById($groupId);
        // group
        if ( $isSectionHidden || ( $forumGroup !=null && $forumGroup->entityId != null) )
        {
            $groupField = new HiddenField('group');
            $groupField->setValue($groupId);
        }
        else
        {
            $groupField = new ForumSelectBox('group');
            $groupField->setOptions($groupSelect);

            if ( $groupId )
            {
                $groupField->setValue($groupId);
            }

            // process list of groups for the validator
            $groupIds = array();

            foreach($groupSelect as $group)
            {
                if ( !$group['value'] || $group['disabled'] )
                {
                    continue;
                }

                $groupIds[] = $group['value'];
            }

            $groupField->setRequired(true);
            $groupField->addValidator(new IntValidator());
            $groupField->addValidator(new InArrayValidator($groupIds));
        }

        $this->addElement($groupField);

        // post
        if ( $mobileWysiwyg )
        {
            $textField = new MobileWysiwygTextarea('text','forum');
        }
        else {
            $textField = new WysiwygTextarea('text','forum', array(
                BOL_TextFormatService::WS_BTN_IMAGE, 
                //BOL_TextFormatService::WS_BTN_VIDEO,
                BOL_TextFormatService::WS_BTN_HTML
            ));
        }

        $textField->setRequired(true);

        $sValidator = new StringValidator(self::MIN_POST_LENGTH, self::MAX_POST_LENGTH);
        $sValidator->setErrorMessage($lang->text('forum', 'chars_limit_exceeded', array('limit' => self::MAX_POST_LENGTH)));
        $textField->addValidator($sValidator);
        $textField->setLabel(OW::getLanguage()->text('forum', 'new_topic_body'));
        $this->addElement($textField);

        // subscribe
        $subscribeField = new CheckboxField('subscribe');
        $subscribeField->setLabel($lang->text('forum', 'subscribe'));
        $subscribeField->setValue(true);
        $this->addElement($subscribeField);

        // submit
        $submit = new Submit('post');
        $submit->setValue($lang->text('forum', 'add_post_btn'));
        $this->addElement($submit);
    }

    /**
     * Set title invitation
     * 
     * @param string $invitation
     * @return void
     */
    public function setTitleInvitation($invitation)
    {
        $this->getElement('title')->setHasInvitation(true)->setInvitation($invitation);
    }
}