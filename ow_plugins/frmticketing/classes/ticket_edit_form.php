<?php
class FRMTICKETING_CLASS_TicketEditForm extends Form
{
    /**
     * Class constructor
     * 
     * @param string $name
     * @param string $uid
     * @param FRMTICKETING_BOL_Ticket $ticketDto
     * @param boolean $mobileWysiwyg
     */
    public function __construct( $name, $uid, FRMTICKETING_BOL_Ticket $ticketDto=null, $mobileWysiwyg = false )
    {
        parent::__construct($name);

        $lang = OW::getLanguage();

        $topicIdField = new HiddenField('ticket-id');
        $topicIdField->setValue($ticketDto->id);
        $this->addElement($topicIdField);

        $attachmentUid = new HiddenField('attachmentUid');
        $attachmentUid->setValue($uid);
        $this->addElement($attachmentUid);

        // title
        $titleField = new TextField('title');
        $titleField->setValue($ticketDto->title);
        $titleField->setLabel($lang->text('frmticketing', 'new_ticket_subject'));
        $titleField->setRequired(true);
        $this->addElement($titleField);

        // post
        if ( $mobileWysiwyg )
        {
            $textField = new MobileWysiwygTextarea('text','forum');
        }
        else {
            $textField = new WysiwygTextarea('text', array(
                BOL_TextFormatService::WS_BTN_IMAGE, 
                BOL_TextFormatService::WS_BTN_VIDEO, 
                BOL_TextFormatService::WS_BTN_HTML
            ));
        }

        $textField->setValue($ticketDto->description);
        $textField->setLabel($lang->text('forum', 'new_topic_body'));
        $textField->setRequired(true);
        $sValidator = new StringValidator(FORUM_CLASS_TopicAddForm::MIN_POST_LENGTH, FORUM_CLASS_TopicAddForm::MAX_POST_LENGTH);
        $sValidator->setErrorMessage($lang->text('forum', 'chars_limit_exceeded', array('limit' => FORUM_CLASS_TopicAddForm::MAX_POST_LENGTH)));
        $textField->addValidator($sValidator);
        $this->addElement($textField);

        $submit = new Submit('save');
        $submit->setValue($lang->text('base', 'edit_button'));
        $this->addElement($submit);

        $cancel = new Button('cancel');
        $cancel->setValue(OW::getLanguage()->text('frmticketing','cancel_button'));
        $this->addElement($cancel);

        $cancelUrl=OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketDto->id));

        OW::getDocument()->addOnloadScript('
            $("form[name='.$name.'] input[name=cancel]").click(
                function(){
                    window.location = "'.$cancelUrl.'";
                }
            );
        ');
    }
}