<?php
class BASE_MCMP_ProfileHeader extends OW_MobileComponent
{
    /**
     *
     * @var BOL_User
     */
    protected $user;
    protected $show_toolbar;

    public function __construct( BOL_User $user, $show_toolbar = true )
    {
        parent::__construct();
        
        $this->user = $user;
        $this->show_toolbar = $show_toolbar;
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $avatarData = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($this->user->id));
        $avatarDto = BOL_AvatarService::getInstance()->findByUserId($this->user->id);
        
        $owner = false;
        
        if ( OW::getUser()->getId() == $this->user->getId() )
        {
            $owner = true;
        }
        
        $isModerator = (OW::getUser()->isAuthorized('base') || OW::getUser()->isAdmin());
                
        $avatarData[$this->user->id]['src'] = BOL_AvatarService::getInstance()->getAvatarUrl( $this->user->getId(), 1, null, true, !($owner || $isModerator) );
        $default_avatar['src'] = BOL_AvatarService::getInstance()->getDefaultAvatarUrl(1);

        $user = array();

        $user["avatar"] = !empty($avatarData[$this->user->id]['src']) ? $avatarData[$this->user->id] : $default_avatar;
        $user["displayName"] = $avatarData[$this->user->id]["title"];
        $url= OW::getRouter()->urlForRoute("base_user_profile_picture", array(
            "username" => $this->user->username));
        $user["avatar"]["url"]=$url;
        $user["avatar"]["label"]=$avatarData[$this->user->id]["label"];
        $user["avatar"]["labelColor"]=$avatarData[$this->user->id]["labelColor"];
        $user["avatar"]["imageInfo"] = BOL_AvatarService::getInstance()->getAvatarInfo((int) $this->user->id, $user["avatar"]["src"]);

        $this->assign("user", $user);
        if($this->show_toolbar){
            $this->addComponent('toolbar', OW::getClassInstance("BASE_MCMP_ProfileActionToolbar", $this->user->id));
        }else{
            $this->assign('toolbar', '');
        }

        $eventParams = array(
                'action' => 'base_view_my_presence_on_site',
                'ownerIdList' => array($this->user->id),
                'viewerId' => OW::getUser()->getId()
            );

        $permissions = OW::getEventManager()->getInstance()->call('privacy_check_permission_for_user_list', $eventParams);
        $showPresence = !(isset($permissions[$this->user->id]['blocked']) && $permissions[$this->user->id]['blocked'] == true);
        $this->assign("showPresence", $showPresence);
        
        $isOnline = null;
        $activityStamp = null;
        
        if ( $showPresence )
        {
            $onlineInfo = BOL_UserService::getInstance()->findOnlineStatusForUserList(array($this->user->id));
            $isOnline = $onlineInfo[$this->user->id];
            
            $activityStamp = $this->user->activityStamp;
        }

        $additionalWidgetEvent = OW::getEventManager()->trigger(new OW_Event('on.before.profile.view.widget.render', array( 'userId' => (int) $this->user->id)));
        if(isset($additionalWidgetEvent->getData()['cmp'])){
            $additionalWidget = $additionalWidgetEvent->getData()['cmp'];
            $this->assign('additionalWidget', $additionalWidget);
        }

        $userId = $this->user->id;
        if(OW::getConfig()->getValue('base', 'mandatory_user_approve')){
            $isApproved = BOL_UserService::getInstance()->isApproved($userId);
            $this->assign('isApproved', $isApproved);
            $hasAccessToApproveUser = BOL_UserService::getInstance()->hasAccessToApproveUser($userId);
            if (!$isApproved && $hasAccessToApproveUser['valid']){
                $moderator_note = BOL_UserApproveDao::getInstance()->getRequestedNotes($userId);
                if (!empty($moderator_note)){
                    $note = $moderator_note['admin_message'];
                    $note = str_replace("\n", '<br />', $note);
                    $this->assign('moderator_note', $note);
                }
            }
        }

        $this->assign("isOnline", $isOnline);
        $this->assign("avatarDto", $avatarDto);
        $this->assign("activityStamp", $activityStamp);
        
        $this->assign('owner', $owner);
        $this->assign('isModerator', $isModerator);
    }
}