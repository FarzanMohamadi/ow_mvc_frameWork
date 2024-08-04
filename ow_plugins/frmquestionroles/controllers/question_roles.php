<?php
/**
 * frmquestionroles
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

class FRMQUESTIONROLES_CTRL_QuestionRoles extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }

    /***
     * @param null $params
     * @throws Redirect404Exception
     */
    public function disapprovedUsers($params = null) {
        if (!OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        }
        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
        $rpp = (int) OW::getConfig()->getValue('base', 'users_count_on_page');

        $first = ($page - 1) * $rpp;
        $count = $rpp;

        $service = FRMQUESTIONROLES_BOL_Service::getInstance();
        $usersInfo = $service->getDisApprovedUsers($first, $count);
        if ($usersInfo['valid'] == false) {
            throw new Redirect404Exception();
        }

        $idList = $usersInfo['users'];

        $this->setDocumentKey('disapproved_users ');

        $this->setPageHeading(OW::getLanguage()->text('frmquestionroles', 'users_disapproved'));
        $this->setPageTitle(OW::getLanguage()->text('frmquestionroles', 'users_disapproved'));
        $this->setPageHeadingIconClass('ow_ic_user');

        $itemCount = $usersInfo['allSize'];
        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($itemCount / $rpp), 5));

        $questionList = array();
        $avatarArr = array();
        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView ) {
            $qs[] = 'birthdate';
        }

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView ) {
            $qs[] = 'sex';
        }

        if ( !empty($idList) )
        {
            $avatarArr = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, true, true, true, true);
            $questionList = BOL_QuestionService::getInstance()->getQuestionData($idList, $qs);
        }

        $list = BOL_UserService::getInstance()->findUserListByIdList($idList);
        $userList = array();
        foreach ( $list as $dto ) {
            $userList[] = array(
                'dto' => $dto
            );
        }
        $userFlatList = [];
        foreach ($userList as $userL){
            foreach($userL as $k => $v){
                $userFlatList[] = $v;
            }
        }

        $friendshipStatusEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::USER_LIST_FRIENDSHIP_STATUS, array('list' =>    $userFlatList,'desktopVersion'=>true)));
        if(isset($friendshipStatusEvent->getData()['answerValues']) && sizeof($friendshipStatusEvent->getData()['answerValues'])>0){
            $this->assign('answerValues', $friendshipStatusEvent->getData()['answerValues']);
            $this->assign('questionNameList', $friendshipStatusEvent->getData()['questionNameList']);
            $this->assign('questionNameValues', $friendshipStatusEvent->getData()['questionNameValues']);
        }
        $this->assign('questionList', $questionList);
        $this->assign('avatars', $avatarArr);
        $this->assign('list', $userList);
    }

}
