<?php

class FRMGROUPSRSS_BOL_GroupRssDao extends OW_BaseDao
{
    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * Singleton instance.
     *
     * @var FRMGROUPSRSS_BOL_GroupRss
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSRSS_BOL_GroupRssDao
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function getDtoClassName()
    {
        return 'FRMGROUPSRSS_BOL_GroupRss';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsrss_group_rss';
    }
    /***
     * @return array FRMGROUPSRSS_BOL_GroupRss
     */
    public function findLinksByGroupId($groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        return $this->findListByExample($ex);
    }

    public function saveGroupRssLinks(array $rssLinks, $groupId, $creatorId)
    {
        foreach ($rssLinks as $rssLink) {
            if (!empty($rssLink)) {
                $this->saveGroupRssLink($rssLink, $groupId, $creatorId);
            }
        }
    }

    public function saveGroupRssLink($rssLink, $groupId, $creatorId)
    {
        $groupsRss = new FRMGROUPSRSS_BOL_GroupRss();
        $groupsRss->groupId = $groupId;
        $groupsRss->rssLink = $rssLink;
        $groupsRss->creatorId = $creatorId;
        $this->save($groupsRss);
    }

    public function linkExistsForGroup($rssLink, $groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('groupId',$groupId);
        $ex->andFieldEqual('rssLink',$rssLink);
        return $this->countByExample($ex) == 1;
    }

    public function getGroupRssForInterval($count)
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }
        $ex = new OW_Example();
        $ex->setOrder('lastUpdateDate ASC');
        $ex->setLimitClause(0, $count);
        return $this->findListByExample($ex);
    }

    public function updateLastFeedDate($id, $date)
    {
        $groupsRss = $this->findById($id);
        $groupsRss->lastRssFeedDate = $date;
        $this->save($groupsRss);
    }

    public function updateLastUpdateDate($id, $date)
    {
        $groupsRss = $this->findById($id);
        $groupsRss->lastUpdateDate = $date;
        $this->save($groupsRss);
    }

    public function removeDeletedGroupsRssLink()
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }
        $rssTable = $this->getTableName();
        $groupTable = GROUPS_BOL_GroupDao::getInstance()->getTableName();

        $query = "DELETE FROM ". $rssTable ." WHERE ".$rssTable.".groupId NOT IN(SELECT id FROM ".$groupTable.")";

        OW::getDbo()->query($query);

    }

    public function removeRssForDeletedGroup($groupId)
    {
        $rssTable = $this->getTableName();
        $query = "DELETE FROM ". $rssTable ." WHERE ".$rssTable.".groupId =:groupId";
        OW::getDbo()->query($query,array('groupId' => $groupId));

    }
}
