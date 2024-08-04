<?php
final class FRMINVITE_BOL_Service
{
    const CONF_INVITATION_COUNT_ON_PAGE = 5;
    const ON_SEND_INVITATION='frminvite.on.send.invitation';

    private static $classInstance;
    private $invitationDetailsDao;
    private $limitationDao;
    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->invitationDetailsDao = FRMINVITE_BOL_InvitationDetailsDao::getInstance();
        $this->limitationDao = FRMINVITE_BOL_InvitationLimitationDao::getInstance();
    }


    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

   public function checkUserPermission(){
       $haspermission = OW::getUser()->isAuthorized('frminvite', 'invite');
       return $haspermission;
   }

    public function onSendInvitation(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['senderId']) && isset($params['invitedEmail'])){
            $senderId = $params['senderId'];
            $invitedEmail = $params['invitedEmail'];
            $this->invitationDetailsDao->addInvitationDetails($senderId,$invitedEmail);
        }
    }

    public function getInvitationDetailsData($page,$invitationDataCount)
    {
        if ( $page === null )
        {
            $first = 0;
            $count = (int) $invitationDataCount;
        }
        else
        {
            $config =  OW::getConfig();
            $count = $config->getValue('frminvite', 'invitation_view_count');
            $page = ( $page === null ) ? 1 : (int) $page;
            $first = ( $page - 1 ) * $count;
        }
        $items = $this->invitationDetailsDao->getInvitationDetailsData($first,$count);
        $data = array();
        foreach($items['data'] as $item){
            $userName=BOL_UserService::getInstance()->getUserName($item['senderId']);
            $displayName = BOL_UserService::getInstance()->getDisplayName($item['senderId']);
            if(isset($userName)) {
                $item['senderUserName'] = $userName;
            }
            if(isset($displayName)) {
                $item['senderDisplayName'] = $displayName;
            }
            $data['data'][]= $item;
        }
        return $data;
    }

    public function getInvitationDetailsDataCount()
    {
        return $this->invitationDetailsDao->getInvitationDetailsDataCount();
    }

    /**
     * @param $sectionId
     * @return array
     */
    public function getAdminSections($sectionId)
    {
        $sections = array();

        for ($i = 1; $i <= 4; $i++) {
            $sections[] = array(
                'sectionId' => $i,
                'active' => $sectionId == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frminvite.admin.section-id', array('sectionId' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }
        return $sections;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frminvite', 'invitationDetailsInfo');
        }
        else if ($sectionId == 2) {
            return OW::getLanguage()->text('frminvite', 'viewCountSetting');
        } else if ($sectionId == 3) {
            return OW::getLanguage()->text('frminvite', 'createInvitationLink');
        } else if ($sectionId == 4) {
            return OW::getLanguage()->text('frminvite', 'general_setting');
        }
    }

    /**
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * i.moradnejad@gmail.com
     */
    public function createInvitationLink(){
        $dto = new BOL_InviteCode();
        $dto->setCode(UTIL_String::getRandomString(20));
        $dto->setUserId(0);
        $dto->setExpiration_stamp(time() + 3600 * 24 * 30);
        BOL_InviteCodeDao::getInstance()->save($dto);
        $url = OW_URL_HOME.'join?code='.$dto->code;
        return $url;
    }

    /**
     * @param integer $neededBudget
     * @return bool
     */
    public function checkInviteLimit($neededBudget){
        $userId = OW::getUser()->getId();
        $max = 100;
        if (OW::getConfig()->configExists('frminvite', 'invite_daily_limit'))
            $max = OW::getConfig()->getValue('frminvite', 'invite_daily_limit');
        $limit = $this->limitationDao->findByUserId($userId);
        if(!isset($limit)){
            $limit = new FRMINVITE_BOL_InvitationLimitation();
            $limit->setUserId($userId);
            $limit->setNumber(0);
            $limit->setDate(date('Y-m-d'));
        }
        if(date('Y-m-d') != $limit->getDate()){
            $limit->setNumber(0);
            $limit->setDate(date('Y-m-d'));
        }
        if ($max - $limit->getNumber() >= $neededBudget) {
            $limit->setNumber($limit->getNumber() + $neededBudget);
            $this->limitationDao->save($limit);
            return true;
        }
        return false;
    }

    public function canUserSendInvitation($emailList) {
        if ( sizeof($emailList) > (int)OW::getConfig()->getValue('base', 'user_invites_limit') )
        {
            OW::getFeedback()->error(OW::getLanguage()->text('admin', 'invite_members_max_limit_message', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit'))));
            return false;
        }
        return true;
    }

    public function sendInvitation($emails = null, $numbers = null) {
        if (isset($emails) && !is_array($emails) && !is_string($emails)) {
            return array('valid' => false);
        }

        if (isset($numbers) && !is_array($numbers) && !is_string($numbers)) {
            return array('valid' => false);
        }

        if (isset($emails) && !is_array($emails)) {
            $emails = array_unique(preg_split('/\n/', $emails));
        }

        if (isset($numbers) && !is_array($numbers)) {
            $numbers = array_unique(preg_split('/\n/', $numbers));
        }

        $language = OW::getLanguage();

        //email list
        $emailList = array();
        if(isset($emails)) {
            foreach ($emails as $email) {
                if (empty(trim($email))) {
                    continue;
                }
                $emailValue = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($email));
                $emailValue = trim($emailValue);
                if (!UTIL_Validator::isEmailValid($emailValue)) {
                    return array('valid' => false, 'email' => $emailValue);
                }
                $emailList[] = $emailValue;
            }
            if (!$this->canUserSendInvitation($emailList)) {
                return array('valid' => false);
            }
        }

        //sms list
        $numList = array();
        if(isset($numbers)) {
            foreach ($numbers as $number) {
                if(empty(trim($number))) {
                    continue;
                }
                $numberValue = UTIL_HtmlTag::escapeHtml(UTIL_HtmlTag::stripTagsAndJs($number));
                $numberValue = trim($numberValue);
                if (!FRMSMS_BOL_Service::getInstance()->isMobileValueValid($numberValue)) {
                    return array('valid' => false, 'number' => $numberValue);
                }
                $numList[] = $numberValue;
            }
            if (sizeof($numList) > (int)OW::getConfig()->getValue('base', 'user_invites_limit')) {
                $limitError = $language->text('admin', 'invite_members_max_limit_message', array('limit' => (int)OW::getConfig()->getValue('base', 'user_invites_limit')));
                return array('valid' => false, 'limit' => $limitError);
            }
        }

        //send list
        if ( empty($emailList) && empty($numList))
        {
            $limitError = $language->text('admin', 'invite_members_min_limit_message');
            return array('valid' => false, 'limit' => $limitError);
        }

        $num = sizeof($emailList);
        $neededBudget = sizeof($emailList) + sizeof($numList);

        if($this->checkInviteLimit($neededBudget)) {
            foreach ($emailList as $email) {
                BOL_UserService::getInstance()->sendAdminInvitation($email);
                OW::getEventManager()->trigger(new OW_Event('frminvite.on.send.invitation', array('senderId' => OW::getUser()->getId(), 'invitedEmail' => $email)));
            }

            $registeredUsers = array();
            $invalidNumbers = array();

            foreach ($numList as $number) {
                $event = new OW_Event('frmsms.phone_number_check', array('number' => $number));
                OW_EventManager::getInstance()->trigger($event);
                $eventData = $event->getData();

                if (!isset($eventData) || !isset($eventData['user_exists']) || $eventData['user_exists']|| isset($eventData['userPhone_notIn_ValidList']))
                {
                    if(isset($eventData['userPhone_notIn_ValidList']))
                    {
                        $invalidNumbers[] = array(
                            'number' => $number
                        );
                    }else {
                        $user = BOL_UserService::getInstance()->findUserById($eventData['user_id']);
                        if (isset($user)) {
                            $registeredUsers[] = array(
                                'id' => $user->getId(),
                                'number' => $number,
                                'username' => $user->getUsername(),
                                'url' => OW::getRouter()->urlForRoute('base_user_profile', array('username' => $user->getUsername()))
                            );
                        }
                    }
                    continue;
                }

                $num++;

                $text = $language->text('frminvite', 'sms_template_invite_user_text', array('url' => FRMINVITE_BOL_Service::getInstance()->createInvitationLink()));
                $eventInvite = OW::getEventManager()->trigger(new OW_Event('frm.before.send.invite', array('text' => $text, 'number' => $number)));
                if(isset($eventInvite->getData()['text'])){
                    $text = $eventInvite->getData()['text'];
                }

                FRMSMS_BOL_Service::getInstance()->sendSMSWithCron($number, $text);

                OW::getEventManager()->trigger(new OW_Event('frminvite.on.send.invitation', array('senderId' => OW::getUser()->getId(), 'invitedNumber' => $number)));
            }

            return array('valid' => true, 'registered_users' => $registeredUsers, 'invalidNumbers' => $invalidNumbers, 'sentInvitationsNumber' => $num);
        }else{
            $limitError = $language->text('frminvite', 'reach_daily_limit_error');
            return array('valid' => false, 'limit' => $limitError);
        }
    }

    /**
     * @return int
     */
    public function getUserDailyLeftBudget(){
        $userId = OW::getUser()->getId();
        $max = 100;
        if (OW::getConfig()->configExists('frminvite', 'invite_daily_limit'))
            $max = OW::getConfig()->getValue('frminvite', 'invite_daily_limit');
        $limit = $this->limitationDao->findByUserId($userId);
        if(isset($limit) && date('Y-m-d') == $limit->getDate())
            return $max - $limit->getNumber();
        return $max;
    }
}