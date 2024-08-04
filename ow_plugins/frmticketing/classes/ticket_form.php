<?php
class FRMTICKETING_CLASS_TicketForm extends Form
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
     * @param $ticketInfo
     * @param boolean $mobileWysiwyg
     */
    public function __construct( $name, $attachmentUid, $ticketInfo=null, $mobileWysiwyg = false )
    {

        parent::__construct($name);
        $lang = OW::getLanguage();

        if(isset($ticketInfo))
        {
            $topicIdField = new HiddenField('ticket-id');
            $topicIdField->setValue($ticketInfo['id']);
            $this->addElement($topicIdField);
        }
        // attachments
        $attachmentUidField = new HiddenField('attachmentUid');
        $attachmentUidField->setValue($attachmentUid);
        $this->addElement($attachmentUidField);


        //title
        $titleField = new TextField('title');
        $titleField->setLabel($lang->text('frmticketing','ticket_title_label'));
        $titleField->setHasInvitation(true);
        $titleField->setInvitation($lang->text('frmticketing', 'ticket_title_label'));
        $titleField->setRequired(true);
        if(isset($ticketInfo))
        {
            $titleField->setValue($ticketInfo['title']);
        }
        $this->addElement($titleField);


        // description
        if ( $mobileWysiwyg )
        {
            $descriptionField = new MobileWysiwygTextarea('description','frmticketing');
        }
        else {
            $descriptionField = new WysiwygTextarea('description','frmticketing', array(
                BOL_TextFormatService::WS_BTN_IMAGE, 
                BOL_TextFormatService::WS_BTN_VIDEO, 
                BOL_TextFormatService::WS_BTN_HTML
            ));
        }
        if(isset($ticketInfo)) {
            $descriptionField->setValue($ticketInfo['description']);
        }
        $descriptionField->setRequired(true);
        $sValidator = new StringValidator(self::MIN_TEXT_LENGTH, self::MAX_TEXT_LENGTH);
        $sValidator->setErrorMessage($lang->text('frmticketing', 'chars_limit_exceeded', array('limit' => self::MAX_TEXT_LENGTH)));
        $descriptionField->addValidator($sValidator);
        $descriptionField->setLabel(OW::getLanguage()->text('frmticketing', 'description'));
        $this->addElement($descriptionField);

        $categories = FRMTICKETING_BOL_TicketCategoryService::getInstance()->getTicketCategoryListByStatus();
        $categoryField = new Selectbox('category');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmticketing','select_category');
        foreach ($categories as $category) {
            $option['category_'.$category->id] = $category->title;
        }

        $categoryOptionEvent= OW::getEventManager()->trigger(new OW_Event('ticket.category.option',array('option'=>$option,'userId'=>OW::getUser()->getId())));
        if(isset($categoryOptionEvent->getData()['option']))
        {
            $option=$categoryOptionEvent->getData()['option'];
        }
        $categoryField->setHasInvitation(false);
        $categoryField->setOptions($option);
        $categoryField->addAttribute('id','category');
        $categoryField->setRequired();
        if(isset($ticketInfo['categoryId'])) {
            $categoryField->setValue('category_'.$ticketInfo['categoryId']);
        }else if (isset($ticketInfo['networkId'])){
            $categoryField->setValue('network_'.$ticketInfo['networkId']);
        }

        $validator = new FRMTICKETING_CLASS_CategorySelectValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmticketing', 'category_selected_is_invalid'));
        $categoryField->addValidator($validator);

        $this->addElement($categoryField);

        $orders = FRMTICKETING_BOL_TicketOrderService::getInstance()->getTicketOrderList();
        $orderField = new Selectbox('order');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmticketing','select_order');
        foreach ($orders as $order) {
            $option[$order->id] = $order->title;
        }
        $orderField->setHasInvitation(false);
        $orderField->setOptions($option);
        $orderField->addAttribute('id','order');
        $orderField->setRequired();
        if(isset($ticketInfo['orderId']))
            $orderField->setValue($ticketInfo['orderId']);
        $this->addElement($orderField);

        // submit
        $submit = new Submit('submit');
        $submit->setValue($lang->text('frmticketing', 'save_button'));
        $this->addElement($submit);

        $cancel = new Button('cancel');
        $cancel->setValue(OW::getLanguage()->text('frmticketing','cancel_button'));
        $this->addElement($cancel);

        $cancelUrl = OW::getRouter()->urlForRoute('frmticketing.view_tickets');

        OW::getDocument()->addOnloadScript('
            $("form[name='.$name.'] input[name=cancel]").click(
                function(){
                    window.location = "'.$cancelUrl.'";
                }
            );
        ');
    }
}