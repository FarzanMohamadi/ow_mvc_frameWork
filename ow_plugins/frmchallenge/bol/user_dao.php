<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.bol
 * @since 1.0
 */
class FRMCHALLENGE_BOL_UserDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_User';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_user';
    }

    public function addUserPoint($userId, $point){
        $user = null;
        if($userId != null) {
            $user = $this->findByUserId($userId);
            if ($user == null) {
                $user = new FRMCHALLENGE_BOL_User();
                $user->userId = $userId;
            }
            $user->point = $user->point + $point;
        }
        $this->save($user);
        return $user;
    }

    public function findByUserId($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    public function getUsersPoint($count = 10){
        $example = new OW_Example();
        $example->setOrder('point DESC');
        $example->setLimitClause(0, $count);
        return $this->findListByExample($example);
    }
}
