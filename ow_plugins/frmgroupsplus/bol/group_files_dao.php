<?php
/**
 * Data Access Object for `frmgroupsplus_group_managers` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus.bol
 * @since 1.0
 */
class FRMGROUPSPLUS_BOL_GroupFilesDao extends OW_BaseDao
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
     * @var FRMGROUPSPLUS_BOL_GroupFilesDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMGROUPSPLUS_BOL_GroupFilesDao
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
        return 'FRMGROUPSPLUS_BOL_GroupFiles';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmgroupsplus_group_files';
    }

    public function getGroupFilesByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->findListByExample($example);
    }

    public function deleteGroupFilesByAidAndGid($groupId, $attachmentId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('attachmentId', $attachmentId);
        $example->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($example);
    }
    public function findFileIdByAidAndGid($groupId, $attachmentId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('attachmentId', $attachmentId);
        $example->andFieldEqual('groupId', $groupId);
        return $this->findIdByExample($example);
    }
    public function deleteGroupFilesByGroupId($groupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        return $this->deleteByExample($example);
    }
    public function addFileForGroup($groupId, $attachmentId)
    {
        $groupFiles = new FRMGROUPSPLUS_BOL_GroupFiles();
        $groupFiles->setGroupId($groupId);
        $groupFiles->setAttachmentId($attachmentId);
        $this->save($groupFiles);

        OW::getEventManager()->trigger(new OW_Event('groups.group.content.update', array('action' => 'add_file', 'groupId' => $groupId, 'attachmentId' => $attachmentId)));

        return $groupFiles->getId();
    }

    public function findFileListByGroupId( $groupId, $first, $count )
    {
        $first = (int) $first;
        $count = (int) $count;
        $query = "SELECT u.* FROM " . $this->getTableName() . " u WHERE u.groupId=:g ORDER BY u.id DESC LIMIT :lf, :lc";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "g" => $groupId,
            "lf" => $first,
            "lc" => $count
        ));
    }

    public function findAttachmentIdListByGroupId( $groupId, $first, $count )
    {
        $first = (int) $first;
        $count = (int) $count;
        $query = "SELECT u.attachmentId FROM " . $this->getTableName() . " u WHERE u.groupId=:g ORDER BY u.id DESC LIMIT :lf, :lc";

        return $this->dbo->queryForColumnList($query, array(
            "g" => $groupId,
            "lf" => $first,
            "lc" => $count
        ));
    }

    /**
     * @param $groupId
     * @param null $searchTitle
     * @return mixed
     */
    public function findCountByGroupId( $groupId,$searchTitle=null )
    {
        $params= array(
            "g" => $groupId
        );
        $joinClause=" ";
        $whereJoinClause=" ";
        if(isset($searchTitle))
        {
            $joinClause = " INNER JOIN " . OW_DB_PREFIX . "base_attachment ba ON u.attachmentId=ba.id ";
            $whereJoinClause = " AND ba.origFileName LIKE :title ";
            $params["title"] = '%' . $searchTitle . '%';
        }
        $query = "SELECT COUNT(*) FROM " . $this->getTableName() . " u ".$joinClause." WHERE u.groupId=:g ".$whereJoinClause;

        return $this->dbo->queryForColumn($query, $params);
    }

    public function findAllFiles()
    {
        $query = "SELECT * FROM " . $this->getTableName();

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
    public function findFileByFiltering($first, $count, $searchValue, $userId)
    {

        if ($first < 0) {
            $first = 0;
        }

        if ($count < 0) {
            $count = 1;
        }


        $partialJoin = "INNER JOIN (SELECT groupId, attachmentId FROM " . $this->getTableName() . ") AS  FILE ";

        $userGroup ="";
        if($userId != null) {
            $userGroup = "UNION SELECT FILE.attachmentId FROM(
(SELECT groupId FROM " . OW_DB_PREFIX . "groups_group_user WHERE userId=".$userId.") AS USER " . $partialJoin . " 
ON USER.groupId = FILE.groupId)";
        }
        $query = "SELECT * FROM (SELECT FILE.attachmentId FROM(
(SELECT id FROM " . OW_DB_PREFIX . "groups_group WHERE whoCanView='anyone' AND STATUS = 'active') AS publicGroups " . $partialJoin . " 
ON publicGroups.id = FILE.groupId)".$userGroup.") AS groupFile

INNER JOIN (SELECT * FROM " . OW_DB_PREFIX . "base_attachment WHERE origFileName LIKE :sv) AS attach 
ON groupFile.attachmentId = attach.id 
ORDER BY attach.addStamp DESC LIMIT :f,:c";


        return $this->dbo->queryForList($query, array('f' => $first, 'c' => $count, 'sv' => '%' . $searchValue . '%'));

    }
}