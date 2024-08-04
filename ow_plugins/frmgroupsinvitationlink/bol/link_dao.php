<?php
/**
 * Data Access Object for `frmgroupsinvitationlink_link` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.bol
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_BOL_LinkDao extends OW_BaseDao
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
     * @var FRMGROUPSINVITATIONLINK_BOL_LinkDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSINVITATIONLINK_BOL_LinkDao
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
        return 'FRMGROUPSINVITATIONLINK_BOL_Link';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsinvitationlink_link';
    }

    public function checkUniqueness($hash)
    {
        $example = new OW_Example();
        $example->andFieldEqual('hashLink', $hash);
        $result = $this->findObjectByExample($example);
        if ($result == null) {
            return false;
        }
        return true;
    }

    public function deactivate($linkId)
    {
        $link = $this->findById($linkId);

        $link->isActive = 0;

        $this->save($link);
    }

    public function deactivateGroupLinks($groupId)
    {
        $sql = "UPDATE `" . $this->getTableName() . "` SET `isActive` = 0
            WHERE `groupId` = :groupId";

        $this->dbo->query($sql, array('groupId' => $groupId));
    }

    public function findGroupLinks($groupId, $page = 1, $limit = 10)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->setOrder('createDate desc');
        $example->setLimitClause(($page-1)*$limit, $limit);

        return $this->findListByExample($example);
    }

    public function findGroupLatestLink($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('isActive', 1);
        $example->setOrder("id desc");
        return $this->findObjectByExample($example);
    }

    public function findGroupByHashLink($code)
    {
        $example = new OW_Example();
        $example->andFieldEqual('hashLink', $code);
        $example->andFieldEqual('isActive', 1);
        return $this->findObjectByExample($example);
    }


    public function findLinkByHash($code)
    {
        $example = new OW_Example();
        $example->andFieldEqual('hashLink', $code);
        return $this->findObjectByExample($example);
    }

}