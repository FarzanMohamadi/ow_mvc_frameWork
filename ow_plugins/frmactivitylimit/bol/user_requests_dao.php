<?php
/**
 * frmactivitylimit
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmactivitylimit
 * @since 1.0
 */

class FRMACTIVITYLIMIT_BOL_UserRequestsDao extends OW_BaseDao
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
        return 'FRMACTIVITYLIMIT_BOL_UserRequests';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmactivitylimit_user_requests';
    }

    /***
     * @param $item FRMACTIVITYLIMIT_BOL_UserRequests
     * @return mixed
     */
    public function increaseCountDB($item){
        $item->db_count++;
        $this->save($item);

        return $item;
    }

    /***
     * @param $item FRMACTIVITYLIMIT_BOL_UserRequests
     */
    public function lock($item){
        $item->setLastResetTimestamp(time());
        $item->setDbCount(-1);
        $this->save($item);
    }

    /***
     * @param $item FRMACTIVITYLIMIT_BOL_UserRequests
     */
    public function reset($item){
        $item->setLastResetTimestamp(time());
        $item->setDbCount(0);
        $this->save($item);
    }

    /**
     * @param $id
     * @param int $cacheLifeTime
     * @param array $tags
     * @return mixed
     */
    public function findById( $id, $cacheLifeTime = 0, $tags = array() )
    {
        $item = parent::findById($id, $cacheLifeTime, $tags);
        if(!isset($item)){
            // insert new row
            $query = '
        INSERT INTO ' . $this->getTableName() . '  (id, userId, last_reset_timestamp,db_count)
        VALUES (:uId, :uId, ' . time() . ', 1)
        ON DUPLICATE KEY UPDATE 
          `db_count`=`db_count`+1';
            $this->dbo->query($query, ['uId'=>$id]);

            $item = parent::findById($id, $cacheLifeTime, $tags);
        }
        return $item;
    }
}
