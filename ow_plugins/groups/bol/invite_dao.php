<?php
/**
 * Data Access Object for `groups_invite` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_InviteDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var GROUPS_BOL_InviteDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GROUPS_BOL_InviteDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'GROUPS_BOL_Invite';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'groups_invite';
    }

    /**
     * @param integer $groupId
     * @param integer $userId
     * @return GROUPS_BOL_Invite
     */
    public function findInvite( $groupId, $userId, $inviterId = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('userId', (int) $userId);

        if ( $inviterId !== null )
        {
            $example->andFieldEqual('inviterId', (int) $inviterId);
        }

        return $this->findObjectByExample($example);
    }

    public function findInviteList( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        return $this->findListByExample($example);
    }

    public function findInviteListByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', (int) $userId);

        return $this->findListByExample($example);
    }

    public function findListByGroupIdAndInviterId( $groupId, $inviterId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('inviterId', (int) $inviterId);

        return $this->findListByExample($example);
    }

    /**
     * @param integer $groupId
     * @param integer $userId
     */
    public function deleteByUserIdAndGroupId( $groupId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }

    /**
     * @param integer $userId
     */
    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }


    /**
     * @param integer $groupId
     */
    public function deleteByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        $this->deleteByExample($example);
    }


    /**
     * @param integer $groupId
     */
    public function findListByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', (int) $groupId);

        return $this->findListByExample($example);
    }
}
