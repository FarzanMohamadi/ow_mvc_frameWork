<?php

class FRMGROUPSINVITATIONLINK_BOL_Service
{

    const GET_INVITATION_LINKS_FOR_GROUP = 'frmgroupsinvitationlink.get.invitation.links.for.group';
    const GO_TO_DEEP_LINK = 'frmgroupsinvitationlink.go.to.deep.link';
    const TABLE_PAGE_LIMIT = 10;
    const USERS_PAGE_LIMIT = 21;

    /**
     * Singleton instance.
     *
     * @var FRMGROUPSINVITATIONLINK_BOL_Service
     */
    private static $classInstance;

    private $linkDao;
    private $linkUserDao;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSINVITATIONLINK_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->linkDao = FRMGROUPSINVITATIONLINK_BOL_LinkDao::getInstance();
        $this->linkUserDao = FRMGROUPSINVITATIONLINK_BOL_LinkUserDao::getInstance();
    }

    public function generateLink($groupId)
    {

        if(!OW::getUser()->isAuthenticated()){
            return;
        }

        $link = new FRMGROUPSINVITATIONLINK_BOL_Link();
        $link->setUserId(OW::getUser()->getId());
        $link->setGroupId($groupId);
        $link->setCreateDate(time());
        $link->setIsActive(1);

        do{
            $randomHash = FRMSecurityProvider::getInstance()->hashSha256Data(rand(10000, 10000000));
        } while($this->linkDao->checkUniqueness($randomHash));
        $hashLinkWithGroupId = $randomHash . '-' . $groupId;
        $link->setHashLink($hashLinkWithGroupId);

        return $this->linkDao->save($link);
    }

    public function getGroupLinks($groupId, $page = 1, $limit = 10)
    {
        if(!$this->isCurrentUserCanAddLink($groupId)){
            return null;
        }
        $links = $this->linkDao->findGroupLinks($groupId, $page, $limit);
        $linksArray = array();

        foreach ($links as $link){
            $statusText = $link->isActive ?
                OW::getLanguage()->text('frmgroupsinvitationlink', 'active_link') :
                OW::getLanguage()->text('frmgroupsinvitationlink', 'deactivated_link');
            $showUsersUrl = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.link-joins',array('groupId' => $groupId, 'linkId'=>$link->id));
            $deactivateUrl = $link->isActive? OW::getRouter()->urlForRoute('frmgroupsinvitationlink.deactivate-link',array('linkId'=>$link->id)) : '';

            $hashedLink = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code' => $link->hashLink));
            $linksArray[] = array(
                'time' => $link->createDate,
                'hash' => $hashedLink,
                'hashBrief' => $this->linkBriefer($hashedLink),
                'isActive' => $link->isActive,
                'isActiveText' => $statusText,
                'user' => array(
                    'name' => BOL_UserService::getInstance()->getDisplayName($link->userId),
                    'url' => BOL_UserService::getInstance()->getUserUrl($link->userId)
                ),
                'showUsersUrl' => $showUsersUrl,
                'deactivateUrl' => $deactivateUrl,
            );
        }

        return $linksArray;
    }

    public function linkBriefer($hashedLink)
    {
        if(!isset($hashedLink) || $hashedLink==''){
            return '';
        }
        $mainSegments = explode('//', $hashedLink);
        $domain = explode('/', $mainSegments[1])[0];
        return $domain . '/...' . substr($hashedLink, -5);
    }

    public function findGroupLatestLink($groupId)
    {
        return $this->linkDao->findGroupLatestLink($groupId);
    }

    public function isCurrentUserCanAddLink($groupId,$groupDto=null)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }
        if(!isset($groupDto)) {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }
        return GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
    }

    public function isCurrentUserCanSeeLink( $groupId,$groupDto=null)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }
        if(!isset($groupDto)) {
            $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        }
        return GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($groupDto);
    }

    public function findGroupByInvitationLink($code)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return null;
        }
        $targetGroupLink = $this->linkDao->findGroupByHashLink($code);
        if(!isset($targetGroupLink)){
            return null;
        }

        $groupId = $targetGroupLink->getGroupId();
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(isset($groupDto) || $this->isCurrentUserCanSeeLink($groupId,$groupDto)){
            return $groupDto;
        }
        return null;
    }

    public function countGroupLinks($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->linkDao->countByExample($example);
    }

    public function deactivateLink($link)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }
        $group = GROUPS_BOL_GroupDao::getInstance()->findById($link->getGroupId());
        if(!$this->isCurrentUserCanAddLink($group->getId(),$group)){
            return false;
        }
        $this->linkDao->deactivate($link->getId());
        return true;
    }

    public function deactivateAllGroupLinks($groupId)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return false;
        }
        if(!$this->isCurrentUserCanAddLink($groupId)){
            return false;
        }
        $this->linkDao->deactivateGroupLinks($groupId);
        return true;
    }

    public function registerUserInGroupLink($code)
    {
        $link = $this->linkDao->findLinkByHash($code);
        if($link == null){
            return;
        }
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return;
        }

        $groupUserDto = GROUPS_BOL_GroupUserDao::getInstance()->findGroupUser($link->getGroupId(), OW::getUser()->getId());
        if($groupUserDto != null){
            return;
        }

        $this->linkUserDao->registerUserInGroupLink($link->getId(), $link->getGroupId());
    }

    public function findUsersListJoinedByLinkCountByGroupId( $groupId )
    {
        return $this->linkUserDao->findCountByGroupId($groupId);
    }

    public function findUsersListCountByLinkId( $linkId )
    {
        return $this->linkUserDao->findCountByLinkId($linkId);
    }

    public function checkIfUserRegisteredByLink(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['groupId']) || !isset($params['userId'])){
            return;
        }
        $groupId = $params['groupId'];
        $userId = $params['userId'];

        $userLastLink = $this->linkUserDao->getUserLastLink($groupId, $userId);
        if($userLastLink != null){
            $this->linkUserDao->joinUserInGroupLink($userLastLink->id);
        }
    }

    public function removeUserJoinByLink(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['groupId']) || !isset($params['userIds'])){
            return;
        }

        $userId = $params['userIds'][0];
        $groupId = $params['groupId'];

        $this->linkUserDao->deleteUserInGroupLink($groupId, $userId);
    }

    public function findUsersListJoinedByLinkByGroupId($groupId, $first = 1, $count = 21)
    {
        $groupUserList = $this->linkUserDao->findUserListByGroupId($groupId, $first, $count);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return BOL_UserService::getInstance()->findUserListByIdList($idList);
    }

    public function findUsersListByLinkId($linkId, $first = 1, $count = 21)
    {
        $groupUserList = $this->linkUserDao->findUserListByLinkId($linkId, $first, $count);
        $idList = array();
        foreach ( $groupUserList as $groupUser )
        {
            $idList[] = $groupUser->userId;
        }

        return BOL_UserService::getInstance()->findUserListByIdList($idList);
    }

    public function getDataForUsersList( $groupId, $linkId, $first, $count )
    {
        if(isset($linkId)) {
            $userList = $this->findUsersListByLinkId($linkId, $first, $count);
            $userCount = $this->findUsersListCountByLinkId($linkId);
        } else{
            $userList = $this->findUsersListJoinedByLinkByGroupId($groupId, $first, $count);
            $userCount = $this->findUsersListJoinedByLinkCountByGroupId($groupId);
        }
        return array(
            $userList,
            (int) $userCount
        );
    }

    /**
     * @param OW_Event $event
     */
    public function getGroupInvitationLinksEvent(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();

        if(!isset($params['groupId']))
        {
            return;
        }

        $count = 10;
        if(!isset($params['count'])){
            $count = (int) $params['count'];
        }


        if(isset($params['first']) && isset($_GET['first'])){
            $page = (((int) $_GET['first']) / $count) + 1;
        }else{
            $page = 1;
        }

        $invitationLinks = $this->linkDao->findGroupLinks($params['groupId'], $page, $count);

        $invitationLinksArray = array();
        foreach ($invitationLinks as $link) {
            $hashLink = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code'=>$link->hashLink));
            $userId = $link->getUserId();
            $userObject = BOL_UserService::getInstance()->findUserById($userId);
            $username = BOL_UserService::getInstance()->getDisplayName($userId);
            $avatar = BOL_AvatarService::getInstance()->getAvatarUrl($userId);
            $user = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->populateUserData($userObject, $avatar, $username);
            $arrayItem = array(
                'id' => $link->getId(),
                'hashLink' => $hashLink,
                'isActive' => $link->getIsActive() == "1" ? true : false,
                'createDate' => (string)$link->getCreateDate(),
                'creatorUser' => $user
            );
            $invitationLinksArray[] = $arrayItem;
        }

        $data['invitationLinks'] = $invitationLinksArray;
        $data['canSeeLinks'] = $this->isCurrentUserCanSeeLink($params['groupId']);
        $data['canAddLinks'] = $this->isCurrentUserCanAddLink($params['groupId']);
        $event->setData($data);
    }

    function isMobile()
    {
        return preg_match("/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|boost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    }

    public function getMobileLinkRedirectForGroup(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();

        if(!isset($params['groupId']))
        {
            return;
        }
        $groupId = $params['groupId'];
        $link = null;
        if($this->isMobile() && OW::getConfig()->configExists('frmgroupsinvitationlink', 'deep_link')){
            $userLastLink = $this->linkUserDao->getUserLastLink($groupId, OW::getUser()->getId());
            if($userLastLink != null){
                $link = OW::getConfig()->getValue('frmgroupsinvitationlink', 'deep_link');
                $link .= '/get_group/' . $groupId;
                $confirmText = OW::getLanguage()->text('frmgroupsinvitationlink', 'are_you_want_to_open_group_in_application');

                $forwardJs =
                    "var answer = $.confirm('" . $confirmText . "');
                    answer.buttons.ok.action = function(){
                        window.location.href = '" . $link . "';
                    }";
                OW::getDocument()->addOnloadScript($forwardJs);
            }
        }

        $data['link'] = $link;
        $event->setData($data);
    }

    public function isUserVisitedGroupLink($groupId, $userId)
    {

        $userLastLink = $this->linkUserDao->getUserLastLink($groupId, $userId);
        if($userLastLink != null){
            return true;
        }
        return false;
    }
}