<?php
class FRMINVITE_CTRL_Invite extends OW_ActionController
{
    private $userService;

    public function __construct()
    {
        parent::__construct();

        $this->userService = BOL_UserService::getInstance();
    }

    public function index( $params )
    {
        $service = FRMINVITE_BOL_Service::getInstance();
        if(!$service->checkUserPermission() || !OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        $language = OW::getLanguage();

        $budgetLeft = $service->getUserDailyLeftBudget();
        $budgetLeftMessage = $language->text('frminvite','left_budget_message',array('budget'=>$budgetLeft));
        $this->assign('budgetLeftMessage',$budgetLeftMessage);


        // invite members
        $form = new Form('invite-members');

        $emails = new Textarea('emails');
        $form->addElement($emails);
        $emails->setRequired(false);
        $emails->setHasInvitation(true);
        $emails->setInvitation($language->text('admin', 'invite_members_textarea_invitation_text', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));

        /**
         * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
         */
        $sms_enabled = false;
        if(FRMSecurityProvider::checkPluginActive('frmsms', true)){
            $sms_enabled = true;
            $smss = new Textarea('smss');
            $smss->setRequired(false);
            $smss->setHasInvitation(true);
            $smss->setInvitation($language->text('frminvite', 'invite_members_textarea_sms_invitation_text', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));
            $form->addElement($smss);
        }
        $this->assign('sms_enabled', $sms_enabled);

        $submit = new Submit('submit');
        $submit->setValue($language->text('admin', 'invite_members_submit_label'));
        $form->addElement($submit);

        $this->addForm($form);

        if ( OW::getRequest()->isPost())
        {
            if ( $form->isValid($_POST) )
            {

                $data = $form->getValues();

                //secure email text
                $emailsPosted = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['emails']));

                //secure numbers text
                $numbersPosted = null;
                if(isset($data['smss'])){
                    $numbersPosted = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($data['smss']));
                }

                $result = $service->sendInvitation($emailsPosted, $numbersPosted);

                $emailInputData = '';
                $smsInputData = '';

                if (isset($result['valid'])) {
                    if (!$result['valid']) {
                        if (isset($result['email'])) {
                            $emailInputData = $emailsPosted;
                            OW::getFeedback()->error($language->text('frminvite', 'wrong_email_format_error', array('email' => trim($result['email']))));
                        }
                        if (isset($result['number'])) {
                            OW::getFeedback()->error($language->text('frminvite', 'wrong_mobile_format_error', array('phone' => trim($result['number']))));
                            if (isset($numbersPosted)) {
                                $smsInputData = $numbersPosted;
                            }
                        }
                        if (isset($result['limit'])) {
                            OW::getFeedback()->error($result['limit']);
                        }
                    } else if(isset($result['registered_users']) && isset($result['invalidNumbers']) && isset($result['sentInvitationsNumber'])) {
                        // valid true
                        if (sizeof($result['registered_users']) > 0) {
                            $this->assign('registered_users', $result['registered_users']);
                        }
                        if (sizeof($result['invalidNumbers']) > 0) {
                            $this->assign('invalidNumbers', $result['invalidNumbers']);
                        }

                        OW::getFeedback()->info($language->text('frminvite', 'invite_members_success_message', array('num' => $result['sentInvitationsNumber'])));
                        $smsElement = $form->getElement('smss');
                        if (isset($smsElement)) {
                            $smsElement->setValue('');
                        }
                    }
                }
                $form->getElement('emails')->setValue($emailInputData);
                if ($sms_enabled) {
                    $form->getElement('smss')->setValue($smsInputData);
                }
            }
        }
    }
}