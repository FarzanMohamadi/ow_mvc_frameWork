<?php
/**
 * frmgroupsinvitationlink
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.controllers
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_CTRL_Link extends OW_ActionController
{
    /**
     *
     * @var FRMGROUPSINVITATIONLINK_BOL_Service
     */
    private $service;
    private $linkDao;

    public function __construct()
    {
        $this->service = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance();
        $this->linkDao = FRMGROUPSINVITATIONLINK_BOL_LinkDao::getInstance();
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgroupsinvitationlink')->getStaticJsUrl().'groups_invitation_link.js');

    }

    public function addLink(){

        if(!OW::getRequest()->isAjax() || !isset($_POST['id']) || !OW::getUser()->isAuthenticated()){
            throw new Redirect404Exception();
        }

        $groupId = $_POST['id'];
        if(!$this->service->isCurrentUserCanAddLink($groupId)){
            throw new Redirect404Exception();
        }

        $newLink = $this->service->generateLink($groupId);

        if($newLink){
            $hashLink = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code'=>$newLink->hashLink));
            $resultText = OW::getLanguage()->text('frmgroupsinvitationlink', 'link_generation_success_message');
            exit(json_encode(array("result" => true, "message" => $resultText, "link" => $hashLink)));
        } else{
            $resultText = OW::getLanguage()->text('frmgroupsinvitationlink', 'link_generation_failure_message');
            exit(json_encode(array("result" => false, "message" => $resultText)));
        }

    }

    public function joinLink($params){
        if(!isset($params) || !isset($params['code'])){
            throw new Redirect404Exception();
        }
        $group = $this->service->findGroupByInvitationLink($params['code']);

        if(!isset($group)) {
            throw new Redirect404Exception();
        }
        $groupId = $group->getId();
        $this->service->registerUserInGroupLink($params['code']);
        $this->redirect(OW::getRouter()->urlForRoute('groups-view',array('groupId' => $groupId)));
    }

    public function links($params)
    {

        if(!isset($params) || !isset($params['id'])){
            throw new Redirect404Exception();
        }
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            throw new Redirect404Exception();
        }

        $groupId = $params['id'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($groupDto)){
            throw new Redirect404Exception();
        }

        $page = 1;
        if(isset($_GET['page'])){
            $page = $_GET['page'];
        }
        $limit = FRMGROUPSINVITATIONLINK_BOL_Service::TABLE_PAGE_LIMIT;

        $showAllUsersUrl = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.link-joins-without-link-id',array('groupId'=>$groupId));
        $links = $this->service->getGroupLinks($groupId, $page, $limit);

        if($links == null){
            OW::getFeedback()->error(OW::getLanguage()->text('frmgroupsinvitationlink', 'no_link'));
        }

        $generateText = OW::getLanguage()->text('frmgroupsinvitationlink', 'add_link');
        $confirmText = OW::getLanguage()->text('frmgroupsinvitationlink', 'add_link_confirm');
        $addLinkUrl = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.add-link', array('id' => $groupId ));

        $this->setPageTitle(OW::getLanguage()->text('frmgroupsinvitationlink', 'links_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmgroupsinvitationlink', 'links_page_heading'));

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($this->service->countGroupLinks($groupId) / $limit), 5));
        $this->assign('links', $links);
        $this->assign('showAllUsersUrl', $showAllUsersUrl);
        $this->assign('groupName', $groupDto->title);
        $this->assign('groupUrl', OW::getRouter()->urlForRoute('groups-view',array('groupId' => $groupDto->getId())));
        $this->assign('addLinkUrl', $addLinkUrl);
        $this->assign('confirmText', $confirmText);
        $this->assign('generateText', $generateText);
        $this->assign("groupId", $groupId);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgroupsinvitationlink')->getStaticJsUrl().'groups_invitation_link.js');

        $groupInfoComponent = OW::getClassInstance("GROUPS_CMP_BriefInfo", $groupId);
        $this->addComponent('groupInfoComponent', $groupInfoComponent);

    }

    public function deactivate($params)
    {
        if(!isset($params) || !isset($params['linkId'])){
            throw new Redirect404Exception();
        }

        $link = $this->linkDao->findById($params['linkId']);
        $result = $this->service->deactivateLink($link);

        if(!$result){
            throw new Redirect404Exception();
        }

        $this->redirect(OW::getRouter()->urlForRoute('frmgroupsinvitationlink.group-links',array('id' => $link->getGroupId())));
    }

    public function linkJoins( $params )
    {
        if(!isset($params['groupId'])){
            throw new Redirect404Exception();
        }
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            throw new Redirect404Exception();
        }

        $groupId = $params['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if(!isset($groupDto)){
            throw new Redirect404Exception();
        }


        if(isset($params['linkId'])){
            $linkId = $params['linkId'];
            $link = $this->linkDao->findById($linkId);
            $usersCount = $this->service->findUsersListCountByLinkId($linkId);
            $this->setPageTitle(OW::getLanguage()->text('frmgroupsinvitationlink', 'link_user_list_page_title'));
            $this->setPageHeading(OW::getLanguage()->text('frmgroupsinvitationlink', 'link_user_list_page_heading'));
            $hashBrief = $this->service->linkBriefer(OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code' => $link->hashLink)));
            $hashBriefText = OW::getLanguage()->text('frmgroupsinvitationlink', 'related_link', array('link' => $hashBrief));
        } else{
            $linkId = null;
            $usersCount = $this->service->findUsersListJoinedByLinkCountByGroupId($groupId);
            $this->setPageTitle(OW::getLanguage()->text('frmgroupsinvitationlink', 'all_links_user_list_page_title'));
            $this->setPageHeading(OW::getLanguage()->text('frmgroupsinvitationlink', 'all_links_user_list_page_heading'));
            $hashBriefText = '';
        }

        $usersPerPage = FRMGROUPSINVITATIONLINK_BOL_Service::USERS_PAGE_LIMIT;
        $listType = empty($params['list']) ? 'latest' : strtolower(trim($params['list']));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;
        list($list, $itemCount) = $this->service->getDataForUsersList($groupId, $linkId, $page, $usersPerPage);

        $userListComponent = OW::getClassInstance("FRMGROUPSINVITATIONLINK_Members", $list, $itemCount, $usersPerPage, true, $listType);
        $this->addComponent('userListComponent', $userListComponent);

        $groupInfoComponent = OW::getClassInstance("GROUPS_CMP_BriefInfo", $groupId);
        $this->addComponent('groupInfoComponent', $groupInfoComponent);

        $usersCountString = OW::getLanguage()->text('frmgroupsinvitationlink', 'number_of_users', array("number"=>$usersCount));


        $this->assign('usersCountString', $usersCountString);
        $this->assign('listType', $listType);
        $this->assign('groupName', $groupDto->title);
        $this->assign('groupUrl', OW::getRouter()->urlForRoute('groups-view',array('groupId' => $groupDto->getId())));
        $this->assign('backUrl', OW::getRouter()->urlForRoute('frmgroupsinvitationlink.group-links',array('id' => $groupId)));
        $this->assign('backLabel', OW::getLanguage()->text('frmgroupsinvitationlink', 'back_button'));
        $this->assign('hashBriefText', $hashBriefText);

    }

}

class FRMGROUPSINVITATIONLINK_Members extends BASE_CMP_Users
{
    private $listKey;

    public function __construct( $list, $itemCount, $usersOnPage, $showOnline, $listKey )
    {
        $this->listKey = $listKey;

        parent::__construct($list, $itemCount, $usersOnPage, $showOnline);
    }

    public function getFields( $userIdList )
    {
        return array();
    }
}