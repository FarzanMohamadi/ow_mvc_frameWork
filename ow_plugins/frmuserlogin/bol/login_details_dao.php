<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmuserlogin.bol
 * @since 1.0
 */
class FRMUSERLOGIN_BOL_LoginDetailsDao extends OW_BaseDao
{
    CONST IP = 'ip';
    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMUSERLOGIN_BOL_LoginDetails';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmuserlogin_login_details';
    }

    /***
     * @param $userId
     * @param bool $checkAuth
     * @return FRMUSERLOGIN_BOL_LoginDetails|void
     */
    public function addLoginDetails($userId, $checkAuth = true)
    {
        if($checkAuth && !OW::getUser()->isAuthenticated()){
            return;
        }
        $loginDetails = new FRMUSERLOGIN_BOL_LoginDetails();
        $loginDetails->setIp(FRMUSERLOGIN_BOL_Service::getInstance()->getCurrentIP());
        $loginDetails->setTime(time());
        $loginDetails->setBrowser(FRMUSERLOGIN_BOL_Service::getInstance()->getCurrentBrowserInformation());
        $loginDetails->setUserId($userId);
        $this->save($loginDetails);

        $notifyUserByEmail = BOL_PreferenceService::getInstance()->getPreferenceValue('frmuserlogin_login_detail_subscribe',$userId);
        if($notifyUserByEmail!=null && $notifyUserByEmail){
            FRMUSERLOGIN_BOL_Service::getInstance()->sendEmailToUsers($userId);
        }

        return $loginDetails;
    }

    /**
     * @param $userId
     * @param int $countOfRow
     * @return array
     */
    public function getUserLoginDetails($userId, $countOfRow = 5){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->setOrder('`time` DESC');
        if($countOfRow>0){
            $ex->setLimitClause(0, $countOfRow);
        }
        return $this->findListByExample($ex);
    }

    public function deleteLoginDetails()
    {
        $expiredTime = time() - (int)OW::getConfig()->getValue('frmuserlogin', FRMUSERLOGIN_BOL_Service::EXPIRE_TIME) * 30 * 24 * 60 * 60;

        $ex = new OW_Example();
        $ex->andFieldLessOrEqual('time',$expiredTime);
        $this->deleteByExample($ex);
    }

    /***
     * @param $userId
     */
    public function deleteUserLoginDetails($userId){
        $ex = new OW_Example();
        $ex->andFieldEqual('userId',$userId);
        $this->deleteByExample($ex);
    }
}
