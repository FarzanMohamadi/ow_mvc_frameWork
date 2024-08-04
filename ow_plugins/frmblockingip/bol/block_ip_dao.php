<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmblockingip.bol
 * @since 1.0
 */
class FRMBLOCKINGIP_BOL_BlockIpDao extends OW_BaseDao
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
        return 'FRMBLOCKINGIP_BOL_BlockIp';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmblockingip_block_ip';
    }
    
    public function isLocked()
    {
        $user = $this->getCurrentUser();
        if($user!=null){
            if($user->getCount()>OW::getConfig()->getValue('frmblockingip', FRMBLOCKINGIP_BOL_Service::TRY_COUNT_BLOCK)){
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    /**
     * @return FRMBLOCKINGIP_BOL_BlockIp
     */
    public function getCurrentUser()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('ip', FRMBLOCKINGIP_BOL_Service::getInstance()->getCurrentIP());
        $result = $this->findObjectByExample($ex);
        $expireMinutes = (int)OW::getConfig()->getValue('frmblockingip',
            FRMBLOCKINGIP_BOL_Service::EXPIRE_TIME);
        if ($result != null && time() > $result->getTime() + $expireMinutes * 60) {
            $this->deleteBlockCurrentIp();
            return null;
        }
        return $result;
    }

    /**
     * @return FRMBLOCKINGIP_BOL_BlockIp
     */
    public function addBlockIp()
    {
        $user = $this->getCurrentUser();
        if($user!=null){
            $user->setCount($user->getCount()+1);
            $user->setTime(time());
            $this->save($user);
            return $user;
        }else{
            $newBlockIp = new FRMBLOCKINGIP_BOL_BlockIp();
            $newBlockIp->setIp(FRMBLOCKINGIP_BOL_Service::getInstance()->getCurrentIP());
            $newBlockIp->setTime(time());
            $newBlockIp->setCount(1);
            $this->save($newBlockIp);
            return $newBlockIp;
        }
    }
    
    public function deleteBlockIp()
    {
        $expareTime = time() - (int)OW::getConfig()->getValue('frmblockingip', FRMBLOCKINGIP_BOL_Service::EXPIRE_TIME) * 60;

        $ex = new OW_Example();
        $ex->andFieldLessOrEqual('time',$expareTime);
        $this->deleteByExample($ex);
    }

    public function deleteBlockCurrentIp()
    {

        $ex = new OW_Example();
        $ex->andFieldEqual('ip', FRMBLOCKINGIP_BOL_Service::getInstance()->getCurrentIP());
        $this->deleteByExample($ex);
    }

    public function getUserTryCount(){
        $user = $this->getCurrentUser();
        if($user!=null){
            return $user->getCount();
        }
        return 0;
    }

}
