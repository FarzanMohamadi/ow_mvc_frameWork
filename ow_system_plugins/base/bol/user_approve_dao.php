<?php
/**
 * Singleton. 'User Approve' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_UserApproveDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_UserApproveDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserApproveDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_UserDisapprove';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_user_disapprove';
    }

    /***
     * @param $userId
     * @return BOL_UserDisapprove
     */
    public function findByUserId( $userId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->findObjectByExample($ex);
    }

    public function deleteByUserId( $userId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);

        return $this->deleteByExample($ex);
    }

    public function findUnapproveStatusForUserList( $idList )
    {
        if (!is_array($idList) || empty($idList)) {
            return array();
        }
        $query = "SELECT `userId` FROM `" . $this->getTableName() . "`
            WHERE `userId` IN (" . $this->dbo->mergeInClause($idList) . ")";

        return $this->dbo->queryForColumnList($query);
    }

    public function getRequestedNotes($userId){
        $dto = $this->findByUserId($userId);
        if(empty($dto) || $dto->changeRequested != 1){
            return null;
        }
        return json_decode($dto->notes, true);
    }

    public function requestForChange($userId, $message){
        $notes = json_encode(['admin_message'=>$message]);

        $query = "UPDATE `" . $this->getTableName() . "`
            SET `changeRequested`=1, `notes`=:notes
            WHERE `userId`=$userId;
        ";
        $this->dbo->query($query, ['notes'=>$notes]);
    }

    public function fixedRequestForChange($userId){
        $query = "UPDATE `" . $this->getTableName() . "`
            SET `changeRequested`=0
            WHERE `userId`=$userId;
        ";
        $this->dbo->query($query);
    }
}