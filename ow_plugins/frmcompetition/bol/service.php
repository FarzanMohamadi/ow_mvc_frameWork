<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcompetition.bol
 * @since 1.0
 */
class FRMCOMPETITION_BOL_Service
{
    const ON_ADD_COMPRTITION = 'frmcompetition.on.add.competition';
    const ON_ADD_POINT_TO_GROUP = 'frmcompetition.on.add.point.to.group';
    const ON_ADD_POINT_TO_USER = 'frmcompetition.on.add.point.to.user';
    const ON_BEFORE_COMPETITION_VIEW_RENDER = 'frm.on.before.competition.view.render';

    private static $classInstance;
    private $competitionDao;
    private $competitionUserDao;
    private $competitionGroupDao;
    public $TYPE_GROUP = 'group';
    public $TYPE_USER = 'user';

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->competitionDao = FRMCOMPETITION_BOL_CompetitionDao::getInstance();
        $this->competitionUserDao = FRMCOMPETITION_BOL_CompetitionUserDao::getInstance();
        $this->competitionGroupDao = FRMCOMPETITION_BOL_CompetitionGroupDao::getInstance();
    }

    /***
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findCompetitions($first = 0, $count = 20){
        return $this->competitionDao->findCompetitions($first, $count);
    }

    /***
     * @return mixed
     */
    public function findAllCompetitions(){
        return $this->competitionDao->findAllCompetitions();
    }

    /***
     * @param $id
     * @return mixed
     */
    public function findCompetitionById($id){
        return $this->competitionDao->findCompetitionById($id);
    }

    /***
     * @param $id
     */
    public function deleteCompetitionById($id){
        $this->competitionDao->deleteCompetitionById($id);
    }

    /***
     * @param $title
     * @param $description
     * @param $active
     * @param $image
     * @param $startDate
     * @param $endDate
     * @param $type
     * @param null $competitionId
     * @return FRMCOMPETITION_BOL_Competition
     */
    public function saveCompetition($title, $description, $active, $image, $startDate, $endDate, $type, $competitionId = null){
        return $this->competitionDao->saveCompetition($title, $description, $active, $image, $startDate, $endDate, $type, $competitionId);
    }


    /***
     * @param $competitionId
     * @return array
     */
    public function findCompetitionGroups($competitionId){
        return $this->competitionGroupDao->findCompetitionGroups($competitionId);
    }

    /***
     * @param $groupId
     * @param $competitionId
     * @param $value
     * @return FRMCOMPETITION_BOL_CompetitionGroup|mixed
     */
    public function saveCompetitionGroup($groupId, $competitionId, $value){
        return $this->competitionGroupDao->saveCompetitionGroup($groupId, $competitionId, $value);
    }

    /***
     * @param $competitionId
     * @return array
     */
    public function findCompetitionUsers($competitionId){
        return $this->competitionUserDao->findCompetitionUsers($competitionId);
    }

    /***
     * @param $userId
     * @param $competitionId
     * @param $value
     * @return FRMCOMPETITION_BOL_CompetitionUser|mixed
     */
    public function saveCompetitionUsers($userId, $competitionId, $value){
        return $this->competitionUserDao->saveCompetitionUsers($userId, $competitionId, $value);
    }

    /***
     * @param $action
     * @param null $titleValue
     * @param null $descriptionValue
     * @param null $activeValue
     * @param null $typeValue
     * @param null $startDateValue
     * @param null $endDateValue
     * @return Form
     */
    public function getCompetitionForm($action, $titleValue = null, $descriptionValue = null, $activeValue = null, $typeValue = null, $startDateValue = null, $endDateValue = null){
        $form = new Form('competition');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $title = new TextField('title');
        $title->setLabel(OW::getLanguage()->text('frmcompetition', 'title'));
        $title->setRequired();
        $title->setValue($titleValue);
        $title->setHasInvitation(false);
        $form->addElement($title);


        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_MORE,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
            BOL_TextFormatService::WS_BTN_VIDEO
        );
        $description = new WysiwygTextarea('description','frmcompetition', $buttons);
        $description->setLabel(OW::getLanguage()->text('frmcompetition', 'description'));
        $description->setSize(WysiwygTextarea::SIZE_L);
        $description->setRequired();
        $description->setValue($descriptionValue);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $active = new CheckboxField('active');
        $active->setLabel(OW::getLanguage()->text('frmcompetition', 'active'));
        $active->setValue($activeValue);
        $form->addElement($active);

        $currentYear = date('Y', time());
        if(OW::getConfig()->getValue('frmjalali', 'dateLocale')==1){
            $currentYear=$currentYear-1;
        }
        $startDate = new DateField('startDate');
        $startDate->setLabel(OW::getLanguage()->text('frmcompetition', 'startDate'));
        $startDate->setRequired();
        $startDate->setMinYear($currentYear - 10);
        $startDate->setMaxYear($currentYear + 10);
        if($startDateValue==null){
            $startDateValue = time();
        }
        $startDateValue = date('Y', $startDateValue) . '/' . date('n', $startDateValue) . '/' . date('j', $startDateValue);
        $startDate->setValue($startDateValue);
        $form->addElement($startDate);

        $optionsTypeList = array();
        $optionsTypeList[$this::getInstance()->TYPE_GROUP] = OW::getLanguage()->text('frmcompetition', 'groups_label');
        $optionsTypeList[$this::getInstance()->TYPE_USER] = OW::getLanguage()->text('frmcompetition', 'users_label');

        $type = new Selectbox('type');
        $type->setLabel(OW::getLanguage()->text('frmcompetition', 'type'));
        $type->setOptions($optionsTypeList);
        $type->setValue($typeValue);
        $type->setRequired(true);
        $type->setHasInvitation(false);
        $form->addElement($type);

        $endDate = new DateField('endDate');
        $endDate->setLabel(OW::getLanguage()->text('frmcompetition', 'endDate'));
        $endDate->setRequired();
        $endDate->setMinYear($currentYear - 10);
        $endDate->setMaxYear($currentYear + 10);
        if($endDateValue==null){
            $endDateValue = time();
        }
        $endDateValue = date('Y', $endDateValue) . '/' . date('n', $endDateValue) . '/' . date('j', $endDateValue);
        $endDate->setValue($endDateValue);
        $form->addElement($endDate);

        $image = new FileField('image');
        $image->setLabel(OW::getLanguage()->text('frmcompetition', 'image'));
        $form->addElement($image);

        $enRoleList = new CheckboxField('enSentNotification');
        $enRoleList->setLabel(OW::getLanguage()->text('frmcompetition', 'notification_form_lbl_published'));
        $form->addElement($enRoleList);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    public function getCompetitionUserForm($action){
        $form = new Form('competitionUser');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);

        $value = new TextField('value');
        $value->setLabel(OW::getLanguage()->text('frmcompetition', 'value'));
        $value->setRequired();
        $value->setHasInvitation(false);
        $value->addValidator(new IntValidator());
        $form->addElement($value);

        $username = new TextField('username');
        $username->setLabel(OW::getLanguage()->text('frmcompetition', 'username'));
        $username->setRequired();
        $username->setHasInvitation(false);
        $form->addElement($username);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $action
     * @return Form
     */
    public function getCompetitionGroupForm($action){
        $form = new Form('competitionGroup');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);

        $value = new TextField('value');
        $value->setLabel(OW::getLanguage()->text('frmcompetition', 'value'));
        $value->setRequired();
        $value->setHasInvitation(false);
        $value->addValidator(new IntValidator());
        $form->addElement($value);

        $optionsGroupList = array();
        $allGroups = $this->findAllGroupList();
        foreach ($allGroups as $group){
            $optionsGroupList[$group->id] = $group->title;
        }

        $group = new Selectbox('groupId');
        $group->setLabel(OW::getLanguage()->text('frmcompetition', 'group_title'));
        $group->setOptions($optionsGroupList);
        $group->setRequired(true);
        $group->setHasInvitation(false);
        $form->addElement($group);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    public function findAllGroupList(){
        if(class_exists('GROUPS_BOL_GroupDao') && OW::getPluginManager()->getPlugin('groups')->isActive()) {
            $groupDao = GROUPS_BOL_GroupDao::getInstance();
            $example = new OW_Example();
            return $groupDao->findListByExample($example);
        }

        return array();
    }

    /***
     * @param $imageName
     * @return null|string
     */
    public function saveFile($imageName){
        if (!((int)$_FILES[$imageName]['error'] !== 0 || !is_uploaded_file($_FILES[$imageName]['tmp_name']) || !UTIL_File::validateImage($_FILES[$imageName]['name']))) {
            $iconName = FRMSecurityProvider::generateUniqueId() . '.' . UTIL_File::getExtension($_FILES[$imageName]['name']);
            $userfilesDir = OW::getPluginManager()->getPlugin('frmcompetition')->getUserFilesDir();
            $tmpImgPath = $userfilesDir . $iconName;
            $image = new UTIL_Image($_FILES[$imageName]['tmp_name']);
            $image->saveImage($tmpImgPath);
            return $iconName;
        }

        return null;
    }

    /***
     * @param $imageName
     * @return string
     */
    public function getFile($imageName){
        return OW::getPluginManager()->getPlugin('frmcompetition')->getUserFilesUrl() . $imageName;
    }

    public function onCollectNotificationActions( BASE_CLASS_EventCollector $e )
    {
        $e->add(array(
            'section' => 'frmcompetition',
            'action' => 'competition-add_competition',
            'description' => OW::getLanguage()->text('frmcompetition', 'email_notifications_setting_competition'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmcompetition', 'email_notification_section_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
        $e->add(array(
            'section' => 'frmcompetition',
            'action' => 'competition-add_group_point',
            'description' => OW::getLanguage()->text('frmcompetition', 'email_notifications_setting_group_get_point'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmcompetition', 'email_notification_section_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
        $e->add(array(
            'section' => 'frmcompetition',
            'action' => 'competition-add_user_point',
            'description' => OW::getLanguage()->text('frmcompetition', 'email_notifications_setting_user_get_point'),
            'selected' => true,
            'sectionLabel' => OW::getLanguage()->text('frmcompetition', 'email_notification_section_label'),
            'sectionIcon' => 'ow_ic_write'
        ));
    }

    public function getPartialDescription($fullDescription){
        $sentenceCorrected = false;
        if ( mb_strlen($fullDescription) > 300 )
        {
            $sentence = strip_tags($fullDescription);
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
        }
        if($sentenceCorrected){
            $content = $sentence.'...';
        }
        else{
            $content = UTIL_String::truncate(strip_tags($fullDescription), 300, "...");
        }

        return $content;
    }

    public function onAddCompetitionEnt(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['competitionDto']) || !class_exists('NOTIFICATIONS_BOL_Service')) {
            return;
        }
        $competitionDto = $params['competitionDto'];
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $users = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));

        $notificationParams = array(
            'pluginKey' => 'frmcompetition',
            'action' => 'competition-add_competition',
            'entityType' => 'competition-add_competition',
            'entityId' => (int)$competitionDto->getId(),
            'userId' => null,
            'time' => time()
        );

        $notificationData = array(
            'string' => array(
                'key' => 'frmcompetition+competition_notification_string',
                'vars' => array(
                    'competitionTitle' => $competitionDto->title,
                    'competitionUrl' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId()))
                )
            ),
            'avatar' => $avatars[OW::getUser()->getId()],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId()))
        );

        $userIds = [];
        foreach ( $users as  $user ) {
            if ($user->getId() == OW::getUser()->getId()) {
                continue;
            }
            $userIds[] = $user->getId();
        }

        // send notifications in batch to userIds
        $event = new OW_Event('notifications.batch.add',
            ['userIds'=>$userIds, 'params'=>$notificationParams],
            $notificationData);
        OW::getEventManager()->trigger($event);

        // set them as seen
        $notifService = NOTIFICATIONS_BOL_Service::getInstance();
        foreach ( $userIds as  $userId ) {
            $notification = $notifService->findNotification('competition-add_competition', (int)$competitionDto->getId(), $userId);
            if($notification!=null) {
                $notification->sent = 0;
                $notifService->saveNotification($notification);
            }
        }
    }

    public function onAddPointToGroup(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['competitionId']) || !class_exists('NOTIFICATIONS_BOL_Service') ||
            !isset($params['groupId']) || !isset($params['points'])) {
            return;
        }
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['groupId']);
        $groupUrl = GROUPS_BOL_Service::getInstance()->getGroupUrl($groupDto);

        if ( $groupDto === null )
        {
            throw new Redirect404Exception();
        }
        $competitionDto=$this->findCompetitionById($params['competitionId']);
        if(!$competitionDto){
            throw new Redirect404Exception();
        }
        $userIds = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($params['groupId']);
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));

        $notificationParams = array(
            'pluginKey' => 'frmcompetition',
            'action' => 'competition-add_group_point',
            'entityType' => 'competition-add_group_point',
            'entityId' => (int)$competitionDto->getId(),
            'userId' => null,
            'time' => time()
        );

        $notificationData = array(
            'string' => array(
                'key' => 'frmcompetition+group_add_point_notification_string',
                'vars' => array(
                    'competitionTitle' => $competitionDto->title,
                    'competitionUrl' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId())),
                    'competitionPoint'=>$params['points'],
                    'groupTitle' =>$groupDto->title,
                    'groupUrl' =>$groupUrl
                )
            ),
            'avatar' => $avatars[OW::getUser()->getId()],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId()))
        );

        // send notifications in batch to userIds
        $event = new OW_Event('notifications.batch.add',
            ['userIds'=>$userIds, 'params'=>$notificationParams],
            $notificationData);
        OW::getEventManager()->trigger($event);

        // set them as seen
        foreach ( $userIds as  $userId ) {
            $notifService = NOTIFICATIONS_BOL_Service::getInstance();
            $notification = $notifService->findNotification('competition-add_group_point', (int)$competitionDto->getId(), $userId);
            if($notification!=null) {
                $notification->sent = 0;
                $notifService->saveNotification($notification);
            }
        }
    }

    public function onAddPointToUser(OW_Event $event){
        $params = $event->getParams();
        if (!isset($params['competitionId']) || !class_exists('NOTIFICATIONS_BOL_Service') ||
            !isset($params['userId']) || !isset($params['points'])) {
            return;
        }
        $competitionDto=$this->findCompetitionById($params['competitionId']);
        if(!$competitionDto){
            throw new Redirect404Exception();
        }
        $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array(OW::getUser()->getId()));

        $notificationParams = array(
            'pluginKey' => 'frmcompetition',
            'action' => 'competition-add_user_point',
            'entityType' => 'competition-add_user_point',
            'entityId' => (int)$competitionDto->getId(),
            'userId' => $params['userId'],
            'time' => time()
        );

        $notificationData = array(
            'string' => array(
                'key' => 'frmcompetition+user_add_point_notification_string',
                'vars' => array(
                    'competitionTitle' => $competitionDto->title,
                    'competitionUrl' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId())),
                    'competitionPoint'=>$params['points']
                )
            ),
            'avatar' => $avatars[OW::getUser()->getId()],
            'content' => '',
            'url' => OW::getRouter()->urlForRoute('frmcompetition.competition', array('id' => $competitionDto->getId()))
        );
        $event = new OW_Event('notifications.add', $notificationParams, $notificationData);
        OW::getEventManager()->trigger($event);
        $notifService = NOTIFICATIONS_BOL_Service::getInstance();
        $notification = $notifService->findNotification('competition-add_user_point', (int)$competitionDto->getId(), $params['userId']);
        if($notification!=null) {
            $notification->sent = 0;
            $notifService->saveNotification($notification);
        }
    }

    public function getEditedDataNotification(OW_Event $event)
    {
        $params = $event->getParams();
        $notificationData = $event->getData();
        if ($params['pluginKey'] != 'frmcompetition')
            return;

        $entityType = $params['entityType'];
        $entityId =  $params['entityId'];
        if ($entityType == 'competition-add_user_point' || $entityType == 'competition-add_competition') {
            $competition= FRMCOMPETITION_BOL_CompetitionDao::getInstance()->findById($entityId);
            if(isset($competition)) {
                $notificationData["string"]["vars"]["competitionTitle"] = $competition->title;
            }
        } elseif ($entityType == 'competition-add_group_point') {
            $competition= FRMCOMPETITION_BOL_CompetitionDao::getInstance()->findById($entityId);
            if(isset($competition)) {
                $notificationData["string"]["vars"]["competitionTitle"] = $competition->title;
                if(FRMSecurityProvider::checkPluginActive('groups', true)) {
                    $groupUrlArray = explode('/', $notificationData["string"]["vars"]["groupUrl"]);
                    $groupId = end($groupUrlArray);
                    $group=GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
                    if(isset($group)) {
                        $notificationData['string']["vars"]["groupTitle"] = $group->title;
                    }
                }
            }
        }

        $event->setData($notificationData);
    }
}
