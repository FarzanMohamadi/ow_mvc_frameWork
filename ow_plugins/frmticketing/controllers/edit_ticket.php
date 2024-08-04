<?php
class FRMTICKETING_CTRL_EditTicket extends OW_ActionController
{

    private $ticketService;

    public function __construct()
    {
        parent::__construct();
        $this->ticketService= FRMTICKETING_BOL_TicketService::getInstance();

    }
    /**
     * Controller's default action
     *
     * @param array $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function index( array $params = null )
    {

        if ( !isset($params['ticketId']) || !($ticketId = (int) $params['ticketId']) )
        {
            throw new Redirect404Exception();
        }

        $ticketInfo =  $this->ticketService->findTicketInfoById($ticketId);
        $ticketDto=  $this->ticketService->findTicketById($ticketId);

        if ( !$ticketDto )
        {
            throw new Redirect404Exception();
        }


        $userId = OW::getUser()->getId();

        $isTicketManager = OW::getUser()->isAuthorized('frmticketing', 'view_tickets') || OW::getUser()->isAdmin();
        $isOwner=OW::getUser()->getId()==$ticketInfo['userId'];

        if ( !$isTicketManager && !$isOwner )
        {
            throw new Redirect404Exception();
        }


        $uid = FRMSecurityProvider::generateUniqueId();
        $editTicketForm = $this->generateEditTicketForm($ticketInfo, $uid);
        $this->addForm($editTicketForm);
        $lang = OW::getLanguage();
        $router = OW::getRouter();


        $ticketUrl = $router->urlForRoute('frmticketing.view_ticket', array('ticketId' => $ticketInfo['id']));

        $lang->addKeyForJs('frmticketing', 'confirm_delete_attachment');

        $attachmentService = FRMTICKETING_BOL_TicketAttachmentService::getInstance();

        $attachments = $attachmentService->findAttachmentsByEntityIdList(array($ticketInfo['id']),FRMTICKETING_BOL_TicketAttachmentDao::TICKET_TYPE);
        if( isset($attachments[$ticketId]) ){
            $this->assign('attachments', $attachments[$ticketId]);
        }
        else{
            $this->assign('attachments', array());
        }

        $attachmentCmp = new BASE_CLASS_FileAttachment('frmticketing', $uid);
        $this->addComponent('attachmentsCmp', $attachmentCmp);

        if ( OW::getRequest()->isPost() && $editTicketForm->isValid($_POST) )
        {
            $values = $editTicketForm->getValues();

            // update the ticket
            $this->ticketService->editTicket($userId,
                $values, $ticketDto);

            $this->redirect($ticketUrl);
        }

        OW::getDocument()->setHeading(OW::getLanguage()->text('frmticketing', 'edit_ticket_title'));
        OW::getDocument()->setHeadingIconClass('ow_ic_edit');

        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$ticketDto->id,'isPermanent'=>true,'activityType'=>'delete_attachment')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $attachmentDeleteCode = $frmSecuritymanagerEvent->getData()['code'];
            $this->assign('attachmentDeleteCode',$attachmentDeleteCode);
        }
    }

    /**
     * Generates edit topic form.
     *
     * @param $ticketInfo
     * @param $uid
     * @return Form
     */
    private function generateEditTicketForm($ticketInfo, $uid )
    {
        $form = new FRMTICKETING_CLASS_TicketForm(
            'edit-ticket-form',
            $uid,
            $ticketInfo
        );

        $this->addForm($form);
        return $form;
    }
}
