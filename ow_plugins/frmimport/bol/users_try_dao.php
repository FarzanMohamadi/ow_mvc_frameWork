<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmimport.bol
 * @since 1.0
 */
class FRMIMPORT_BOL_UsersTryDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'FRMIMPORT_BOL_UsersTry';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmimport_users_try';
    }

    /***
     * @param $userId
     * @param $type
     * @return FRMIMPORT_BOL_UsersTry
     */
    public function getUserTry($userId, $type)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('type', $type);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $userId
     * @param $type
     * @return array|FRMIMPORT_BOL_UsersTry
     */
    public function addOrUpdateUserTry($userId, $type)
    {
        $user = $this->getUserTry($userId, $type);
        if($user != null) {
            $user->time = time();
            $this->save($user);
        }else{
            $user = new FRMIMPORT_BOL_UsersTry();
            $user->time = time();
            $user->userId = $userId;
            $user->type = $type;
            $this->save($user);
        }
        return $user;
    }
}
