<?php
/**
 * Class FRMSUBGROUPS_BOL_Service
 */
class FRMSUBGROUPS_BOL_Service
{

    private $subgroupDao;
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * FRMSUBGROUPS_BOL_Service constructor.
     */
    private function __construct()
    {
        $this->subgroupDao = FRMSUBGROUPS_BOL_SubgroupDao::getInstance();
    }

    /**
     * @param $parentGroupId
     * @param null $first
     * @param null $count
     * @return mixed
     */
    public function findSubGroups($parentGroupId, $first = null, $count = null)
    {
        return $this->subgroupDao->findSubGroups($parentGroupId, $first, $count);
    }

    /**
     * @param $parentGroupId
     * @return mixed
     */
    public function findSubGroupListCount($parentGroupId)
    {
        return $this->subgroupDao->findSubGroupListCount($parentGroupId);
    }

    /**
     * @param OW_Event $event
     */
    public function checkAccessCreateSubgroups(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['groupId'])) {
            return;
        }
        $groupId = $params['groupId'];
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        if (!isset($groupDto)) {
            return;
        }
        $canCreateSubGroup = false;
        $isUserInGroup = false;
        if (OW::getUser()->isAuthenticated()) {
            $userId = OW::getUser()->getId();
            $isUserInGroup = GROUPS_BOL_Service::getInstance()->findUser($groupId, $userId);
        }

        $isModerator = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
        if ($isUserInGroup || $isModerator) {
            $canCreateSubGroup = true;
        }
        $title = UTIL_String::truncate(strip_tags($groupDto->title), 100, '...');
        $event->setData(array('canCreateSubGroup' => $canCreateSubGroup, 'groupId' => $groupId, 'groupTitle' => $title));
    }


    /**
     * @param OW_Event $event
     */
    public function checkAccessViewSubgroups(OW_Event $event)
    {
        $canView = true;
        $params = $event->getParams();
        if (!isset($params['groupId'])) {
            $canView = false;
        }
        $groupId = $params['groupId'];
        $groupService = GROUPS_BOL_Service::getInstance();
        $groupDto = $groupService->findGroupById($groupId);
        if (!isset($groupDto)) {
            $canView = false;
        }


        $isUserInSubGroup = $groupService->findUser($groupId, OW::getUser()->getId());
        $canEdit = $groupService->isCurrentUserCanEdit($groupDto);
        if(!$isUserInSubGroup && !$canEdit)
        {
            $canView = false;
        }
        $event->setData(['canView' => $canView]);
    }


    /**
     * @param OW_Event $event
     */
    public function checkAccessViewSubgroupDetails(OW_Event $event)
    {
        $canView = true;
        $params = $event->getParams();
        if (!isset($params['subGroupId'])) {
            return;
        }
        $subGroupId = $params['subGroupId'];
        $subGroupEntityDto=$this->subgroupDao->findSubGroupDto($subGroupId);
        if(!isset($subGroupEntityDto))
        {
            return;
        }

        /**
         * Guest user can't view the subgroups
         */
        if(!OW::getUser()->isAuthenticated())
        {
            $event->setData(['canView' => false]);
            return;
        }

        /**
         * no need to check for ua user which is already a member of this subgroup
         */
        $isUserInSubGroup = GROUPS_BOL_Service::getInstance()->findUser($subGroupId, OW::getUser()->getId());
        if($isUserInSubGroup)
        {
            return;
        }

        /**
         * check if user is member of parent group
         */
        $parentGroupDto = GROUPS_BOL_Service::getInstance()->findGroupById($subGroupEntityDto->parentGroupId);
        if (!isset($parentGroupDto)) {
            return;
        }

        $isUserInParentGroup = GROUPS_BOL_Service::getInstance()->findUser($parentGroupDto->getId(), OW::getUser()->getId());
        if (!$isUserInParentGroup && !OW::getUser()->isAuthorized('groups')) {
            $canView = false;
        }
        $event->setData(['canView' => $canView]);
    }

    /**
     * @param OW_Event $event
     */
    public function addParentGroupField(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if (isset($_GET['parentGroupId']) && isset($params['form'])) {
            $parentGroupId = $_GET['parentGroupId'];
            $form = $params['form'];
            $eventHasAccess = OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.create.subgroups', array('groupId' => $parentGroupId)));
            if (isset($eventHasAccess->getData()['canCreateSubGroup']) && $eventHasAccess->getData()['canCreateSubGroup']) {
                $hiddenField = new HiddenField('parentGroupId');
                $hiddenField->setValue($parentGroupId);
                $form->addElement($hiddenField);
                $data['form'] = $form;
                $parentGroupTitle = $eventHasAccess->getData()['groupTitle'];
                $data['parentGroupTitle'] = $parentGroupTitle;
                $data['backUrl'] = OW::getRouter()->urlForRoute('groups-view', array('groupId' => $parentGroupId));
                $event->setData($data);
            }
        }
    }


    public function createSubgroup(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if ((isset($_POST['parentGroupId']) || isset($params['parentGroupId'])) && isset($params['groupId'])) {
            $parentGroupId = isset($params['parentGroupId']) ? $params['parentGroupId'] : $_POST['parentGroupId'];
            $subGroupId = $params['groupId'];
            if ($parentGroupId == $subGroupId) {
                return;
            }
            $eventHasAccess = OW::getEventManager()->trigger(new OW_Event('frmsubgroup.check.access.create.subgroups', array('groupId' => $parentGroupId)));
            if (isset($eventHasAccess->getData()['canCreateSubGroup']) && $eventHasAccess->getData()['canCreateSubGroup']) {
                $subGroupDto = new FRMSUBGROUPS_BOL_Subgroup();
                $subGroupDto->parentGroupId = $parentGroupId;
                $subGroupDto->subGroupId = $subGroupId;
                $this->subgroupDao->save($subGroupDto);
            }
        }
    }

    /**
     * @param OW_Event $event
     */
    public function addFindSubGroupsWhereClause(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if (!isset($params['joinColumnWithParentId']) || !isset($params['parentGroupId'])) {
            return;
        }
        $parentGroupId = $params['parentGroupId'];
        $joinColumnWithParentId = $params['joinColumnWithParentId'];
        $joinClauseQuerySubGroup = " INNER JOIN " . $this->subgroupDao->getTableName() . " AS `sb` ON " . $joinColumnWithParentId . "=`sb`.`subGroupId` AND `sb`.`parentGroupId`= " . $parentGroupId;
        $data['joinClauseQuerySubGroup'] = $joinClauseQuerySubGroup;
        $event->setData($data);
    }


    /**
     * @param $subGroupId
     * @return array
     */
    public function getParentGroupUsers($subGroupId)
    {
        $subgroupDto = $this->subgroupDao->findSubGroupDto($subGroupId);
        $parentGroupUserIdLists = array();
        if (isset($subgroupDto) && FRMSecurityProvider::checkPluginActive('groups', true)) {
            $parentGroupUserIdLists = GROUPS_BOL_Service::getInstance()->findGroupUserIdList($subgroupDto->parentGroupId);
        }
        return $parentGroupUserIdLists;
    }

    /**
     * @param null $searchedTitle
     * @param null $url
     * @return Form
     */
    public function getGroupFilterForm($searchedTitle = null, $url = null)
    {
        $form = new Form('subGroupFilterForm');
        if (isset($url)) {
            $form->setAction($url);
        }
        $form->setMethod(Form::METHOD_GET);
        $searchTitle = new TextField('searchTitle');
        $searchTitle->addAttribute('placeholder', OW::getLanguage()->text('frmsubgroups', 'search_subgroups_title'));
        $searchTitle->addAttribute('class', 'group_search_title');
        $searchTitle->addAttribute('id', 'searchTitle');
        if ($searchedTitle != null) {
            $searchTitle->setValue($searchedTitle);
        }
        $searchTitle->setHasInvitation(false);
        $form->addElement($searchTitle);

        return $form;
    }

    public function onInviteParentUsers(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if (isset($params['groupId'])) {
            $groupId = $params['groupId'];
            $parentGroupUserIds = $this->getParentGroupUsers($groupId);
            if (sizeof($parentGroupUserIds) > 0) {
                $data['parentGroupUserIds'] = $parentGroupUserIds;
                $event->setData($data);
            }
        }
    }

    /**
     * @param OW_Event $event
     */
    public function replaceQueryGroupList(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['type'])) {
            return;
        }
        $limit = '';
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }
        $filters = $params['filters'];
        $query = null;
        /**
         * get query count list
         */
        if (isset($params['queryCount']))
        {
            if (isset($filters['userId']))
            {
                $query=$this->findQueryForUserCount($params['type'],$filters);
            } else {
                $query=$this->findQueryCount($params['type'],$filters);
            }
        }
        //----------- end get query count list ------------------
        /**
         * get query list
         */
        else {
            if (isset($filters['userId'])) {
                $query=$this->findQueryForUser($params['type'],$filters,$limit);
            } else {
                $query = $this->findQuery($params['type'],$filters,$limit);
            }
        }
        //-------------get query list -----------------------
        $event->setData(['query' => $query]);
    }

    /**
     * @param OW_Event $event
     */
    public function replaceQueryGroupListWithoutOrder(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['type'])) {
            return;
        }
        $limit = '';
        if (isset($params['limit'])) {
            $limit = $params['limit'];
        }
        $filters = $params['filters'];
        $query = null;
        /**
         * get query count list
         */
        if (isset($params['queryCount']))
        {
            if (isset($filters['userId']))
            {
                $query=$this->findQueryForUserCount($params['type'],$filters);
            } else {
                $query=$this->findQueryCount($params['type'],$filters);
            }
        }
        //----------- end get query count list ------------------
        /**
         * get query list
         */
        else {
            if (isset($filters['userId'])) {
                $query=$this->findQueryForUserWithoutOrder($params['type'],$filters,$limit);
            } else {
                $query = $this->findQuery($params['type'],$filters,$limit);
            }
        }
        //-------------get query list -----------------------
        $event->setData(['query' => $query]);
    }


    /**
     * @param $type
     * @param $filters
     * @return string
     */
    private function findQueryForUserCount($type,$filters)
    {
        $query=null;
        switch ($type) {
            case 'latest':
                $query = $this->latestQueryForUserCount($filters);
                break;
            case 'popular':
                $query = $this->popularQueryForUserCount($filters);
                break;
            case 'my':
                if(isset($filters['vid']))
                {
                    $query = $this->anotherUserViewQueryForUserCount($filters);
                }
                else {
                    $query = $this->myQueryForUserCount($filters);
                }
                break;
        }
        return $query;
    }

    /**
     * @param $type
     * @param $filters
     * @return mixed|string|null
     */
    private function findQueryCount($type,$filters)
    {
        $query=null;
        switch ($type) {
            case 'latest':
                if (OW::getUser()->isAuthorized('groups')) {
                    $query = $this->latestGroupListForAdminCount($filters);
                } else {
                    $query = $this->latestQueryForGuestCount($filters);
                }
                break;
            case 'popular':
                if (OW::getUser()->isAuthorized('groups')) {
                    $query = $this->popularGroupListForAdminCount($filters);
                } else {
                    $query = $this->popularQueryForGuestCount($filters);
                }
                break;
        }
        return $query;
    }

    /**
     * @param $type
     * @param $filters
     * @param $limit
     * @return string|null
     */
    private function findQueryForUser($type,$filters,$limit)
    {
        $query=null;
        switch ($type) {
            case 'latest':
                $query = $this->latestQueryForUser($filters, $limit);
                break;
            case 'popular':
                $query = $this->popularQueryForUser($filters, $limit);
                break;
            case 'my':
                if(isset($filters['vid']))
                {
                    $query = $this->anotherUserViewQueryForUser($filters, $limit);
                }
                else{
                    $query = $this->myQueryForUser($filters, $limit);
                }
                break;
        }
        return $query;
    }

    /**
     * @param $type
     * @param $filters
     * @param $limit
     * @return string|null
     */
    private function findQueryForUserWithoutOrder($type,$filters,$limit)
    {
        $query=null;
        switch ($type) {
            case 'latest':
                $query = $this->latestQueryForUser($filters, $limit);
                break;
            case 'popular':
                $query = $this->popularQueryForUser($filters, $limit);
                break;
            case 'my':
                if(isset($filters['vid']))
                {
                    $query = $this->anotherUserViewQueryForUser($filters, $limit);
                }
                else{
                    $query = $this->myQueryForUserWithoutOrderLimit($filters);
                }
                break;
        }
        return $query;
    }

    /**
     * @param $type
     * @param $filters
     * @param $limit
     * @return string|null
     */
    private function findQuery($type,$filters,$limit)
    {
        $isQuestionRoleModerator = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers();

        $query=null;
        switch ($type) {
            case 'latest':
                if (OW::getUser()->isAuthorized('groups') || OW::getUser()->isAdmin() || $isQuestionRoleModerator) {
                    $query = $this->latestGroupListForAdmin($filters, $limit);
                } else {
                    $query = $this->latestQueryForGuest($filters, $limit);
                }
                break;
            case 'popular':
                if (OW::getUser()->isAuthorized('groups')) {
                    $query = $this->popularGroupListForAdmin($filters, $limit);
                } else {
                    $query = $this->PopularQueryForGuest($filters,$limit);
                }
                break;
        }
        return $query;
    }

    /**
     * @param filters
     * @param limit
     * @return string
     */
    private function popularQueryForGuest($filters, $limit)
    {
        $whereClause = ' ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query='
                SELECT COUNT(`gu`.`userId`), `g`.*, NULL parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id 
                INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user gu ON g.id = gu.groupId 
                WHERE sb.parentGroupId IS NULL AND `g`.`whoCanView`="anyone" '.$whereClause. ' GROUP BY `g`.`id` ORDER BY COUNT(`gu`.`userId`) DESC '. $limit;
        return $query;
    }

    /**
     * @param filters
     * @return mixed
     */
    private function popularQueryForGuestCount($filters)
    {
        return $this->latestQueryForGuestCount($filters);
    }
    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function latestQueryForGuest($filters, $limit)
    {
        $whereClause = ' ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query='
                SELECT DISTINCT `g`.*, NULL parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                WHERE sb.parentGroupId IS NULL AND `g`.`whoCanView`="anyone" '.$whereClause. ' ORDER BY `g`.`timeStamp` DESC '. $limit;
        return $query;
    }

    /**
     * @param $filters
     * @return string
     */
    private function latestQueryForGuestCount($filters)
    {
        $whereClause = ' ';
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        $query='
                SELECT COUNT(DISTINCT g.`id`) FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                WHERE sb.parentGroupId IS NULL AND `g`.`whoCanView`="anyone" '.$whereClause;
        return $query;
    }

    /**
     * @param $filters
     * @return string
     */
    private function latestQueryForUserCount($filters)
    {
        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g3`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g3`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g3`.`title`) like UPPER (:searchTitle) ';
        }

        if(isset($filters['status']) && $filters['status']=='approval') {
            $whereClause .= $this->generateInClauseForRolesToManageSpecificUsers('g3');
        }

        $query = '
                    SELECT COUNT(`g3`.`id`) FROM
                    (
                        SELECT DISTINCT `g`.*, NULL parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                        WHERE 
                                
                                (g.id IN (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId))
                        UNION 
                        
                        SELECT DISTINCT `g`.*, `sb`.`parentGroupId` FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                        LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                        WHERE 
                        (
                            sb.parentGroupId IS NULL AND 
                            `g`.`whoCanView`="anyone"
                        )
                        
                        OR 
                        (
                            sb.parentGroupId IS NOT NULL AND 
                            `g`.`whoCanView`="anyone" AND 
                            sb.parentGroupId IN (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId)
                        )
                    
                    ) AS `g3` WHERE '.$whereClause;

        return $query;
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function latestQueryForUser($filters, $limit)
    {
        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g3`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g3`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g3`.`title`) like UPPER (:searchTitle) ';
        }

        if(isset($filters['status']) && $filters['status']=='approval') {
            $whereClause .= $this->generateInClauseForRolesToManageSpecificUsers('g3');
        }
        $query = '
                    SELECT `g3`.* FROM
                    (
                        SELECT DISTINCT `g`.*, NULL parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                        WHERE 
                                
                                (g.id IN (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId))
                        UNION 
                        
                        SELECT DISTINCT `g`.*, `sb`.`parentGroupId` FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                        LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                        WHERE 
                        (
                            sb.parentGroupId IS NULL AND 
                            `g`.`whoCanView`="anyone"
                        )
                        
                        OR 
                        (
                            sb.parentGroupId IS NOT NULL AND 
                            `g`.`whoCanView`="anyone" AND 
                            sb.parentGroupId IN (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId)
                        )
                    
                    ) AS `g3` WHERE '.$whereClause. ' ORDER BY `g3`.`timeStamp` DESC '.$limit;

        return $query;
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function latestGroupListForAdmin($filters, $limit)
    {
        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }

        $whereClause .= $this->generateInClauseForRolesToManageSpecificUsers();

        $query='SELECT DISTINCT `g`.*, `sb`.`parentGroupId` FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id WHERE '.$whereClause. ' ORDER BY `g`.`timeStamp` DESC '. $limit;

        return $query;
    }

    /**
     * @param $filters
     * @return string
     */
    private function latestGroupListForAdminCount($filters)
    {

        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }

        $whereClause .= $this->generateInClauseForRolesToManageSpecificUsers();

        $query='SELECT COUNT(DISTINCT `g`.`id`) FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id WHERE '.$whereClause;

        return $query;
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function popularQueryForUser($filters,$limit)
    {
        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g3`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g3`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g3`.`title`) like UPPER (:searchTitle) ';
        }
        $query = '  
                    SELECT `g4`.cnt,`g5`.*,`sb`.`parentGroupId` AS  parentGroupId FROM 
                    (
                    SELECT COUNT(`gu`.`userId`) as cnt, `g3`.id FROM
                    ( SELECT DISTINCT `g`.*, NULL as parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g` WHERE 
                    (g.id IN (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId)) 
                     UNION SELECT DISTINCT g.*, `sb`.`parentGroupId` as parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group AS `g` LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` 
                     ON sb.subGroupId = g.id 
                     WHERE ( sb.parentGroupId IS NULL AND `g`.`whoCanView`="anyone" ) OR ( sb.parentGroupId IS NOT NULL AND `g`.`whoCanView`="anyone" AND sb.parentGroupId IN                                            
                    (SELECT gu.groupId FROM ' . OW_DB_PREFIX . 'groups_group_user AS `gu` WHERE `gu`.`userId`=:userId) ) ) AS `g3` 
                    INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user gu ON `g3`.`id` = `gu`.`groupId` WHERE '. $whereClause .' GROUP BY `gu`.`groupId` ORDER BY COUNT(`gu`.`userId`) DESC LIMIT 0,3) AS g4
                    
                     LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g4.id INNER JOIN ' . OW_DB_PREFIX . 'groups_group AS `g5` ON g5.id = g4.id
                    ';

        return $query;
    }

    /**
     * @param $filters
     * @return string
     */
    private function popularQueryForUserCount($filters)
    {
        return $this->latestQueryForUserCount($filters);
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function popularGroupListForAdmin($filters,$limit)
    {
        $whereClause = ' 1=1 ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query='SELECT COUNT(`gu`.`userId`), `g`.*, `sb`.`parentGroupId` FROM ' . OW_DB_PREFIX . 'groups_group AS `g`
                LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id  
                INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user gu ON g.id = gu.groupId 
                WHERE '.$whereClause. ' GROUP BY `gu`.`groupId` ORDER BY COUNT(`gu`.`userId`) DESC '. $limit;

        return $query;
    }

    /**
     * @param $filters
     * @return string
     */
    private function popularGroupListForAdminCount($filters)
    {
        return $this->latestGroupListForAdminCount($filters);
    }


    public function anotherUserViewQueryForUser($filters, $limit)
    {
        $whereClause = ' ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query='
        select * from ( SELECT g.*, sb.parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group g
		            INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId 
		            LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS sb ON sb.subGroupId = g.id
		            WHERE u.userId=:u '. $whereClause.' AND g.whoCanView="anyone"
		         union 
						(select g.*,sb1.parentGroupId from ' . OW_DB_PREFIX . 'groups_group_user gu, ' . OW_DB_PREFIX . 'groups_group g 
						 LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS sb1 ON sb1.subGroupId = g.id
						 where g.id = gu.groupId and gu.userId = :u  '.$whereClause.' and g.whoCanView = :invite and g.id in 
								(select gu2.groupId from ' . OW_DB_PREFIX . 'groups_group_user gu2 where gu2.userId = :vid)  
						)  
				) as g  
				order by g.lastActivityTimeStamp DESC '.$limit;
        return $query;
    }

    public function anotherUserViewQueryForUserCount($filters)
    {
        $whereClause = ' ';
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds'] ) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query='
        select COUNT(*) from ( SELECT g.*, sb.parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group g
		            INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId 
		            LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS sb ON sb.subGroupId = g.id
		            WHERE u.userId=:u '. $whereClause.' AND g.whoCanView="anyone"
		         union 
						(select g.*,sb1.parentGroupId from ' . OW_DB_PREFIX . 'groups_group_user gu, ' . OW_DB_PREFIX . 'groups_group g 
						 LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS sb1 ON sb1.subGroupId = g.id
						 where g.id = gu.groupId and gu.userId = :u  '.$whereClause.' and g.whoCanView = :invite and g.id in 
								(select gu2.groupId from ' . OW_DB_PREFIX . 'groups_group_user gu2 where gu2.userId = :vid)  
						)  
				) as g ';
        return $query;
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function myQueryForUser($filters, $limit)
    {
        $whereClause = '';

        $typeJoinClause='';
        if(isset($filters['joinClause']['typeJoinClause']) && !empty(trim($filters['joinClause']['typeJoinClause']))) {
            $typeJoinClause = $filters['joinClause']['typeJoinClause'];
        }

        if(isset($filters['whereClause']['typeWhereClause']) && !empty(trim($filters['whereClause']['typeWhereClause']))) {
            $whereClause .= $filters['whereClause']['typeWhereClause'];
        }
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query = '  SELECT g.*, sb.parentGroupId FROM ' . OW_DB_PREFIX . 'groups_group g
                    INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId '
                    .$typeJoinClause.
                    ' LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                    WHERE u.userId=:u '. $whereClause .' GROUP BY `g`.`id` ORDER BY g.lastActivityTimeStamp DESC '. $limit;

        return $query;
    }

    /**
     * @param $filters
     * @param $limit
     * @return string
     */
    private function myQueryForUserWithoutOrderLimit($filters)
    {
        $whereClause = '';

        $typeJoinClause='';
        if(isset($filters['joinClause']['typeJoinClause']) && !empty(trim($filters['joinClause']['typeJoinClause']))) {
            $typeJoinClause = $filters['joinClause']['typeJoinClause'];
        }

        if(isset($filters['whereClause']['typeWhereClause']) && !empty(trim($filters['whereClause']['typeWhereClause']))) {
            $whereClause .= $filters['whereClause']['typeWhereClause'];
        }
        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }
        $query = '  SELECT g.id, g.lastActivityTimeStamp, "group" AS type FROM ' . OW_DB_PREFIX . 'groups_group g
                    INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId '
            .$typeJoinClause.
            ' LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                    WHERE u.userId=:u '. $whereClause .' GROUP BY `g`.`id` ';

        return $query;
    }


    /**
     * @param $filters
     * @return string
     */
    private function myQueryForUserCount($filters)
    {
        $whereClause = ' ';
        $typeJoinClause='';
        if(isset($filters['joinClause']['typeJoinClause']) && !empty(trim($filters['joinClause']['typeJoinClause']))) {
            $typeJoinClause = $filters['joinClause']['typeJoinClause'];
        }

        if(isset($filters['whereClause']['typeWhereClause']) && !empty(trim($filters['whereClause']['typeWhereClause']))) {
            $whereClause .= $filters['whereClause']['typeWhereClause'];
        }

        if(!isset($filters['isNativeAdminOrGroupModerator']) || !$filters['isNativeAdminOrGroupModerator']){
            $whereClause .= ' AND `g`.`status`=:s ';
        }
        if (isset($filters['groupIds']) && sizeof($filters['groupIds']) > 0) {
            $whereClause .= " AND `g`.`id` in (" . OW::getDbo()->mergeInClause($filters['groupIds']) . ") ";
        }
        if (isset($filters['searchTitle']) && !empty($filters['searchTitle'])) {
            $whereClause .= ' AND UPPER(`g`.`title`) like UPPER (:searchTitle) ';
        }

        $query = '  SELECT COUNT(DISTINCT g.id) FROM ' . OW_DB_PREFIX . 'groups_group g
                    INNER JOIN ' . OW_DB_PREFIX . 'groups_group_user u ON g.id = u.groupId '
                    .$typeJoinClause.
                    ' LEFT OUTER JOIN ' . OW_DB_PREFIX . 'frmsubgroups_groups AS `sb` ON sb.subGroupId = g.id
                    WHERE u.userId=:u '. $whereClause;

        return $query;
    }

    /**
     * @param $parentGroupId
     * @param null $title
     * @param int $first
     * @param int $count
     * @return array|void
     * @throws Redirect404Exception
     */
    public function findSubGROUPSByParentGroup($parentGroupId,$title=null,$first=0,$count=20)
    {
        $isModerator=$this->checkIfParentGroupModerator($parentGroupId);
        return $this->subgroupDao->findSubGROUPSByParentGroup($parentGroupId,$title,$isModerator,$first,$count);
    }

    /**
     * @param $parentGroupId
     * @param null $title
     * @return int|void
     * @throws Redirect404Exception
     */
    public function findSubGROUPSByParentGroupCount($parentGroupId,$title=null)
    {
        $isModerator=$this->checkIfParentGroupModerator($parentGroupId);
        return $this->subgroupDao->findSubGROUPSByParentGroupCount($parentGroupId,$title,$isModerator);
    }

    /**
     * @param $parentGroupId
     * @return bool
     * @throws Redirect404Exception
     */
    private function checkIfParentGroupModerator($parentGroupId)
    {
        $isModerator=false;

        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            throw new Redirect404Exception();
        }

        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($parentGroupId);
        if(!isset($groupDto))
        {
            throw new Redirect404Exception();
        }

        $canEditGroup = GROUPS_BOL_Service::getInstance()->isCurrentUserCanEdit($groupDto);
        if($canEditGroup)
        {
            $isModerator=true;
        }
        return $isModerator;
    }


    /**
     * @param int $first
     * @param int $count
     * @return mixed
     */
    public function findLatestGroupList($first=0,$count=5)
    {
        $limit=' LIMIT '.$first.','.$count;
        $params=array('s'=>'active');
        if(OW::getUser()->isAuthenticated())
        {
            $userId = OW::getUser()->getId();
            if(OW::getUser()->isAuthorized('groups')){
               $query= $this->latestGroupListForAdmin(null,$limit);
            }
            else{
                $query= $this->latestQueryForUser(null,$limit);
                $params['userId']=$userId = OW::getUser()->getId();
            }
        }else{
            $query= $this->latestQueryForGuest(null,$limit);
        }
        return OW::getDbo()->queryForObjectList($query, GROUPS_BOL_GroupDao::getInstance()->getDtoClassName(), $params);
    }


    /**
     * @param int $first
     * @param int $count
     * @return mixed
     */
    public function findPopularGroupList($first=0,$count=5)
    {
        $limit=' LIMIT '.$first.','.$count;
        $params=array('s'=>'active');
        if(OW::getUser()->isAuthenticated())
        {
            $userId = OW::getUser()->getId();
            if(OW::getUser()->isAuthorized('groups')){
                $query= $this->popularGroupListForAdmin(null,$limit);
            }
            else{
                $query= $this->popularQueryForUser(null,$limit);
                $params['userId']=$userId = OW::getUser()->getId();
            }
        }else{
            $query= $this->popularQueryForGuest(null,$limit);
        }
        return OW::getDbo()->queryForObjectList($query,  GROUPS_BOL_GroupDao::getInstance()->getDtoClassName(), $params);
    }


    public function addSubGroupWidget(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['controller']) && isset($params['groupId'])){
            $bcw = new BASE_CLASS_WidgetParameter();
            $bcw->additionalParamList=array('entityId'=>$params['groupId']);
            $groupController = $params['controller'];
            $groupController->addComponent('subGroupsList', new FRMSUBGROUPS_MCMP_SubgroupListWidget($bcw));
            $subGroupsBoxInformation = array(
                'show_title' => true,
                'title' => OW_Language::getInstance()->text('frmsubgroups', 'subgroup_list_title'),
                'wrap_in_box' => true,
                'icon' => 'ow_ic_info',
                'type' => "",
            );
            $groupController->assign('subGroupsBoxInformation', $subGroupsBoxInformation);
        }
    }

    public function loadStaticFiles(){
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmsubgroups')->getStaticCssUrl() . 'frmsubgroups.css');
    }


    /**
     * @param OW_Event $event
     */
    public function getParentGroupData(OW_Event $event)
    {
        $params= $event->getParams();
        $data= $event->getData();

        if(!isset($params['parentGroupId']) && !isset($params['subGroupId']) )
        {
            return;
        }

        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }

        if(isset($params['parentGroupId'])) {
            $parentGroupDto = GROUPS_BOL_Service::getInstance()->findGroupById($params['parentGroupId']);
        } else if(isset($params['subGroupId']))
        {
            $subgroupDto =  $this->subgroupDao->findSubGroupDto($params['subGroupId']);
            if(isset($subgroupDto)) {
                $parentGroupDto = GROUPS_BOL_Service::getInstance()->findGroupById($subgroupDto->parentGroupId);
            }
        }
        if(!isset($parentGroupDto))
        {
            return;
        }

        $content = UTIL_String::truncate(strip_tags($parentGroupDto->title), 200, "...");

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $content)));
        if (isset($stringRenderer->getData()['string'])) {
            $content = ($stringRenderer->getData()['string']);
        }
        $parentUrl=OW::getRouter()->urlForRoute('groups-view', array('groupId' => $parentGroupDto->getId()));
        $data['parentData'] = OW::getLanguage()->text('frmsubgroups','parent_group_title',['parentTitle'=>$content,'parentUrl'=>$parentUrl]);

        $event->setData($data);
    }


    /**
     * @param OW_Event $event
     */
    public function getSubGroupDeleteConfirm(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if(!isset($params['groupId']))
        {
            return;
        }
        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }

        $subGroupsDtos =  $this->subgroupDao->findAllSubgroupsDto($params['groupId']);
        if(sizeof($subGroupsDtos)>0) {
            $i = 0;
            $len = count($subGroupsDtos);
            $subGroupsTitles='<br>';
            foreach ($subGroupsDtos as $subGroupsDto) {
                if ($i == $len - 1) {
                    $subGroupsTitles .= '- '. UTIL_String::truncate(strip_tags($subGroupsDto->title), 100, "...");
                } else {
                    $subGroupsTitles .= '- '.UTIL_String::truncate(strip_tags($subGroupsDto->title), 100, "...") . '<br> ';
                }
                $i++;
            }
            $data['subGroupsTitles'] = OW::getLanguage()->text('frmsubgroups','delete_subgroups_message',['subGroupsTitles'=>$subGroupsTitles]);

            $event->setData($data);
        }
    }

    public function onFindParentGroup(OW_Event $event) {
        $params = $event->getParams();
        if(!isset($params['groupId']))
        {
            return;
        }

        $subgroupDto = $this->subgroupDao->findSubGroupDto($params['groupId']);
        if (isset($subgroupDto)) {
            $event->setData(array('parentId' => $subgroupDto->parentGroupId));
        }
    }

    public function onFindSubGroups(OW_Event $event) {
        $params = $event->getParams();
        $title = null;
        $first = 0;
        $count = 20;
        if(!isset($params['groupId']))
        {
            return;
        }
        if(isset($params['title']))
        {
            $title = $params['title'];
        }
        if(isset($params['first']))
        {
            $first = $params['first'];
        }
        if(isset($params['count']))
        {
            $count = $params['count'];
        }
        $groups = $this->findSubGROUPSByParentGroup($params['groupId'], $title, $first, $count);
        $event->setData(array('groups' => $groups));
    }

    /**
     * @param OW_Event $event
     */
    public function onDeleteGroup(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['groupId']))
        {
            return;
        }
        $groupId = $params['groupId'];
        $groupId = trim($groupId);
        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return;
        }
        $subGroupsDtos =  $this->subgroupDao->findAllSubgroupsDto($groupId);
        if($subGroupsDtos != null && sizeof($subGroupsDtos)>0) {
            foreach ($subGroupsDtos as $subGroupsDto) {
                GROUPS_BOL_Service::getInstance()->deleteGroup($subGroupsDto->id);
                $this->subgroupDao->deleteSubgroupDto($subGroupsDto->id);
            }
        }
    }

    /**
     * @param string $groupTableAlias
     * @return string
     */
    private function generateInClauseForRolesToManageSpecificUsers($groupTableAlias ='g') {
        $whereClause = " ";

        $userRoles = GROUPS_BOL_Service::getInstance()->getUserRolesToManageSpecificUsers();
        $isQuestionRoleModerator = GROUPS_BOL_Service::getInstance()->checkIfUserHasRolesToManageSpecificUsers($userRoles);

        if (!OW::getUser()->isAuthorized('groups') && !OW::getUser()->isAdmin() && $isQuestionRoleModerator) {
            $userIds = OW::getEventManager()->trigger(new OW_Event('frmquestionroles.getUsersByRolesData', array('rolesData' => $userRoles)));
            $userIds = $userIds->getData();
            if (!empty($userIds)) {
                $whereClause = ' AND `'.$groupTableAlias.'`.`userId` IN (' . OW::getDbo()->mergeInClause($userIds) . ') ';
            }
        }
        return $whereClause;
    }
}
