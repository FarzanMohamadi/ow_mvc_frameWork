<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class MARKET_BOL_StoreDao extends OW_BaseDao
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
        return 'MARKET_BOL_Store';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'market_data';
    }

    public function getUserStoreNameById($userId){
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        return $this->findObjectByExample($example);
    }

    public function removeUserStoreNameById($userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }
}
