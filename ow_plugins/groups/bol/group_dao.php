<?php
/**
 * Data Access Object for `groups_group` table.
 *
 * @package ow_plugins.groups.bol
 * @since 1.0
 */
class GROUPS_BOL_GroupDao extends OW_BaseDao
{
    const LIST_CACHE_LIFETIME = 86400;
    const LIST_CACHE_TAG = 'groups.list';
    const LIST_CACHE_TAG_LATEST = 'groups.list.latest';
    const LIST_CACHE_TAG_POPULAR = 'groups.list.popular';
    const CHANEL = 'chanel';
    const GROUP = 'group';
    /**
     * Singleton instance.
     *
     * @var GROUPS_BOL_GroupDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GROUPS_BOL_GroupDao
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
        return 'GROUPS_BOL_Group';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'groups_group';
    }

    /**
     * Find latest group authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestGroupAuthorsIds($first, $count)
    {
        $where = 'WHERE';

        if ( !OW::getUser()->isAuthorized('groups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $where .= ' `g`.`whoCanView`="' . GROUPS_BOL_Service::WCV_ANYONE . '" AND';
        }

        $query = "SELECT `g`.`userId` FROM `" . $this->getTableName() . "` AS `g`
            $where `g`.`status`=:s
            GROUP BY `g`.`userId` ORDER BY MAX(`g`.`timeStamp`) DESC LIMIT :f, :c";

        return $this->dbo->queryForColumnList($query, array(
            'f' => $first,
            'c' => $count,
            's' => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }

    /**
     * Find latest public group list ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicGroupListIds( $first, $count )
    {
        $example = new OW_Example();

        $example->setOrder('`timeStamp` DESC');
        $example->setLimitClause($first, $count);

        if ( !OW::getUser()->isAuthorized('groups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $example->andFieldEqual('whoCanView', GROUPS_BOL_Service::WCV_ANYONE);
        }

        $example->andFieldEqual("status", GROUPS_BOL_Group::STATUS_ACTIVE);

        return $this->findIdListByExample($example);
    }

    public function findOrderedList( $first, $count , $isNativeAdminOrGroupModerator = false)
    {
        $first = (int) $first;
        $count = (int) $count;
        if(OW::getUser()->isAuthenticated()){
            if(OW::getUser()->isAuthorized('groups')){
                $example = new OW_Example();
                $example->setOrder('`timeStamp` DESC');
                $example->setLimitClause($first, $count);
                if (!$isNativeAdminOrGroupModerator){
                    $example->andFieldEqual("status", GROUPS_BOL_Group::STATUS_ACTIVE);
                }
                return $this->findListByExample($example, self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_LATEST ));
            }else{
                $query = "select distinct g.* from ".OW_DB_PREFIX."groups_group g, ".OW_DB_PREFIX."groups_group_user guser where guser.groupId = g.id and g.`status` = :status and (g.whoCanView = :whoCanView or guser.userId = :userId) order by timeStamp desc limit :f,:c";
                return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
                    'f' => $first,
                    'c' => $count,
                    'userId' => OW::getUser()->getId(),
                    'whoCanView' => GROUPS_BOL_Service::WCV_ANYONE,
                    'status' => GROUPS_BOL_Group::STATUS_ACTIVE));
            }
        }else{
            $example = new OW_Example();
            $example->setOrder('`timeStamp` DESC');
            $example->setLimitClause($first, $count);
            $example->andFieldEqual('whoCanView', GROUPS_BOL_Service::WCV_ANYONE);
            $example->andFieldEqual("status", GROUPS_BOL_Group::STATUS_ACTIVE);
            return $this->findListByExample($example, self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_LATEST ));
        }
    }

    public function findLimitedList( $count )
    {
        $example = new OW_Example();
        $example->setLimitClause(0, $count);

        return $this->findListByExample($example);
    }

    public function findMostPupularList( $first, $count, $isNativeAdminOrGroupModerator = false )
    {
        $groupUserTable = GROUPS_BOL_GroupUserDao::getInstance()->getTableName();

        $where = 'WHERE';
        if(OW::getUser()->isAuthenticated()){
            $userId = OW::getUser()->getId();
            if(!OW::getUser()->isAuthorized('groups')){
                $where .= ' (g.whoCanView="' . GROUPS_BOL_Service::WCV_ANYONE . '" or gu.userId = '.$userId.') AND ';
            }
        }else{
            $where .= ' g.whoCanView="' . GROUPS_BOL_Service::WCV_ANYONE . '" AND ';
        }

        $where = $isNativeAdminOrGroupModerator ? $where : $where ." `g`.status=:s ";

        $query = "SELECT `g`.`id`, `g`.`title`, `g`.`description`, `g`.`imageHash`, `g`.`timeStamp`, `g`.`userId`,`g`.`privacy`, `g`.`whoCanView`, `g`.`whoCanInvite`, `g`.`status`,count(gu.userId) as `groupMembers` 
                from `".$this->getTableName(). "` as `g`
                inner join `".OW_DB_PREFIX."groups_group_user` as `gu`
                on `g`.`id` = `gu`.`groupId` ". $where ."
                group by `g`.`id`
                order by count(`gu`.`userId`) desc
                LIMIT :f, :c";

       /* $query = "SELECT `g`.* FROM `" . $this->getTableName() . "` AS `g`
            LEFT JOIN `" . $groupUserTable . "` AS `gu` ON `g`.`id` = `gu`.`groupId`
            $where g.status=:s
            GROUP BY `g`.`id` ORDER BY COUNT(`gu`.`id`) DESC LIMIT :f, :c";*/

        $queryParams = array('f' => $first, 'c' => $count);
        if (!$isNativeAdminOrGroupModerator){
            $queryParams['s'] = GROUPS_BOL_Group::STATUS_ACTIVE;
        }
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $queryParams,
            self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_POPULAR ));
    }

    public function findAllCount()
    {
        $example = new OW_Example();

        if ( !OW::getUser()->isAuthorized('groups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $example->andFieldEqual('whoCanView', GROUPS_BOL_Service::WCV_ANYONE);
        }
        
        $example->andFieldEqual("status", GROUPS_BOL_Group::STATUS_ACTIVE);

        return $this->countByExample($example);
    }

    public function findByTitle( $title )
    {
        $example = new OW_Example();
        $example->andFieldEqual('title', $title);

        return $this->findObjectByExample($example);
    }

    public function findAllUserGroups( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }


    private function checkTypeParameter($type=null)
    {
        if(isset($type)) {
            if (!in_array(strtolower($type), array(self::GROUP, self::CHANEL))) {
                throw new InvalidArgumentException('invalid type input exception');
            }
        }
    }

    private function getTypeWhereClause($groupTableName,$type=null)
    {
        $typeWhereClause = "";

        if (strtolower($type) == self::CHANEL)
        {
            $typeWhereClause = " AND ".$groupTableName.".`isChannel` = 1 ";
        }

        if(strtolower($type) == self::GROUP)
        {
            $typeWhereClause = " AND ".$groupTableName.".`isChannel` = 0 ";
        }

        return $typeWhereClause;
    }


    public function findByUserId( $userId, $first = null, $count = null, $inGroupIdList = null,
                                  $searchTitle = null, $orderWithLastActivity = true, $parentId=null,
                                  $status=GROUPS_BOL_Group::STATUS_ACTIVE ,$type=null)
    {
        $this->checkTypeParameter($type);
        $filters =['userId'=> $userId, 'groupIds' => $inGroupIdList,'searchTitle'=>$searchTitle,
            'parentId'=>$parentId,'orderWithLastActivity'=>$orderWithLastActivity,
            'status'=> $status,'type' =>$type];
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();
        $owner = false;
        $viewerGroupQuery["before"] = "";
        $viewerGroupQuery["query"] = "";
        $viewerGroupQuery["after"] = "";
        $viewerId = null;
        $limit = '';
        if ( $first !== null && $count !== null )
        {
            $limit = " LIMIT $first, $count";
        }
        $params = array(
            'u' => $userId,
            's' => $status
        );
        $statusClause = "AND g.status=:s ";

        $searchQuery = "";
        if($inGroupIdList!=null && sizeof($inGroupIdList)>0){
            $searchQuery.=  " AND `g`.`id` in (". OW::getDbo()->mergeInClause($inGroupIdList) .")";
        }
        if($searchTitle!=null){
            $searchQuery.=' AND UPPER(`g`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $searchTitle . '%';
        }


        $typeWhereClause="";
        if(isset($type))
        {
            $typeWhereClause = $this->getTypeWhereClause("`g`",$type);
        }

        if(OW::getUser()->isAuthenticated()){
            if($userId == OW::getUser()->getId()){
                $owner = true;
            }else if ( !OW::getUser()->isAuthorized('groups')){
                $viewerId = OW::getUser()->getId();
                $viewerGroupQuery["before"] = "select * from ( ";
                $viewerGroupQuery["query"] = " GROUP BY g.id union (select g.* from ".$groupUserDao->getTableName()."
                gu, " . $this->getTableName() . " g  where g.id = gu.groupId and
                gu.userId = :u and g.whoCanView = :invite ".$typeWhereClause."
                 and g.id in (select gu2.groupId from "
                .$groupUserDao->getTableName()." gu2 where gu2.userId = :vid) ".$searchQuery." GROUP BY g.id ) ";
                $viewerGroupQuery["query"] = $viewerGroupQuery["query"] . " ) as g ";
            }
        }

        if($viewerId != null){
            $params['vid'] = OW::getUser()->getId();
            $params['invite'] = GROUPS_BOL_Service::WCV_INVITE;
            $filters['vid'] = OW::getUser()->getId();
            $filters['invite'] = GROUPS_BOL_Service::WCV_INVITE;
        }

        $wcvWhere = ' 1 ';

        if ( !OW::getUser()->isAuthorized('groups') && !$owner ) //TODO TEMP Hack - checking if current user is moderator
        {
            $wcvWhere = 'g.whoCanView="' . GROUPS_BOL_Service::WCV_ANYONE . '"';
        }

        $columnOrder = 'timeStamp';
        if($orderWithLastActivity){
            $columnOrder = 'lastActivityTimeStamp';
        }
        $query = $viewerGroupQuery["before"] . "SELECT g.* FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId 
             WHERE u.userId=:u " . $statusClause . " AND " . $wcvWhere . $searchQuery
            .$typeWhereClause. $viewerGroupQuery["query"] . " ORDER BY g.".$columnOrder." DESC " . $limit;

        $filters['whereClause']['typeWhereClause']=$typeWhereClause;
        $eventQueryParams =['filters'=>$filters, 'type'=>'my', 'limit'=> $limit];
        $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list',$eventQueryParams));
        if(isset($eventQuery->getData()['query']))
        {
            $query = $eventQuery->getData()['query'];
        }
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }

    public function findByUserIdQuery($userId, $inGroupIdList = null, $searchTitle = null, $parentId = null, $status = GROUPS_BOL_Group::STATUS_ACTIVE, $type = null)
    {

        $this->checkTypeParameter($type);
        $filters =['userId'=> $userId, 'groupIds' => $inGroupIdList,'searchTitle'=>$searchTitle,
            'parentId'=>$parentId,
            'status'=> $status,'type' =>$type];

        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();
        $owner = false;
        $viewerGroupQuery["before"] = "";
        $viewerGroupQuery["query"] = "";
        $viewerGroupQuery["after"] = "";
        $viewerId = null;

        $params = array(
            'u' => $userId,
            's' => $status
        );
        $statusClause = "AND g.status=:s ";

        $searchQuery = "";
        if($inGroupIdList!=null && sizeof($inGroupIdList)>0){
            $searchQuery.=  " AND `g`.`id` in (". OW::getDbo()->mergeInClause($inGroupIdList) .")";
        }
        if($searchTitle!=null){
            $searchQuery.=' AND UPPER(`g`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $searchTitle . '%';
        }

        $typeWhereClause="";
        if(isset($type))
        {
            $typeWhereClause = $this->getTypeWhereClause("`g`",$type);
        }

        if(OW::getUser()->isAuthenticated()){
            if($userId == OW::getUser()->getId()){
                $owner = true;
            }else if ( !OW::getUser()->isAuthorized('groups')){
                $viewerId = OW::getUser()->getId();
                $viewerGroupQuery["before"] = "select * from ( ";
                $viewerGroupQuery["query"] = " GROUP BY g.id union (select g.* from ".$groupUserDao->getTableName()."
                gu, " . $this->getTableName() . " g  where g.id = gu.groupId and
                gu.userId = :u and g.whoCanView = :invite ".$typeWhereClause."
                 and g.id in (select gu2.groupId from "
                    .$groupUserDao->getTableName()." gu2 where gu2.userId = :vid) ".$searchQuery." GROUP BY g.id ) ";
                $viewerGroupQuery["query"] = $viewerGroupQuery["query"] . " ) as g ";
            }
        }

        if($viewerId != null){
            $params['vid'] = OW::getUser()->getId();
            $params['invite'] = GROUPS_BOL_Service::WCV_INVITE;
            $filters['vid'] = OW::getUser()->getId();
            $filters['invite'] = GROUPS_BOL_Service::WCV_INVITE;
        }

        $wcvWhere = ' 1 ';

        if ( !OW::getUser()->isAuthorized('groups') && !$owner ) //TODO TEMP Hack - checking if current user is moderator
        {
            $wcvWhere = 'g.whoCanView="' . GROUPS_BOL_Service::WCV_ANYONE . '"';
        }


        $query = $viewerGroupQuery["before"] . "SELECT g.id, g.lastActivityTimeStamp, 'group' as type FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId 
             WHERE u.userId=:u " . $statusClause . " AND " . $wcvWhere . $searchQuery
            .$typeWhereClause. $viewerGroupQuery["query"];

        $filters['whereClause']['typeWhereClause']=$typeWhereClause;
        $eventQueryParams =['filters'=>$filters, 'type'=>'my'];
        $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list.without.order', $eventQueryParams));
        if(isset($eventQuery->getData()['query']))
        {
            $query = $eventQuery->getData()['query'];
        }

        $result = [
            "query" => $query,
            "params" => $params
        ];

        return $result;
    }




    public function findCountByUserId( $userId, $inGroupIdList = null, $searchTitle = null, $parentId=null,
                                       $status=GROUPS_BOL_Group::STATUS_ACTIVE, $type=null)
    {
        $this->checkTypeParameter($type);
        $filters =['userId'=> $userId, 'groupIds' => $inGroupIdList,'searchTitle'=>$searchTitle, 'parentId'=>$parentId,'status' =>$status,'type'=>$type];
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();
        $owner = false;
        $viewerGroupQuery["before"] = "SELECT count( DISTINCT g.id) ";
        $viewerGroupQuery["query"] = "";
        $viewerGroupQuery["after"] = "";
        $viewerId = null;
        $searchQuery = "";

        $params = array(
            'u' => $userId,
            's' => $status
        );

        if($inGroupIdList!=null && sizeof($inGroupIdList)>0){
            $searchQuery.=  " AND `g`.`id` in (". OW::getDbo()->mergeInClause($inGroupIdList) .")";
        }
        if($searchTitle!=null){
            $searchQuery.=' AND UPPER(`g`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $searchTitle . '%';
        }

        $typeWhereClause="";
        if(isset($type))
        {
            $typeWhereClause = $this->getTypeWhereClause("`g`",$type);
        }

        if(OW::getUser()->isAuthenticated()){
            if($userId == OW::getUser()->getId()){
                $owner = true;
            }else if ( !OW::getUser()->isAuthorized('groups')){
                $viewerId = OW::getUser()->getId();
                $viewerGroupQuery["before"] = "select count(*) from ( SELECT g.* ";
                $viewerGroupQuery["query"] = " group by g.id union (select g.* from ".$groupUserDao->getTableName()." gu, " . $this->getTableName() . " g  where g.id = gu.groupId and gu.userId = :u and g.whoCanView = :invite ".$typeWhereClause." and g.id in (select gu2.groupId from ".$groupUserDao->getTableName()." gu2 where gu2.userId = :vid) ".$searchQuery." group by g.id ) ";
                $viewerGroupQuery["query"] = $viewerGroupQuery["query"] . " ) as g ";
            }
        }

        if($viewerId != null){
            $params['vid'] = OW::getUser()->getId();
            $params['invite'] = GROUPS_BOL_Service::WCV_INVITE;
            $filters['vid'] = OW::getUser()->getId();
            $filters['invite'] = GROUPS_BOL_Service::WCV_INVITE;
        }

        $wcvWhere = ' 1 ';

        if ( !OW::getUser()->isAuthorized('groups') && !$owner ) //TODO TEMP Hack - checking if current user is moderator
        {
            $wcvWhere = 'g.whoCanView="' . GROUPS_BOL_Service::WCV_ANYONE . '"';
        }

        $joinClauseQuerySubGroup='';
        if(isset($parentId))
        {
            $eventSubGroupClause=OW::getEventManager()->trigger(new OW_Event('groups.list.add.where.clause',array('parentGroupId'=>$parentId,'joinColumnWithParentId'=>'`g`.`id`')));
            if(isset($eventSubGroupClause->getData()['joinClauseQuerySubGroup']))
            {
                $joinClauseQuerySubGroup=$eventSubGroupClause->getData()['joinClauseQuerySubGroup'];
            }
        }

        $query = $viewerGroupQuery["before"] . " FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId "
            .$joinClauseQuerySubGroup.
            " WHERE u.userId=:u AND g.status=:s AND " . $wcvWhere . $searchQuery .$typeWhereClause
            .$viewerGroupQuery["query"];

        $filters['whereClause']['typeWhereClause']=$typeWhereClause;
        $eventQueryParams =['filters'=>$filters, 'type'=>'my','queryCount'=> true];
        $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list',$eventQueryParams));
        if(isset($eventQuery->getData()['query']))
        {
            $query = $eventQuery->getData()['query'];
        }
        return (int) $this->dbo->queryForColumn($query, $params);
    }

    public function findMyGroups( $userId, $first = null, $count = null, $type=null )
    {
        return $this->findByUserId($userId, $first, $count,
            null, null, true, null,
            GROUPS_BOL_Group::STATUS_ACTIVE, $type);
    }

    /***
     * @param $ids
     * @param null $first
     * @param null $count
     * @return array
     */
    public function findGroupsWithIds( $ids, $first = null, $count = null )
    {
        if($ids == null || sizeof($ids) == 0){
            return array();
        }
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();

        $limit = '';
        if ( $first !== null && $count !== null )
        {
            $limit = "LIMIT $first, $count";
        }

        $query = "SELECT `g2`.* FROM " . $this->getTableName() . " AS `g2` where `g2`.whoCanView = '".GROUPS_BOL_Service::WCV_ANYONE."' AND `g2`.id in (".OW::getDbo()->mergeInClause($ids).")";
        $params = array();
        if(OW::getUser()->isAdmin() ||  OW::getUser()->isAuthorized('groups')){
            $query = "SELECT `g2`.* FROM " . $this->getTableName() . " AS `g2` where `g2`.id in (".OW::getDbo()->mergeInClause($ids).")";
        }
        else if(OW::getUser()->isAuthenticated()){
            $userCondition = " AND `g`.whoCanView = '".GROUPS_BOL_Service::WCV_INVITE . "'  AND `u`.`userId` = :userId ";
            $query = $query ." union SELECT g.* FROM " . $this->getTableName() . " g INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId WHERE g.status=:s AND `g`.id in (".OW::getDbo()->mergeInClause($ids).") ".$userCondition;

            $params = array(
                's' => GROUPS_BOL_Group::STATUS_ACTIVE,
                'userId' => OW::getUser()->getId()
            );
        }
        $query = $query ." ORDER BY `timeStamp` DESC " . $limit;

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
    }

    public function findMyGroupsCount( $userId )
    {
        return $this->findCountByUserId($userId);
    }

    public function findGroupsByFiltering($query, $params, $useCache)
    {
        if($useCache) {
            return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params,
                self::LIST_CACHE_LIFETIME, array(self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_POPULAR));
        }else{
            return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), $params);
        }
    }

    /**
     * @param bool $popular
     * @param string $status
     * @param null $latest
     * @param null $userId
     * @param array $groupIds
     * @param null $searchTitle
     * @param null $parentId
     * @param bool $isNativeAdminOrGroupModerator
     * @return int
     */
    public function findGroupsByFilteringCount($popular=false,$status=GROUPS_BOL_Group::STATUS_ACTIVE,$latest=null,
                                               $userId=null, $groupIds=array(),$searchTitle=null, $type=null)
    {
        $parentId = null;
        $isNativeAdminOrGroupModerator = false;
        $filters =['popular'=> $popular,'status'=> $status, 'latest'=> $latest, 'userId'=> $userId,
            'groupIds' => $groupIds,'searchTitle'=>$searchTitle, 'parentId'=>$parentId,
            'isNativeAdminOrGroupModerator' => $isNativeAdminOrGroupModerator];

        if($userId!=null)
        {
            return $this->findCountByUserId($userId, $groupIds, $searchTitle, $parentId, $status, $type);
        }
        $groupUserDao = GROUPS_BOL_GroupUserDao::getInstance();

        $whereClause = " WHERE 1=1 ";
        $OrderClause="";
        $params = array();
        if ( !OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('groups') ) //TODO TEMP Hack - checking if current user is moderator
        {
            $whereClause .= ' AND (`g`.`whoCanView`="' . GROUPS_BOL_Service::WCV_ANYONE.'"';
            if(OW::getUser()->isAuthenticated()){
                $whereClause .=" OR `u`.`userId`=:userId ) ";
                $params['userId']=OW::getUser()->getId();
                $filters['userId']=OW::getUser()->getId();
            }else{
                $whereClause .=" ) ";
            }
        }
        if($userId!=null)
        {
            $whereClause.=" AND `u`.`userId`=:u";
            $params['u']=$userId;
        }
        if($groupIds!=null && sizeof($groupIds)>0){
            $whereClause.=  " AND `g`.`id` in (". OW::getDbo()->mergeInClause($groupIds) .")";
        }
        if(!empty($searchTitle)){
            $whereClause.=' AND UPPER(`g`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $searchTitle . '%';
        }
        if(isset($latest) || isset($userId)){
            $OrderClause=" ORDER BY `g`.`timeStamp` DESC";
        }
        if(!$isNativeAdminOrGroupModerator){
            $whereClause.=" AND `g`.`status`=:s ";
            $params['s']=$status;
        }
        if($popular){
            $whereClause.=" ORDER BY COUNT(`u`.`id`) DESC ";
        }

        $joinClauseQuerySubGroup='';
        if(isset($parentId))
        {
            $eventSubGroupClause=OW::getEventManager()->trigger(new OW_Event('groups.list.add.where.clause',array('parentId'=>$parentId,'joinColumnWithParentId'=>'`g`.`id`')));
            if(isset($eventSubGroupClause->getData()['joinClauseQuerySubGroup']))
            {
                $joinClauseQuerySubGroup=$eventSubGroupClause->getData()['joinClauseQuerySubGroup'];
            }
        }
        $eventQuery = OW_EventManager::getInstance()->trigger(new OW_Event('frmsubgroups.replace.query.group.list',['filters'=>$filters, 'type'=>($popular==true)?'popular':'latest', 'queryCount'=> true]));
        if(isset($eventQuery->getData()['query']))
        {
            $query = $eventQuery->getData()['query'];
        }
        if(!isset($query)) {
            if($status==GROUPS_BOL_Group::STATUS_APPROVAL) {
                $whereClause .= GROUPS_BOL_Service::getInstance()->generateInClauseForGroupForQuestionRoles();
            }
            $query = "SELECT COUNT(DISTINCT `g`.`id`) FROM " . $this->getTableName() . " g
            INNER JOIN " . $groupUserDao->getTableName() . " u ON g.id = u.groupId"
                . $joinClauseQuerySubGroup. $whereClause .$OrderClause ;
        }
        if($popular) {
            return (int)$this->dbo->queryForColumn($query, $params,
                self::LIST_CACHE_LIFETIME, array( self::LIST_CACHE_TAG, self::LIST_CACHE_TAG_POPULAR));
        }else{
            return (int)$this->dbo->queryForColumn($query, $params);
        }
    }

    public function setPrivacy( $userId, $privacy )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET privacy=:p WHERE userId=:u';

        $this->dbo->query($query, array(
            'p' => $privacy,
            'u' => $userId
        ));
    }


    /**
     * @param integer $userId
     * @return array<GROUPS_BOL_Invite>
     */
    public function findUserInvitedGroups( $userId, $first, $count )
    {
        $query = "SELECT `g`.*, max(`i`.`timestamp`) AS inviteTimeStamp  FROM `" . $this->getTableName() . "` AS `g`
            INNER JOIN `" . GROUPS_BOL_InviteDao::getInstance()->getTableName() . "` AS `i` ON ( `g`.`id` = `i`.`groupId` )
            WHERE `i`.`userId` = :u AND g.`status`=:status GROUP BY `g`.`id`
            ORDER BY inviteTimeStamp DESC LIMIT :f, :c";

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            'u' => (int) $userId,
            'f' => (int) $first,
            'c' => (int) $count,
            "status" => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }

    /**
     * @param integer $userId
     * @return integer
     */
    public function findUserInvitedGroupsCount( $userId, $newOnly = false )
    {
        $addWhere = $newOnly ? 'i.viewed=0' : '1';

        $query = "SELECT COUNT(DISTINCT g.id) AS `count` FROM `" . $this->getTableName() . "` AS `g`
            INNER JOIN `" . GROUPS_BOL_InviteDao::getInstance()->getTableName() . "` AS `i` ON ( `g`.`id` = `i`.`groupId` )
            WHERE `i`.`userId` = :u AND g.status=:status AND " . $addWhere;

        return $this->dbo->queryForColumn($query, array(
            'u' => (int) $userId,
            'status' => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }

    public function findAllLimited( $first = null, $count = null, $isNativeAdminOrGroupModerator = false )
    {
        $example = new OW_Example();

        $example->setOrder(" id DESC ");

        if ( $first != null && $count !=null )
        {
            $example->setLimitClause($first, $count);
        }

        if (!$isNativeAdminOrGroupModerator){
            $example->andFieldEqual("status", GROUPS_BOL_Group::STATUS_ACTIVE);
        }

        return $this->findListByExample($example);
    }

    /**
     * @param $groupId
     */
    public function activateGroupStatusById($groupId)
    {
        $query = "UPDATE " . $this->getTableName() . " g
            SET g.`status`=:status
            WHERE g.`id`=:id";

        $this->dbo->query($query, array(
            'id' => $groupId,
            'status' => GROUPS_BOL_Group::STATUS_ACTIVE
        ));
    }
}