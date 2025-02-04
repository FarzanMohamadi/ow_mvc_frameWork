<?php
class FRMNEWSFEEDPLUS_CTRL_Forward extends OW_ActionController
{
    public static $PRIVACY_EVERYBODY = 'everybody';
    public static $PRIVACY_ONLY_FOR_ME = 'only_for_me';
    public static $PRIVACY_FRIENDS_ONLY = 'friends_only';
    public function forward()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }
        $errorMessage = OW::getLanguage()->text('frmnewsfeedplus','error_in_forward_progress');
        if (!isset($_POST['actionId']) || !isset($_POST['sendIdList']) || (!isset($_POST['sourceId']) && !isset($_POST['feedType'])) || !isset($_POST['forwardType'])) {
            throw new Redirect404Exception();
        }
        $forwardType=$_POST['forwardType'];
        $sourceGroupId=null;
        $actionId=$_POST['actionId'];
        $sourceId=null;
        if(isset($_POST['sourceId']) && !empty($_POST['sourceId'])) {
            $sourceId = $_POST['sourceId'];
        }
        $selectedIds=json_decode($_POST['sendIdList']);
        if (isset($_POST['privacy'])) {
            $privacy = $_POST['privacy'];
        }
        if($forwardType=='groups') {
            $privacy = self::$PRIVACY_EVERYBODY;
        }

        if (isset($_POST['visibility'])) {
            $visibility = $_POST['visibility'];
        }
        $feedType=$_POST['feedType'];
        $this->checkUserIsValidToForward($actionId, $sourceId, $selectedIds, $feedType,$forwardType);
        FRMSecurityProvider::forwardPost($actionId,$sourceId,$selectedIds,$privacy,$visibility,$feedType,$forwardType);
        $respondArray['messageType'] = 'info';
        $respondArray['message'] = OW::getLanguage()->text('frmnewsfeedplus', 'groups_invite_success_message', array('count' => count($selectedIds)));
        exit(json_encode($respondArray));
    }

    public function checkUserIsValidToForward($actionId,$sourceId,$selectedIds,$feedType,$forwardType)
    {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            throw new Redirect404Exception();
        }
        if($forwardType=='groups') {
            if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
                throw new Redirect404Exception();
            }
        }
        if($feedType=='groups') {
            /*
             * check if user has access to source group
             */
            $sourceGroup = GROUPS_BOL_Service::getInstance()->findGroupById($sourceId);
            if (!isset($sourceGroup)) {
                throw new Redirect404Exception();
            }
            $isCurrentUserCanViewSourceGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($sourceGroup);
            if (!$isCurrentUserCanViewSourceGroup) {
                throw new Redirect404Exception();
            }
            /*
             * check if destination users allow current user to write on their walls.
             */
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                if ($forwardType == 'user') {
                    foreach ($selectedIds as $selectedUserId) {
                        $whoCanPostPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getActionValueOfPrivacy('who_post_on_newsfeed', $selectedUserId);
                        if ($whoCanPostPrivacy == 'only_for_me') {
                            throw new Redirect404Exception();
                        }
                    }
                }
            }

            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_FEED_ITEM_RENDERER, array('actionId' => $actionId, 'feedId' => $sourceId)));
        }


        if($forwardType=='user') {
            $disableNewsfeedFromUserProfile = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
            if (isset($disableNewsfeedFromUserProfile) && $disableNewsfeedFromUserProfile == "on")
            {
                throw new Redirect404Exception();
            }
        }
        /* check if user has access to selected group(s) */
        if($forwardType=='groups') {
            foreach ($selectedIds as $selectedGroupId) {
                $selectedGroup = GROUPS_BOL_Service::getInstance()->findGroupById($selectedGroupId);
                if (!isset($selectedGroup)) {
                    throw new Redirect404Exception();
                }
                $isCurrentUserCanViewSelectedGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($selectedGroup);
                if (!$isCurrentUserCanViewSelectedGroup) {
                    throw new Redirect404Exception();
                }
                else{
                    $event = OW::getEventManager()->trigger(new OW_Event('frmgroupsplus.on.channel.add.widget',array('groupId' => $selectedGroupId)));
                    if(isset($event->getData()['channelParticipant']) && $event->getData()['channelParticipant']==true) {
                        throw new Redirect404Exception();
                    }
                }
            }
        }
        if($feedType=='user') {
            $activity=FRMNEWSFEEDPLUS_BOL_Service::getInstance()->getCreatorActivityOfActionById($actionId);
            /*
             * check if current user is owner of the activity
             */
            if ($activity->userId == OW::getUser()->getId()) {
                return true;
            }
            /*
             * check if current user has access to this activity
             */
            $activityOwnerId = $activity->userId;
            $activityPrivacy = $activity->privacy;

            /*
             * activity is private
             */
            if ($activity->userId != OW::getUser()->getId())
            {
                switch ( $activityPrivacy)
                {
                    case 'only_for_me' :
                        throw new Redirect404Exception();
                        break;
                    case 'everybody' :
                        /*
                         * all users have access to a general status
                         */
                        return true;
                        break;
                    case 'friends_only' :
                        /*
                         * check if current user is a friend of owner of the activity
                         */
                        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
                            throw new Redirect404Exception();
                        }
                        $service = FRIENDS_BOL_Service::getInstance();
                        $isFriends = $service->findFriendship(OW::getUser()->getId(), $activityOwnerId);
                        if (isset($isFriends) && $isFriends->status == 'active') {
                            return true;
                        }else {
                            throw new Redirect404Exception();
                        }
                        break;
                    default:
                        throw new Redirect404Exception();
                }
            }
        }
    }
}
