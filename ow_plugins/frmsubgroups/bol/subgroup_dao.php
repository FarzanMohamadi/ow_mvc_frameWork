<?php
class FRMSUBGROUPS_BOL_SubgroupDao extends OW_BaseDao
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
     * @var FRMSUBGROUPS_BOL_SubgroupDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMSUBGROUPS_BOL_SubgroupDao
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
        return 'FRMSUBGROUPS_BOL_Subgroup';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsubgroups_groups';
    }

    /**
     * @param $parentGroupId
     * @param null $first
     * @param null $count
     * @return mixed
     */
    public function findSubGroups($parentGroupId,$first=null,$count=null)
    {
        $params = array('parentGroupId'=>$parentGroupId);
        $limitClause='';
        $orderClause='';
        if(isset($first) && isset($count))
        {
           $limitClause=' LIMIT :first,:count ';
           $orderClause= ' ORDER BY `g`.`id` DESC ';
            $params['first'] = $first;
            $params['count'] = $count;
        }

        $sql = 'SELECT `g`.* FROM '. GROUPS_BOL_GroupDao::getInstance()->getTableName() . ' AS `g` INNER JOIN ' . $this->getTableName() . ' as `sg` ON `g`.`id` = `sg`.`subGroupId` AND 
        `sg`.`parentGroupId`= :parentGroupId'. $orderClause. $limitClause;

        return $this->dbo->queryForObjectList($sql, GROUPS_BOL_GroupDao::getInstance()->getDtoClassName(),$params);
    }

    /**
     * @param $parentGroupId
     * @return array
     */
    public function findSubGroupListCount($parentGroupId)
    {
        $params = array('parentGroupId'=>$parentGroupId);


        $sql = 'SELECT COUNT(*) FROM '. GROUPS_BOL_GroupDao::getInstance()->getTableName() . ' AS `g` INNER JOIN ' . $this->getTableName() . ' as `sg` ON `g`.`id` = `sg`.`subGroupId` AND 
        `sg`.`parentGroupId`= :parentGroupId';

        return $this->dbo->query($sql,$params);
    }


    /**
     * @param $subGroupId
     * @return FRMSUBGROUPS_BOL_Subgroup
     */
    public function findSubGroupDto($subGroupId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('subGroupId',$subGroupId);
        return $this->findObjectByExample($example);
    }


    /**
     * @param $parentGroupId
     * @param null $title
     * @param bool $isModerator
     * @param int $first
     * @param int $count
     * @return array|void
     */
    public function findSubGROUPSByParentGroup($parentGroupId,$title=null,$isModerator=false,$first=0,$count=20)
    {
        if(!isset($parentGroupId))
        {
            return;
        }

        $params['parentGroupId']=$parentGroupId;
        $params['s']='active';
        $whereClause= ' ';
        if(isset($title))
        {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
            $params['searchTitle']= '%'. $title . '%';
        }


        if(!$isModerator)
        {
            $whereClause .= ' AND u.userId=:userId OR sb.subGroupId IN (SELECT g2.id FROM  ' . OW_DB_PREFIX . 'groups_group  g2
            INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u2 ON g2.id = u2.groupId AND g2.whoCanView="anyone") ';
            $params['userId']=OW::getUser()->getId();
        }
        $limitClause='';
        if(isset($first) && isset($count))
        {
            $limitClause=' LIMIT :first,:count ';
            $params['first'] = $first;
            $params['count'] = $count;
        }

        $sql = '
                SELECT DISTINCT g.* FROM ' . OW_DB_PREFIX . 'groups_group g
                INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId 
                INNER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON `g`.`id`=`sb`.`subGroupId` AND `sb`.`parentGroupId`=:parentGroupId
                WHERE  g.status=:s '.$whereClause.' order by g.id DESC '.$limitClause;

        return $this->dbo->queryForObjectList($sql, GROUPS_BOL_GroupDao::getInstance()->getDtoClassName(),$params);
    }


    /**
     * @param $parentGroupId
     * @param null $title
     * @param bool $isModerator
     * @return int|void
     */
    public function findSubGROUPSByParentGroupCount($parentGroupId,$title=null,$isModerator=false)
    {
        if(!isset($parentGroupId))
        {
            return;
        }

        $params['parentGroupId']=$parentGroupId;
        $params['s']='active';
        $whereClause= ' ';
        if(isset($title))
        {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
            $params['searchTitle']= '%'. $title . '%';
        }

        if(!$isModerator)
        {
            $whereClause .= ' AND u.userId=:userId OR sb.subGroupId IN (SELECT g2.id FROM  ' . OW_DB_PREFIX . 'groups_group  g2
            INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u2 ON g2.id = u2.groupId AND g2.whoCanView="anyone") ';
            $params['userId']=OW::getUser()->getId();
        }
        $sql = '
                SELECT COUNT(DISTINCT g.id) FROM ' . OW_DB_PREFIX . 'groups_group g
                INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId 
                INNER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON `g`.`id`=`sb`.`subGroupId` AND `sb`.`parentGroupId`=:parentGroupId
                WHERE  g.status=:s '.$whereClause.' order by g.id DESC ';

        return (int)$this->dbo->queryForColumn($sql, $params);
    }


    /**
     * @param $parentGroupId
     * @return array GROUPS_BOL_GroupDao
     */
    public function findAllSubgroupsDto($parentGroupId)
    {
        $params['parentGroupId']=$parentGroupId;
        $params['s']='active';
        $sql = '
                SELECT DISTINCT g.* FROM ' . OW_DB_PREFIX . 'groups_group g
                INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId 
                INNER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON `g`.`id`=`sb`.`subGroupId` AND `sb`.`parentGroupId`=:parentGroupId
                WHERE  g.status=:s order by g.id DESC ';

        return $this->dbo->queryForObjectList($sql, GROUPS_BOL_GroupDao::getInstance()->getDtoClassName(),$params);
    }

    public function deleteSubgroupDto($subGroupId)
    {
       $example = new OW_Example();
       $example->andFieldEqual('subGroupId',$subGroupId);
       $this->deleteByExample($example);
    }

}