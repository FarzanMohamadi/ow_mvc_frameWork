<?php
class FRMMUTUAL_CTRL_Mutuals extends OW_ActionController
{

    public function index($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('frmmutual', 'main_menu_item'));
        $this->setPageHeading(OW::getLanguage()->text('frmmutual', 'main_menu_item'));

        if(!isset($params['userId'])){
            OW::getApplication()->redirect(OW_URL_HOME);
        }
        $profileOwnerId = (int) $params['userId'];
        $currentUserId = OW::getUser()->getId();

        if($currentUserId == $profileOwnerId){
            OW::getApplication()->redirect(OW_URL_HOME);
        }

        $profileOwnerFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $profileOwnerId));
        $currentUserFriendsId = OW::getEventManager()->call('plugin.friends.get_friend_list', array('userId' => $currentUserId));

        $mutualFriensdId = array();
        foreach($profileOwnerFriendsId as $profileOwnerFriendId){
            if(in_array($profileOwnerFriendId,$currentUserFriendsId)){
                $mutualFriensdId[] = $profileOwnerFriendId;
            }
        }

        if(sizeof($mutualFriensdId)==0){
            $this->assign('empty_list',true);
        }else{
            $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];
            $perPage = 21; // count of users show in each page
            $count = $perPage;
            $SlicedmutualFriensdId = array_slice($mutualFriensdId,$count*($page-1),$count);
            $userDtoList = BOL_UserService::getInstance()->findUserListByIdList($SlicedmutualFriensdId);
            $this->addComponent('userList', new FRMMUTUAL_CMP_MutualsUserList($userDtoList, sizeof($mutualFriensdId), $perPage, true));
        }
    }

}

class FRMMUTUAL_CMP_MutualsUserList extends BASE_CMP_Users
{

    public function getFields( $userIdList )
    {
        $fields = array();

        $qs = array();

        $qBdate = BOL_QuestionService::getInstance()->findQuestionByName('birthdate');

        if ( $qBdate->onView )
            $qs[] = 'birthdate';

        $qSex = BOL_QuestionService::getInstance()->findQuestionByName('sex');

        if ( $qSex->onView )
            $qs[] = 'sex';

        $questionList = BOL_QuestionService::getInstance()->getQuestionData($userIdList, $qs);

        foreach ( $questionList as $uid => $q )
        {

            $fields[$uid] = array();

            $age = '';

            if ( !empty($q['birthdate']) )
            {
                $date = UTIL_DateTime::parseDate($q['birthdate'], UTIL_DateTime::MYSQL_DATETIME_DATE_FORMAT);

                $age = UTIL_DateTime::getAge($date['year'], $date['month'], $date['day']);
            }

            if ( !empty($q['sex']) )
            {
                $fields[$uid][] = array(
                    'label' => '',
                    'value' => BOL_QuestionService::getInstance()->getQuestionValueLang('sex', $q['sex']) . ' ' . $age
                );
            }

            if ( !empty($q['birthdate']) )
            {
                $dinfo = date_parse($q['birthdate']);
            }
        }

        return $fields;
    }
}