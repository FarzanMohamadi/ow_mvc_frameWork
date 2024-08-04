<?php
/**
 * Data Access Object for `frmgroupsinvitationlink_link_user` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.bol
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_BOL_LinkUserDao extends OW_BaseDao
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
     * @var FRMGROUPSINVITATIONLINK_BOL_LinkUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSINVITATIONLINK_BOL_LinkUserDao
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
        return 'FRMGROUPSINVITATIONLINK_BOL_LinkUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsinvitationlink_link_user';
    }

    public function registerUserInGroupLink($linkId, $groupId)
    {

        $link_user = new FRMGROUPSINVITATIONLINK_BOL_LinkUser();
        $link_user->setGroupId($groupId);
        $link_user->setUserId(OW::getUser()->getId());
        $link_user->setLinkId($linkId);
        $link_user->setIsJoined(0);
        $link_user->visitDate = time();

        $this->save($link_user);
    }

    public function findCountByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('isJoined', 1);
        return $this->countByExample($example);
    }

    public function findCountByLinkId($linkId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('linkId', $linkId);
        $example->andFieldEqual('isJoined', 1);
        return $this->countByExample($example);
    }

    public function findUserListByGroupId($groupId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('isJoined', 1);
        $example->setLimitClause(($first-1)*$count, $count);

        return $this->findListByExample($example);
    }

    public function findUserListByLinkId($linkId, $first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('linkId', $linkId);
        $example->andFieldEqual('isJoined', 1);
        $example->setLimitClause(($first-1)*$count, $count);

        return $this->findListByExample($example);
    }

    public function getUserLastLink($groupId, $userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('isJoined', 0);
        $example->setOrder("visitDate desc");
        return $this->findObjectByExample($example);
    }

    public function joinUserInGroupLink($id)
    {
        $linkUser = $this->findById($id);

        $linkUser->isJoined = 1;
        $linkUser->joinDate = time();

        $this->save($linkUser);
    }

    public function getUserJoinedLink($groupId, $userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('isJoined', 1);
        $example->setOrder("visitDate desc");
        return $this->findObjectByExample($example);
    }

    public function deleteUserInGroupLink($groupId, $userId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('userId', $userId);

        $linkUser = $this->findObjectByExample($example);

        if($linkUser != null){
            $linkUser->isJoined = 0;
            $linkUser->leaveDate = time();
            $this->save($linkUser);
        }
    }

    public function findUserList( $first, $count )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual();
        $ex->setLimitClause($first, $count);

        return $this->findListByExample($ex);

    }

}