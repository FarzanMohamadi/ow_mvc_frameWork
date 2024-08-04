<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 10/29/2017
 * Time: 11:00 AM
 */
class FRMINVITE_BOL_InvitationLimitationDao extends OW_BaseDao
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frminvite_limit';
    }

    public function getDtoClassName()
    {
        return "FRMINVITE_BOL_InvitationLimitation";
    }

    /**
     * @param integer $userId
     * @return FRMINVITE_BOL_InvitationLimitation
     */
    public function findByUserId($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId',$userId);
        return $this->findObjectByExample($example);
    }



}