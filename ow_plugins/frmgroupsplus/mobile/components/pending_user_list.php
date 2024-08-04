<?php
/**
 * component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.mobile.classes
 * @since 1.0
 */

class FRMGROUPSPLUS_MCMP_PendingUserList extends OW_MobileComponent
{


    /**
     * FRMGROUPSPLUS_MCMP_PendingUserList constructor.
     * @param $groupId
     * @param $count
     */
    public function __construct($groupId, $count = 0)
    {
        parent::__construct();

        $users = GROUPS_BOL_Service::getInstance()->findAllInviteList($groupId);
        $usersInformation = array();
        $userCount = 0;
        if($users!=null){
            $userCount = 10;
            $counter = 0;
            foreach($users as $user){
                if(in_array($user->userId,$usersInformation)){
                    continue;
                }
                if($count == -1 || $counter<$userCount){
                    $counter++;
                    $usersInformation[] = $user->userId;
                }
            }
        }
        $more = false;
        if($count != -1 && sizeof($users)>$userCount){
            $more = true;
        }
        $this->assign('more', $more);
        $data = array();
        if ( !empty($usersInformation) )
        {
            $data = BOL_AvatarService::getInstance()->getDataForUserAvatars($usersInformation);
        }
        $revokeUrl = OW::getRouter()->urlFor('FRMGROUPSPLUS_MCTRL_Groups', 'revoke');

        OW::getLanguage()->addKeyForJs('frmgroupsplus', 'revoke_user_invitation_failed_message');
        OW::getLanguage()->addKeyForJs('frmgroupsplus', 'revoke_user_invitation_success_message');

        $this->assign("data", $data);
        $this->assign("userIdList", $usersInformation);
        $this->assign("groupId", $groupId);
        $this->assign('revokeInvitationUrl', $revokeUrl);
        $this->assign('ShowPendingAllUsersUrl', "OW.ajaxFloatBox('FRMGROUPSPLUS_MCMP_PendingUserList', {groupId: '".$groupId."', count:-1} , {width:700, iconClass: 'ow_ic_add'});");
    }

}
