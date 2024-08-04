<?php
class FRMTICKETING_CTRL_AddTicket extends OW_ActionController
{

    /**
     * Class constructor
     */

    private $ticketService;

    public function __construct()
    {
        parent::__construct();
        $this->ticketService= FRMTICKETING_BOL_TicketService::getInstance();

    }

    public function index( array $params )
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('frmticketing', 'add_ticket_page_title'));

        $attachmentUniqueId = FRMSecurityProvider::generateUniqueId();
        $addTicketForm = $this->generateAddTicketForm($attachmentUniqueId);
        $attachmentCmp = new BASE_CLASS_FileAttachment('frmticketing', $attachmentUniqueId);
        $this->addComponent('attachmentsCmp', $attachmentCmp);

        if ( OW::getRequest()->isPost() && $addTicketForm->isValid($_POST) )
        {
            $data = $addTicketForm->getValues();

            $ticketDto = $this->ticketService->addTicket($data);
            if(isset($ticketDto))
            {
                $this->sendNewTicketNotification($ticketDto);
            }
            $redirectUrl=OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketDto->id));
            $this->redirect($redirectUrl);
        }
    }

    private function sendNewTicketNotification($ticketDto)
    {
        $users= FRMTICKETING_BOL_TicketCategoryUserService::getInstance()->findUsersOfCategory($ticketDto->categoryId);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($ticketDto->userId));
        $actor = array(
            'name' => BOL_UserService::getInstance()->getDisplayName($ticketDto->userId),
            'url' => BOL_UserService::getInstance()->getUserUrl($ticketDto->userId)
        );
        $description = nl2br(UTIL_String::truncate($ticketDto->description, 300, '...'));
        foreach ( $users as  $user ) {
            if($user->getId() ==$ticketDto->userId){
                continue;
            }
            $notifService = NOTIFICATIONS_BOL_Service::getInstance();
            $notification = $notifService->findNotification('ticket-add', (int)$ticketDto->id, $user->getId());
            if(isset($notification)) {
                $notification->sent = 0;
                $notification->viewed = 0;
                $notifService->saveNotification($notification);
            }else {
                $event = new OW_Event('notifications.add', array(
                    'pluginKey' => 'frmticketing',
                    'entityType' => 'ticket-add',
                    'entityId' => (int)$ticketDto->id,
                    'action' => 'receive-ticket-update',
                    'userId' => $user->getId(),
                    'time' => time()
                ), array(
                    'avatar' => $avatars[$ticketDto->userId],
                    'string' => array(
                        'key' => 'frmticketing+ticket_notification_string',
                        'vars' => array(
                            'actor' => $actor['name'],
                            'actorUrl' => $actor['url'],
                            'title' => $ticketDto->title,
                            'url' => OW::getRouter()->urlForRoute('frmticketing.view_ticket', array('ticketId' => $ticketDto->id))
                        )
                    ),
                    'content' => $description,
                    'url' => OW::getRouter()->urlForRoute('frmticketing.view_ticket', array('ticketId' => $ticketDto->id))
                ));
                OW::getEventManager()->trigger($event);
            }
        }
    }
    /**
     * Generates add ticket form.
     *
     * @param string $uid
     * @return Form
     */
    private function generateAddTicketForm($uid)
    {
        $form = new FRMTICKETING_CLASS_TicketForm(
            'add-ticket-form',
            $uid,
            null,
            false
        );

        $form->setAction(OW::getRouter()->urlForRoute('frmticketing.add-ticket'));
        $this->addForm($form);
        return $form;
    }

}

