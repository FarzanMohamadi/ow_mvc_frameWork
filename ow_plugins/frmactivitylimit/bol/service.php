<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

class FRMACTIVITYLIMIT_BOL_Service
{
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $userRequestsDao;
    
    private function __construct()
    {
        $this->userRequestsDao = FRMACTIVITYLIMIT_BOL_UserRequestsDao::getInstance();
    }

    public function increaseCountDBForCurrentUser()
    {
        $userId = OW::getUser()->getId();

        /* @var  $item FRMACTIVITYLIMIT_BOL_UserRequests*/
        $item = $this->userRequestsDao->findById($userId);

        if($item->isLocked()){
            return;
        }

        $item = $this->userRequestsDao->increaseCountDB($item);

        // check to lock
        $max_requests = OW::getConfig()->getValue('frmactivitylimit', 'max_db_requests');
        if($item->db_count > $max_requests){
            $this->userRequestsDao->lock($item);
            return;
        }

        // check to renew timestamp
        $minutes_to_reset = OW::getConfig()->getValue('frmactivitylimit', 'minutes_to_reset');
        if(time() > $item->getLastResetTimestamp() + ($minutes_to_reset * 60) ){
            $this->userRequestsDao->reset($item);
            return;
        }
    }

    public function isLocked()
    {
        $userId = OW::getUser()->getId();

        // check other conditions after increase
        /* @var  $item FRMACTIVITYLIMIT_BOL_UserRequests*/
        $item = $this->userRequestsDao->findById($userId);

        if(isset($item) && $item->isLocked()){
            // check to unlock
            $blocking_minutes = OW::getConfig()->getValue('frmactivitylimit', 'blocking_minutes');
            if(time() > $item->getLastResetTimestamp() + ($blocking_minutes * 60) ){
                $this->userRequestsDao->reset($item);
                return false;
            }
            return true;
        }

        return false;
    }
}
