<?php
/**
 * Birthdays Service.
 * 
 * @package ow_plugins.birthdays.bol
 * @since 1.0
 */
final class BIRTHDAYS_BOL_Service
{
    /**
     * @var BIRTHDAYS_BOL_UserDao
     */
    private $birthdaysDao;

    private $birthdaysPrivacyDao;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->birthdaysDao = BIRTHDAYS_BOL_UserDao::getInstance();
        $this->birthdaysPrivacyDao = BIRTHDAYS_BOL_PrivacyDao::getInstance();
    }
    /**
     * Singleton instance.
     *
     * @var BIRTHDAYS_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BIRTHDAYS_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function findListByBirthdayPeriod( $start, $end, $first, $count, $idList = null, $privacy = null )
    {
        return $this->birthdaysDao->findListByBirthdayPeriod($start, $end, $first, $count, $idList, $privacy);
    }

    public function countByBirthdayPeriod( $start, $end, $idList = null, $privacy = null )
    {
        return $this->birthdaysDao->countByBirthdayPeriod($start, $end, $idList, $privacy);
    }

//    public function findListByBirthdayPeriodAndUserIdList( $start, $end, $first, $count, $idList )
//    {
//        return $this->birthdaysDao->findListByBirthdayPeriodAndUserIdList($start, $end, $first, $count, $idList);
//    }

//    public function countByBirthdayPeriodAndUserIdList( $start, $end, $idList )
//    {
//        return $this->birthdaysDao->countByBirthdayPeriodAndUserIdList($start, $end, $idList);
//    }

    /**
     * Checks and raises event on users birthday list.
     */
    public function checkBirthdays()
    {
        $configTs = OW::getConfig()->getValue('birthdays', 'users_birthday_event_ts');

/*        if ( date('j', $configTs) !== date('j', time()) )
        {*/
            $userList = $this->birthdaysDao->findUserListByBirthday(date('Y-m-d'));

            $event = new OW_Event('birthdays.today_birthday_user_list', array('userIdList' => $userList));
            OW::getEventManager()->trigger($event);

            OW::getConfig()->saveConfig('birthdays', 'users_birthday_event_ts', time(), null, false);
/*        }*/
    }

    public function getUserListData( $first, $count )
    {
        //set date bounds for birthdays
        $period = array(
            'start' => date('Y-m-d'),
            'end' => date('Y-m-d', strtotime('+7 day'))
        );

        return array(
            $this->findListByBirthdayPeriod($period['start'], $period['end'], $first, $count, null, array('everybody')), // get users
            $this->countByBirthdayPeriod($period['start'], $period['end'], null, array('everybody')) // count users
        );
    }

    /**
     * @param int $userId
     * @return BIRTHDAYS_BOL_Privacy
     */
    
    public function findBirthdayPrivacyByUserId( $userId )
    {
        return $this->birthdaysPrivacyDao->findByUserId($userId);
    }

    public function deleteBirthdayPrivacyByUserId( $userId )
    {
        $this->birthdaysPrivacyDao->deleteByUserId($userId);
    }

    public function saveBirthdayPrivacy( BIRTHDAYS_BOL_Privacy $dto )
    {
        return $this->birthdaysPrivacyDao->save($dto);
    }

    public function makeLikeNotificaionArray($entityType, $entityId, $likes = null  )
    {
        if ( $likes === null )
        {
            $likes = BIRTHDAYS_BOL_Service::getInstance()->findEntityLikes($entityType, $entityId);
        }

        $this->count = count($likes);

        if ( $this->count == 0 ){
            return null;
        }

        if ($this->count == 1 && $likes[0]->userId == $entityId){
            return null;
        }

        $userIds = array();
        foreach ( $likes as $like )
        {
            $userIds[] = (int) $like->userId;
        }

        if ( $this->count <= 3 )
        {
            $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $urls = BOL_UserService::getInstance()->getUserUrlsForList($userIds);

            $langVars = array();

            foreach( $userIds as $i => $userId )
            {
                $langVars['user' . ($i + 1)] = '<a href="' . $urls[$userId] . '">' . $displayNames[$userId] . '</a>';
            }

            $result = array('key' => 'birthdays+feed_likes_'. $this->count . '_label', 'vars' => $langVars);
        }
        else
        {
            $url = "javascript: OWM.showUsers(" . json_encode($userIds) . ",'".OW::getLanguage()->text('newsfeed','ajax_floatbox_like_users')."')";
            $result = array('key' => 'birthdays+feed_likes_list_label', 'vars' => array('count' => $this->count, 'url' => $url));
        }

        return $result;
    }


    public function findEntityLikes($entityType, $entityId){

        return BOL_VoteDao::getInstance()->findByEntity($entityType, $entityId);
    }
}