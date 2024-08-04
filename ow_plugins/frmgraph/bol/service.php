<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

final class FRMGRAPH_BOL_Service
{

    private static $classInstance;
    private $nodeDao;
    private $groupDao;
    private $graphDao;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->nodeDao = FRMGRAPH_BOL_NodeDao::getInstance();
        $this->groupDao = FRMGRAPH_BOL_GroupDao::getInstance();
        $this->graphDao = FRMGRAPH_BOL_GraphDao::getInstance();
    }


    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getAllRelationship(){
        if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return array();
        }

        $query = "select userId, feedId from ".OW_DB_PREFIX."newsfeed_follow where feedType = 'user' and permission = 'everybody' and feedId in (select id from ".OW_DB_PREFIX."base_user) and userId in (select id from ".OW_DB_PREFIX."base_user);";
        return OW::getDbo()->queryForList($query);
    }

    public function getAllQuestionsProfile(){
        $questions = BOL_QuestionService::getInstance()->findAllQuestionsBySectionForAccountType('all');
        $result = array();
        foreach ( $questions as $section => $list )
        {
            foreach ( $list as $question )
            {
                if(in_array($question['name'], array("username", "password", "joinStamp", "field_mobile", "realname", "email"))){
                    continue;
                }
                $result[$question['name']] = BOL_QuestionService::getInstance()->getQuestionLang($question['name']);
            }
        }
        return $result;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @return array
     */
    public function getAllGroupRelationship(){
        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return array();
        }

        /***
         * I needed to limit the results for memory limits of apache
         * SELECT groupId FROM `".OW_DB_PREFIX."groups_group_user`

         */

        OW::getDbo()->query("CREATE OR REPLACE VIEW `gnet` AS
            SELECT g1 as `userId`, g2 as `feedId`, count(*) as weight
            FROM (
                SELECT t1.groupId as g1, t2.groupId as g2, t1.userId as u
                FROM `".OW_DB_PREFIX."groups_group_user` as t1,`".OW_DB_PREFIX."groups_group_user` as t2
                WHERE t1.userId = t2.userId and t1.groupId <> t2.groupId
                ORDER BY t1.groupId, t2.groupId ASC
            ) as gs
            group by g1,g2
            ;");

        $query = "
            SELECT tt.userId, min(tt.feedId) as feedId, max(tt.weight) as weight
            FROM `gnet` tt
            INNER JOIN
                (SELECT userId, MAX(weight) AS maxweight
                FROM `gnet`
                GROUP BY userId) groupedtt
            ON tt.userId = groupedtt.userId
            AND tt.weight = groupedtt.maxweight
        group by tt.userId
        ; ";

        $resp = OW::getDbo()->queryForList($query);

        // drop temp view
        OW::getDbo()->query("DROP VIEW IF EXISTS gnet;");

        return $resp;
    }

    public function anonymizingGraph($edgeList, $userIds){
        //map user id from 1 to n
        $fakeToRealMapping = array();
        $realToFakeMapping = array();

        $allUserIdsMapping = array();

        $nodeIdsInEdgeList = array();
        foreach ($edgeList as $edge){
            if(!in_array($edge["feedId"], $nodeIdsInEdgeList)) {
                $nodeIdsInEdgeList[] = $edge["feedId"];
            }
            if(!in_array($edge["userId"], $nodeIdsInEdgeList)) {
                $nodeIdsInEdgeList[] = $edge["userId"];
            }
        }
        sort($nodeIdsInEdgeList);

        for ($i = 1; $i <= sizeof($nodeIdsInEdgeList); $i++) {
            $fakeToRealMapping[$i] = $nodeIdsInEdgeList[$i-1];
            $realToFakeMapping[$nodeIdsInEdgeList[$i-1]] = $i;
        }

        //sort user ids
        sort($userIds);
        $counter = 0;
        $index = sizeof($nodeIdsInEdgeList)+1;
        while($counter < sizeof($userIds)){
            if(!isset($realToFakeMapping[$userIds[$counter]])){
                $fakeToRealMapping[$index] = $userIds[$counter];
                $realToFakeMapping[$userIds[$counter]] = $index;
                $index++;
            }
            $counter++;
        }

        $edgeListAfterMapping = array();
        foreach ($edgeList as $edge){
            $edgeListAfterMapping[] = array("feedId" => $realToFakeMapping[$edge["feedId"]], "userId" => $realToFakeMapping[$edge["userId"]]);
        }

        for ($i = 1; $i <= sizeof($fakeToRealMapping); $i++) {
            $allUserIdsMapping[] = $i;
        }

        return array("edgeList" => $edgeListAfterMapping, "allUsers" =>$allUserIdsMapping, "fakeToRealMapping" => $fakeToRealMapping);
    }

    /***
     * @return int|mixed
     */
    public function getLastGroupId(){
        return $this->graphDao->getLastGroupId();
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @return int
     */
    public function getSelectedGroupId(){
        return OW::getConfig()->getValue('frmgraph', 'group_id', $this->getLastGroupId());
    }

    public function findTopUsers($component, $numberOfAllUsers, $pagination, $numberOfResultRows, $use_cached) {
        $pageNumber = !(empty($_GET['page']) || $_GET['page'] == null) ? $_GET['page'] : 1;
        $selectedGroupId = $this->getSelectedGroupId();
        $userService = BOL_UserService::getInstance();

        if (isset($selectedGroupId)) {
            $lastMetric = $this->getLastGraphCalculatedMetricsByGroupId($selectedGroupId);
            $lastMetricDate = '';
            if ($lastMetric != null) {
                $lastMetricDate = UTIL_DateTime::formatSimpleDate($lastMetric->time);
            }
            $component->assign("lastCalculationDate", $lastMetricDate);
        }

        $allAvailablePagesNumber = ceil($numberOfAllUsers / $numberOfResultRows);
        if ($pageNumber > $allAvailablePagesNumber) {
            $pageNumber = $allAvailablePagesNumber;
        }
        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        // never generate cache file from here
        $topUsers = $this->getTopUsersByFormula(0, [], $numberOfResultRows, array(),
            array(), $pageNumber, false, false, true);

        $topUsers = $topUsers['$users'];
        $allInfo = array();

        $usersId = array();
        foreach ($topUsers as $key => $topUser){
            $usersId[] = $topUser['userId'];
        }
        $users = $userService->findUserListByIdList($usersId, true);
        $avatars = BOL_AvatarService::getInstance()->getAvatarsUrlList($usersId);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($usersId);

        foreach ($topUsers as $key => $topUser){
            if(isset($users[$topUser['userId']])) {
                $user = $users[$topUser['userId']];
                $avatarUrl = null;
                if (isset($avatars[$user->id])) {
                    $avatarUrl = $avatars[$user->id];
                }
                $profileAvatarUrl = $avatarUrl;
                $new_item = array(
                    'rank' => ($pageNumber - 1 ) * $numberOfResultRows + $key + 1,
                    'avatar' => '<a class="avatar" href="'.$userService->getUserUrlForUsername($user->username).'"><img src="'.$profileAvatarUrl.'"/></a>',
                );
                $avatarImageInfo = BOL_AvatarService::getInstance()->getAvatarInfo($user->id, $avatarUrl);
                if ($avatarImageInfo['empty']) {
                    $new_item['avatar'] = '<a href="'.$userService->getUserUrlForUsername($user->username).'" class="avatar colorful_avatar_' . $avatarImageInfo['digit'] .'"><span style="background-image: url('. "'" .$profileAvatarUrl. "'". '); background-color:' . $avatarImageInfo['color'] . '"/></a>';
                }
                $new_item['user_info'] = '<div class="top_user_info">' . $new_item['avatar'] . '<a href="'.$userService->getUserUrlForUsername($user->username).'">'.$displayNames[$user->id].'</a></div>';
                unset($new_item['avatar']);
                $new_item['score'] = floor( $topUser['score']*100.0)/100.0;
                $allInfo[$key] = $new_item;
            }
        }
        $component->assign('allInfo', $allInfo);

        if ($pagination && sizeof($topUsers) > 0) {
            $paging = new BASE_CMP_Paging($pageNumber, ceil($numberOfAllUsers / $numberOfResultRows), 5);
            $component->assign('paging', $paging->render());
            $component->assign('pagination', true);
        } else {
            $component->assign('pagination', false);
        }
    }

    /***
     * @param $groupId
     * @return FRMGRAPH_BOL_Graph | mixed
     */
    public function getLastGraphCalculatedMetricsByGroupId($groupId){
        return $this->graphDao->getLastCalculatedMetricsByGroupId($groupId);
    }

    /***
     * @param $groupId
     * @return mixed
     */
    public function getLastNodeCalculatedMetricsByGroupId($groupId){
        return $this->nodeDao->getLastCalculatedMetricsByGroupId($groupId);
    }

    public function getGraphVisualLevel($curLevel, $curLevelCount, $maxCol, $maxRow){
        if($curLevelCount>=$maxCol*$maxRow){
            return $curLevel*$maxRow+$maxRow - 1;
        }
        return  $curLevel*$maxRow+intval($curLevelCount/$maxCol);
    }

    private function part_1(){
        $numberOfUsers = BOL_UserService::getInstance()->count(true);
        $allUsers = BOL_UserService::getInstance()->findList(0, $numberOfUsers, true);
        $userIds = array();
        foreach ($allUsers as $user){
            if(!in_array($user->id, $userIds)){
                $userIds[] = $user->id;
            }
        }
        return $this->anonymizingGraph($this->getAllRelationship(), $userIds);
    }

    private function part_2($ccs, $metricsResult, $sizeOfAllUsers, $sizeofEdgeList){
        $graph = new FRMGRAPH_BOL_Graph();
        $graph->adjacency_list = json_encode($this->getUserAdjacencyList());
        $graph->cluster_coe_avg = $ccs['average'];
        if (!empty($metricsResult["diameter"])){
            $graph->component_distr = json_encode($metricsResult["connectedComponent"]);
            $graph->degree_distr = json_encode($metricsResult["degreeDistributions"]);
            $graph->average_distance = $metricsResult["avgDistance"];
            $graph->degree_average = $metricsResult["sumDegree"]/($sizeOfAllUsers);
            $graph->diameter = $metricsResult["diameter"];
            $graph->distance_distr = json_encode($metricsResult["distanceMatrix"]);
        }
        $graph->edge_count = $sizeofEdgeList;
        $graph->node_count = $sizeOfAllUsers;
        return $graph;
    }

    private function part_3($allUsers, $fakeToRealMapping, $ccs, $metricsResult, $groupIdForInsert, $time){
        foreach ($allUsers as $userFakeId){
            $node = new FRMGRAPH_BOL_Node();
            $node->userId = $fakeToRealMapping[$userFakeId];
            if(isset($ccs['cc'][$userFakeId])){
                $node->cluster_coe = $ccs['cc'][$userFakeId];
            }else{
                $node->cluster_coe = null;
            }
            if(isset($metricsResult["eccentricityCentrality"][$userFakeId])){
                $node->eccentricity_cent = $metricsResult["eccentricityCentrality"][$userFakeId];
            }else{
                $node->eccentricity_cent = null;
            }
            if(isset($metricsResult["degreeCentrality"][$userFakeId])){
                $node->degree_cent = $metricsResult["degreeCentrality"][$userFakeId]/(sizeof($allUsers));
            }else{
                $node->degree_cent = null;
            }
            if(isset($metricsResult["closenessCentrality"][$userFakeId])){
                $node->closeness_cent = $metricsResult["closenessCentrality"][$userFakeId];
            }else{
                $node->closeness_cent = null;
            }
            if(isset($metricsResult["betweennessCentrality"][$userFakeId])){
                $node->betweenness_cent = $metricsResult["betweennessCentrality"][$userFakeId];
            }else{
                $node->betweenness_cent = null;
            }
            if(isset($metricsResult["pageRank"][$userFakeId])){
                $node->page_rank = $metricsResult["pageRank"][$userFakeId];
            }else{
                $node->page_rank = null;
            }
            if(isset($metricsResult["hub"][$userFakeId])){
                $node->hub = $metricsResult["hub"][$userFakeId];
            }else{
                $node->hub = null;
            }
            if(isset($metricsResult["authority"][$userFakeId])){
                $node->authority = $metricsResult["authority"][$userFakeId];
            }else{
                $node->authority = null;
            }
            $node->time = $time;
            $node->groupId = $groupIdForInsert;

            $node = $this->calculateUserContentInformation($node);
            $this->nodeDao->save($node);
        }
    }

    public function calculateAllInformation(){
        $statistical = FRMGRAPH_BOL_Statistics::getInstance();
        $time = time();
        $groupIdForInsert = $this->graphDao->getGroupIdForInsert();

        //get all users
        $anonymizingGraph = $this->part_1();

        $allUsers = $anonymizingGraph['allUsers'];
        $fakeToRealMapping = $anonymizingGraph['fakeToRealMapping'];
        $edgeList = $anonymizingGraph['edgeList'];
        unset($anonymizingGraph);

        //calculate metrics
        $ccs = $statistical->calculateClusterCoefficientOfAllNodes($edgeList, $allUsers);
        $metricsResult = $statistical->getServerInformation($edgeList, $allUsers);

        $graph = $this->part_2($ccs, $metricsResult, sizeof($allUsers), sizeof($edgeList));
        $graph->time = $time;
        $graph->groupId = $groupIdForInsert;

        $graph = $this->calculateGraphContentInformation($graph);
        $graph = $this->calculateGroupInformation($graph);
        $this->graphDao->save($graph);

        $this->part_3($allUsers, $fakeToRealMapping, $ccs, $metricsResult, $groupIdForInsert, $time);

        // clear cache
        $this->generateCache();

        return $groupIdForInsert;
    }

    public function generateCache(){
        @unlink(FRMGRAPH_BOL_Service::getTopUsersCacheFilePath());
        return new FRMGRAPH_CMP_AdminAllUsers([]);
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param FRMGRAPH_BOL_Graph $graph
     * @return mixed
     */
    private function calculateGroupInformation($graph){
        if(!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return $graph;
        }
        $graph->g_adjacency_list = json_encode($this->getGroupAdjacencyList());
        $statistical = FRMGRAPH_BOL_Statistics::getInstance();
        $groupsService = GROUPS_BOL_Service::getInstance();
        //get all users

        $groupList = $groupsService->findGroupList(GROUPS_BOL_Service::LIST_ALL);
        $groupIds = array();
        foreach ($groupList as $group){
            if(!in_array($group->id, $groupIds)){
                $groupIds[] = $group->id;
            }
        }

        $anonymizingGraph = $this->anonymizingGraph($this->getAllGroupRelationship(), $groupIds);
        $allGroups = $anonymizingGraph['allUsers'];
        $fakeToRealMapping = $anonymizingGraph['fakeToRealMapping'];
        $edgeList = $anonymizingGraph['edgeList'];
        $time = time();

        //calculate cluster coefficient
        $ccs = $statistical->calculateClusterCoefficientOfAllNodes($edgeList, $allGroups);
        //calculate another metrics
        $metricsResult = $statistical->getServerInformation($edgeList,$allGroups);
        $groupIdForInsert = $this->graphDao->getGroupIdForInsert();

        $graph->g_cluster_coe_avg = $ccs['average'];
        if (!empty($metricsResult["diameter"])){
            $graph->g_component_distr = json_encode($metricsResult["connectedComponent"]);
            $graph->g_degree_distr = json_encode($metricsResult["degreeDistributions"]);
            $graph->g_average_distance = $metricsResult["avgDistance"];
            $graph->g_degree_average = $metricsResult["sumDegree"]/(sizeof($allGroups));
            $graph->g_diameter = $metricsResult["diameter"];
            $graph->g_distance_distr = json_encode($metricsResult["distanceMatrix"]);
        }
        $graph->g_edge_count = sizeof($edgeList);
        $graph->g_node_count = sizeof($allGroups);

        $graph->g_contents_count = $this->groupContentsCount();
        $graph->g_files_count = $this->groupFilesCount();
        $graph->g_users_interactions_count = $this->groupInteractionsCount();
        $graph->g_all_activities_count = $graph->g_users_interactions_count + intval($graph->g_contents_count) + intval($graph->g_files_count);

        foreach ($allGroups as $groupFakeId){
            $group = new FRMGRAPH_BOL_Group();
            $group->gId = $fakeToRealMapping[$groupFakeId];
            $group->users_count = $groupsService->findUserListCount($group->gId);
            if(isset($ccs['cc'][$groupFakeId])){
                $group->cluster_coe = $ccs['cc'][$groupFakeId];
            }else{
                $group->cluster_coe = null;
            }
            if(isset($metricsResult["eccentricityCentrality"][$groupFakeId])){
                $group->eccentricity_cent = $metricsResult["eccentricityCentrality"][$groupFakeId];
            }else{
                $group->eccentricity_cent = null;
            }
            if(isset($metricsResult["degreeCentrality"][$groupFakeId])){
                $group->degree_cent = ($metricsResult["degreeCentrality"][$groupFakeId])/(2*sizeof($allGroups));
            }else{
                $group->degree_cent = null;
            }
            if(isset($metricsResult["closenessCentrality"][$groupFakeId])){
                $group->closeness_cent = $metricsResult["closenessCentrality"][$groupFakeId];
            }else{
                $group->closeness_cent = null;
            }
            if(isset($metricsResult["betweennessCentrality"][$groupFakeId])){
                $group->betweenness_cent = $metricsResult["betweennessCentrality"][$groupFakeId];
            }else{
                $group->betweenness_cent = null;
            }
            if(isset($metricsResult["pageRank"][$groupFakeId])){
                $group->page_rank = $metricsResult["pageRank"][$groupFakeId];
            }else{
                $group->page_rank = null;
            }
            if(isset($metricsResult["hub"][$groupFakeId])){
                $group->hub = $metricsResult["hub"][$groupFakeId];
            }else{
                $group->hub = null;
            }
            if(isset($metricsResult["authority"][$groupFakeId])){
                $group->authority = $metricsResult["authority"][$groupFakeId];
            }else{
                $group->authority = null;
            }
            $group->time = $time;
            $group->groupId = $groupIdForInsert;

            $group->contents_count = $this->groupContentsCount($group->gId);
            $group->files_count = $this->groupFilesCount($group->gId);
            $group->users_interactions_count = $this->groupInteractionsCount($group->gId);
            $group->all_activities_count = $group->contents_count+$group->files_count+$group->users_interactions_count;

            $this->groupDao->save($group);
        }

        return $graph;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param FRMGRAPH_BOL_Graph $graph
     * @return mixed
     */
    private function calculateGraphContentInformation($graph){
        $graph->contents_count = $this->userNewsfeedActionsCount(array('user-status','groups-status','blog-post','forum-topic'));
        $graph->pictures_count = $this->userPicturesCount();
        $graph->videos_count = $this->userVideosCount();
        $graph->news_count = $this->userNewsCount();
        $allContentsCount = intval($graph->contents_count) + intval($graph->pictures_count) + intval($graph->videos_count) + intval($graph->news_count);
        $graph->users_interactions_count = $this->userInteractionsCount();
        $graph->all_activities_count = $allContentsCount + $graph->users_interactions_count;

        return $graph;
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param FRMGRAPH_BOL_NODE $user
     * @return mixed
     */
    public function calculateUserContentInformation($user){
        $user->contents_count = $this->userNewsfeedActionsCount(array('user-status','groups-status','blog-post','forum-topic'),$user->userId);
        $user->pictures_count = $this->userPicturesCount($user->userId);
        $user->videos_count = $this->userVideosCount($user->userId);
        $user->news_count = $this->userNewsCount($user->userId);
        $user->all_contents_count = intval($user->contents_count) + intval($user->pictures_count) + intval($user->videos_count) + intval($user->news_count);
        $user->all_activities_count = $user->all_contents_count + $this->userInteractionsCount($user->userId);
        $user->all_done_activities_count = $this->userDoneActivitiesCount($user->userId);

        $userDoneLikesAndCommentsCount = $this->userDoneAllLikesAndCommentsCount($user->userId);
        $user->all_done_likes_count = $userDoneLikesAndCommentsCount['likes_count'];
        $user->all_done_comments_count = $userDoneLikesAndCommentsCount['comments_count'];

        $userLikesAndCommentsCount = $this->userLikesAndCommentsCount();
        $user->user_all_likes_count = $userLikesAndCommentsCount['likes_count'];
        $user->user_all_comments_count = $userLikesAndCommentsCount['comments_count'];

        return $user;
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param array $entityTypes
     * @param int $userId
     * @return int
     */
    public function userNewsfeedActionsCount($entityTypes, $userId = -1){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return null;
        }

        //$statusList = " ( 'user-status','groups-status','blog-post','forum-topic') ";
        $statusList = ' ( '.OW::getDbo()->mergeInClause($entityTypes).' ) ';
        if($userId>0){
            $query = "SELECT DISTINCT action.* 
                        FROM " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " action
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON action.id=activity.actionId
                        WHERE activity.activityType=:ca AND activity.userId=:u and `entityType` IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'u' => $userId
            ));
        }
        else {
            $query = "SELECT DISTINCT action.* 
                        FROM " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " action
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON action.id=activity.actionId
                        WHERE activity.activityType=:ca and `entityType` IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            ));
        }
        return count($list);
    }

    /***
     * @param int $userId
     * @return array|int|null
     * @throws Redirect404Exception
     */
    public function userPicturesCount($userId = -1){
        if(!FRMSecurityProvider::checkPluginActive('photo', true)) {
            return null;
        }

        if($userId>0){
            return PHOTO_BOL_PhotoDao::getInstance()->countUserPhotos($userId);
        }
        else {
            return PHOTO_BOL_PhotoDao::getInstance()->countAll();
        }
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $userId
     * @return int
     */
    public function userVideosCount($userId = -1){
        if(!FRMSecurityProvider::checkPluginActive('video', true)) {
            return null;
        }

        if($userId>0){
            return VIDEO_BOL_ClipDao::getInstance()->countUserClips($userId);
        }
        else {
            return VIDEO_BOL_ClipDao::getInstance()->countAll();
        }
    }

    /***
     * @param int $userId
     * @return mixed|null
     * @throws Redirect404Exception
     */
    public function userNewsCount($userId = -1){
        if(!FRMSecurityProvider::checkPluginActive('frmnews', true)) {
            return null;
        }

        if($userId>0){
            return EntryDao::getInstance()->countUserEntry($userId);
        }
        else {
            return EntryDao::getInstance()->countEntrys();
        }
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $userId
     * @return int
     */
    public function userInteractionsCount($userId = -1){
        $userLikesAndComments = FRMGRAPH_BOL_Service::getInstance()->userLikesAndCommentsCount($userId);
        $userLikesCount = isset($userLikesAndComments['like_count']) ? $userLikesAndComments['like_count'] : 0;
        $userCommentsCount = isset($userLikesAndComments['comment_count']) ? $userLikesAndComments['comment_count'] : 0;
        return  $userLikesCount + $userCommentsCount;
    }

    /***
     * @param int $userId
     * @return array|null
     * @throws Redirect404Exception
     */
    public function userLikesAndCommentsCount($userId = -1){
        if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return null;
        }

        if ($userId > 0) {
            $ex = new OW_Example();
            $ex->andFieldEqual('userId',$userId);
            $like_count = NEWSFEED_BOL_LikeDao::getInstance()->countByExample($ex);
            $comment_count = BOL_CommentDao::getInstance()->countByExample($ex);
        }
        else {
            $like_count = NEWSFEED_BOL_LikeDao::getInstance()->countAll();
            $comment_count = BOL_CommentDao::getInstance()->countAll();
        }
        return array("likes_count" => $like_count, "comments_count" => $comment_count);
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $userId
     * @return int
     */
    public function userDoneActivitiesCount($userId){
        $userAllDoneActivities = FRMGRAPH_BOL_Service::getInstance()->userDoneAllLikesAndCommentsCount($userId);
        $likes_count = isset($userAllDoneActivities['likes_count']) ? $userAllDoneActivities['likes_count'] : 0;
        $comments_count = isset($userAllDoneActivities ['comments_count']) ? $userAllDoneActivities ['comments_count'] : 0;
        return $likes_count + $comments_count;
    }

    /***
     * @param $userId
     * @return array|null
     * @throws Redirect404Exception
     */
    public function userDoneAllLikesAndCommentsCount($userId){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return null;
        }

        if($userId>0){
            $query = "SELECT DISTINCT likeT.* 
                        FROM " . NEWSFEED_BOL_LikeDao::getInstance()->getTableName() . " likeT
                        , " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actionT
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activityT ON actionT.id=activityT.actionId
                        WHERE
                            likeT.`entityType`=actionT.`entityType` and likeT.`entityId`=actionT.`entityId`
                            and activityT.activityType=:ca AND activityT.userId=:u";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'u' => $userId
            ));
            $likeCount = count($list);

            $query = "SELECT DISTINCT commentT.* 
                        FROM " . BOL_CommentEntityDao::getInstance()->getTableName() . " commentT
                        , " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actionT
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activityT ON actionT.id=activityT.actionId
                        WHERE
                            commentT.`entityType`=actionT.`entityType` and commentT.`entityId`=actionT.`entityId`
                            and activityT.activityType=:ca AND activityT.userId=:u";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'u' => $userId
            ));
            $commentCount = count($list);

            return array("likes_count"=>$likeCount, "comments_count" => $commentCount);
        }
        return null;
    }

    /***
     * @param int $gId
     * @return int|null
     * @throws Redirect404Exception
     */
    public function groupContentsCount($gId = -1){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return null;
        }

        $statusList = " ('groups-status') ";
        if($gId>0){
            $query = "SELECT DISTINCT action.* 
                        FROM " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " action
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON action.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND actionfeed.feedId=:gId AND `entityType` IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'gId' => $gId
            ));
        }
        else {
            $query = "SELECT DISTINCT action.* 
                        FROM " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " action
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON action.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND `entityType` IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            ));
        }
        return count($list);
    }

    /***
     * @param int $gId
     * @return array|mixed|null
     * @throws Redirect404Exception
     */
    public function groupFilesCount($gId = -1){
        if(!FRMSecurityProvider::checkPluginActive('frmgroupsplus', true)) {
            return null;
        }
        if($gId>0){
            return FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->findCountByGroupId($gId);
        }
        else {
            return FRMGROUPSPLUS_BOL_GroupFilesDao::getInstance()->countAll();
        }
    }

    /***
     * @param int $gId
     * @return int|null
     * @throws Redirect404Exception
     */
    public function groupInteractionsCount($gId = -1){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return null;
        }

        $statusList = " ('groups-status','group','groups-join','groups-add-file') ";
        if($gId>0){
            $query = "SELECT DISTINCT likes.*
                        FROM " . NEWSFEED_BOL_LikeDao::getInstance()->getTableName() . " likes
                        INNER JOIN " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actions ON likes.entityId=actions.entityId and likes.entityType=actions.entityType
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON actions.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND actionfeed.feedId=:gId AND actions.entityType IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'gId' => $gId
            ));
            $like_count = count($list);

            $query = "SELECT DISTINCT comments.*
                        FROM " . BOL_CommentDao::getInstance()->getTableName() . " comments
                        INNER JOIN " . BOL_CommentEntityDao::getInstance()->getTableName() . " comments_entity ON comments.commentEntityId=comments_entity.id
                        INNER JOIN " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actions ON comments_entity.entityId=actions.entityId and comments_entity.entityType=actions.entityType
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON actions.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND actionfeed.feedId=:gId AND actions.entityType IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
                'gId' => $gId
            ));
            $comment_count = count($list);
        }
        else {
            $query = "SELECT DISTINCT likes.*
                        FROM " . NEWSFEED_BOL_LikeDao::getInstance()->getTableName() . " likes
                        INNER JOIN " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actions ON likes.entityId=actions.entityId and likes.entityType=actions.entityType
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON actions.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND actions.entityType IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            ));
            $like_count = count($list);

            $query = "SELECT DISTINCT comments.*
                        FROM " . BOL_CommentDao::getInstance()->getTableName() . " comments
                        INNER JOIN " . BOL_CommentEntityDao::getInstance()->getTableName() . " comments_entity ON comments.commentEntityId=comments_entity.id
                        INNER JOIN " . NEWSFEED_BOL_ActionDao::getInstance()->getTableName() . " actions ON comments_entity.entityId=actions.entityId and comments_entity.entityType=actions.entityType
                        INNER JOIN " . NEWSFEED_BOL_ActivityDao::getInstance()->getTableName() . " activity ON actions.id=activity.actionId
                        INNER JOIN " . NEWSFEED_BOL_ActionFeedDao::getInstance()->getTableName() . " actionfeed ON activity.id=actionfeed.activityId
                        WHERE activity.activityType=:ca AND actions.entityType IN $statusList";
            $list = OW::getDbo()->queryForList($query, array(
                'ca' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE
            ));
            $comment_count = count($list);
        }
        return $like_count+$comment_count;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $userId
     * @param int $groupId
     * @return FRMGRAPH_BOL_NODE
     */
    public function getUserDataByGroupId($userId,$groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('groupId', $groupId);
        return $this->nodeDao->findObjectByExample($ex);
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $gId
     * @param int $groupId
     * @return FRMGRAPH_BOL_NODE
     */
    public function getGroupDataByGroupId($gId,$groupId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('gId', $gId);
        $ex->andFieldEqual('groupId', $groupId);
        return $this->groupDao->findObjectByExample($ex);
    }

    public static function getTopUsersCacheFilePath(){
        return OW::getPluginManager()->getPlugin('frmgraph')->getPluginFilesDir() . 'top_users.json';
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $groupId
     * @param array $values
     * @param int $count
     * @param array $roles
     * @param array $profileQuestionFilters
     * @param int $page_number
     * @param boolean $normalized_output
     * @param bool $return_all
     * @return array
     */
    public function getTopUsersByFormula($groupId, $values, $count, $roles = array(),
                                         $profileQuestionFilters = array(), $page_number = 1,
                                         $normalized_output = false, $return_all = false, $use_cached=false)
    {
        $cache_path = self::getTopUsersCacheFilePath();
        if ($use_cached and OW::getStorage()->fileExists($cache_path)){
            $result = json_decode(file_get_contents($cache_path), true);
        }

        if(!isset($result)) {
            $roles_list = "";
            if ($roles != null)
                foreach ($roles as $roleId) {
                    $roles_list .= $roleId . ",";
                }
            $roles_list = substr($roles_list, 0, -1);

            $q_select = 'userId';
            $q_score = '0';
            foreach ($values as $key => $value) {
                $q_select .= ",IFNULL($key, 0) as $key ";
                $q_score .= "+IFNULL($key, 0)*$value";
            }
            $q = "select $q_select,($q_score) as score
            from " . $this->nodeDao->getTableName() . "
            where groupId=$groupId";

            if ($roles_list) {
                $q .= "\nand UserId in(
				SELECT DISTINCT `userId` 
                FROM `" . OW_DB_PREFIX . "base_authorization_user_role`
                WHERE `roleId` in ($roles_list) 
				)";
            }

            if ($profileQuestionFilters != null) {
                foreach ($profileQuestionFilters as $filter_name => $filter_value) {
                    if (isset($filter_value) && $filter_value) {
                        $q .= "\nand UserId in(
                    SELECT DISTINCT `userId` FROM `" . OW_DB_PREFIX . "base_question_data`
                    WHERE `questionName` = '" . $filter_name . "' And `intValue` in(";
                        foreach ($filter_value as $value)
                            $q .= $value . ",";
                        $q = rtrim($q, ',');
                    }
                    $q .= "))";
                }
            }
            $q .= " ORDER BY `score` DESC";
            $result = OW::getDbo()->queryForList($q);

            // normalization
            if ($normalized_output) {
                $max = 0;
                foreach ($result as $item) {
                    if ($max < $item['score'])
                        $max = $item['score'];
                }
                if ($max != 0) {
                    foreach ($result as $item_number => $item) {
                        $result[$item_number]['score'] /= $max;
                    }
                }
            }

            // cache save
            file_put_contents($cache_path, json_encode($result));
        }

        if ($return_all)
            return array('$total_size'=>sizeof($result), '$users'=>$result);
        return array('$total_size'=>sizeof($result), '$users'=>array_slice($result, ($page_number - 1) * $count, $count));
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param int $groupId
     * @param array $values
     * @param int $count
     * @param $page_number
     * @param array $categories
     * @param bool $normalized_output
     * @return array
     */
    public function getTopGroupsByFormula($groupId, $values, $count, $page_number, $categories = array(), $normalized_output = false)
    {
        $categories_list = "";
        foreach ($categories as $categoryId){
            $categories_list .= $categoryId . ",";
        }
        $categories_list = substr($categories_list, 0, -1);

        $q_select = 'gId';
        $q_score = '0';
        foreach ($values as $key=>$value){
            $q_select .= ",IFNULL($key, 0) as $key ";
            $q_score .= "+IFNULL($key, 0)*$value";
        }
        $q = "select $q_select,($q_score) as score
            from ".$this->groupDao->getTableName()."
            where groupId=$groupId";

        if ($categories_list){
            $q .=  "\nand gId in(
				SELECT DISTINCT `groupId` 
                FROM `" .  OW_DB_PREFIX . "frmgroupsplus_group_information`
                WHERE `categoryId` in ($categories_list) 
				)" ;
        }

        $q .= " ORDER BY `score` DESC";
        $result =  OW::getDbo()->queryForList($q);

        if  ($normalized_output){
            $max = 0;
            foreach ($result as $item){
                if ($max < $item['score'])
                    $max = $item['score'];
            }
            if ($max != 0) {
                foreach ($result as $item_number=>$item) {
                    $result[$item_number]['score'] /= $max;
                }
            }
        }

        return array('total_size'=>sizeof($result), 'groups'=>array_slice($result, ($page_number - 1) * $count, $count));
    }

    /***
     * @param $userId
     * @param $depth
     * @return array
     * @throws Redirect404Exception
     */
    public function getUserNetwork($userId, $depth){
        $result = array($userId => array('depth'=>0,'follows'=>array()));
        if (!FRMSecurityProvider::checkPluginActive('newsfeed', true)) {
            return $result;
        }

        $selectedGroupId = $this->getSelectedGroupId();
        $lastData = $this->graphDao->getLastCalculatedMetricsByGroupId($selectedGroupId);
        if(!isset($lastData) || !isset($lastData->adjacency_list) ){
            return $result;
        }
        $userAdjacencyNetwork = $lastData->adjacency_list;
        $userAdjacencyNetworkTmp = json_decode($userAdjacencyNetwork, true);
        $userAdjacencyNetwork = array();
        foreach ($userAdjacencyNetworkTmp as $userArray){
            $userAdjacencyNetwork[$userArray['id']] = $userArray['follows'];
        }

        $lastStepUsers = array($userId);
        for($i=1;$i<=$depth+1;$i++) {
            $curStepUsers = array();
            foreach ($lastStepUsers as $curNodeId) {
                //$curNodeId followings
                $curNodeFollows = $userAdjacencyNetwork[$curNodeId];
                foreach ($curNodeFollows as $newUserId) {
                    if ( !isset($result[$newUserId]) && $i>$depth ){
                        continue;
                    }
                    if ( !isset($result[$newUserId]) ) {
                        $result[$newUserId] = array('depth' => $i, 'follows' => array());
                        $curStepUsers[] = $newUserId;
                    }
                    if( !in_array($newUserId, $result[$curNodeId]['follows']) ) {
                        $result[$curNodeId]['follows'][] = $newUserId;
                    }
                }

                //$curNodeId followers
                $curNodeFollowers = array();
                foreach($userAdjacencyNetwork as $newUserId => $value) {
                    if( in_array($curNodeId, $userAdjacencyNetwork[$newUserId]) ) {
                        $curNodeFollowers[] = $newUserId;
                    }
                }
                foreach ($curNodeFollowers as $newUserId) {
                    if (!isset($result[$newUserId]) && $i > $depth) {
                        continue;
                    }
                    if (!isset($result[$newUserId])) {
                        $result[$newUserId] = array('depth' => $i, 'follows' => array($curNodeId));
                        $curStepUsers[] = $newUserId;
                    }
                    if (!in_array($curNodeId, $result[$newUserId]['follows'])) {
                        $result[$newUserId]['follows'][] = $curNodeId;
                    }
                }
            }
            $lastStepUsers = $curStepUsers;
        }
        return $result;
    }

    /***
     * @param $gId
     * @param $depth
     * @return array
     * @throws Redirect404Exception
     */
    public function getGroupNetwork($gId, $depth){
        $result = array($gId => array('depth'=>0,'follows'=>array()));

        if (!FRMSecurityProvider::checkPluginActive('groups', true)) {
            return $result;
        }

        $selectedGroupId = $this->getSelectedGroupId();
        $lastData = $this->graphDao->getLastCalculatedMetricsByGroupId($selectedGroupId);
        if(!isset($lastData) || !isset($lastData->g_adjacency_list) ){
            return $result;
        }
        $groupAdjacencyNetwork = $lastData->g_adjacency_list;
        $groupAdjacencyNetworkTmp = json_decode($groupAdjacencyNetwork, true);
        $groupAdjacencyNetwork = array();
        foreach ($groupAdjacencyNetworkTmp as $nodeArray){
            $groupAdjacencyNetwork[$nodeArray['id']] = $nodeArray['follows'];
        }

        $lastStepNodes = array($gId);
        for($i=1;$i<=$depth+1;$i++) {
            $curStepNodes = array();
            foreach ($lastStepNodes as $curNodeId) {
                //$curNodeId followings
                $curNodeFollows = $groupAdjacencyNetwork[$curNodeId];
                foreach ($curNodeFollows as $newNodeItem) {
                    $newNodeId = $newNodeItem['id'];
                    if ( !isset($result[$newNodeId]) && $i>$depth ){
                        continue;
                    }
                    if ( !isset($result[$newNodeId]) ) {
                        $result[$newNodeId] = array('depth' => $i, 'follows' => array());
                        $curStepNodes[] = $newNodeId;
                    }
                    if( !in_array($newNodeId, $result[$curNodeId]['follows']) ) {
                        $result[$curNodeId]['follows'][] = $newNodeItem;
                        $curNodeItem = $newNodeItem;
                        $curNodeItem['id'] = $curNodeId;
                        $result[$newNodeId]['follows'][] = $curNodeItem;
                    }
                }
            }
            $lastStepNodes = $curStepNodes;
        }
        return $result;
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @return array
     */
    public function getLatestRunsByTime()
    {
        $ex = new OW_Example();
        $ex->setOrder('time DESC');
        $ex->setLimitClause(0, 50);

        $sql = 'SELECT `id`, `groupId`, `time` FROM ' . $this->graphDao->getTableName() . $ex;

        return OW::getDbo()->queryForObjectList($sql, $this->graphDao->getDtoClassName());
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @return array
     */
    public function getUserAdjacencyList()
    {
        $edgeList = $this->getAllRelationship();
        $nodeIdsInEdgeList = array();
        foreach ($edgeList as $edge){
            if( !isset($nodeIdsInEdgeList[$edge["feedId"]]) ) {
                $nodeIdsInEdgeList[$edge["feedId"]] = array('id'=>$edge["feedId"],'follows'=>array());
            }
            if( !isset($nodeIdsInEdgeList[$edge["userId"]]) ) {
                $nodeIdsInEdgeList[$edge["userId"]] = array('id'=>$edge["userId"],'follows'=>array());
            }
            $nodeIdsInEdgeList[$edge["userId"]]['follows'][] = $edge["feedId"];
        }
        sort($nodeIdsInEdgeList);
        return $nodeIdsInEdgeList;
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @return array
     */
    public function getGroupAdjacencyList()
    {
        $edgeList = $this->getAllGroupRelationship();
        $nodeIdsInEdgeList = array();
        foreach ($edgeList as $edge){
            if( !isset($nodeIdsInEdgeList[$edge["feedId"]]) ) {
                $nodeIdsInEdgeList[$edge["feedId"]] = array('id'=>$edge["feedId"],'follows'=>array());
            }
            if( !isset($nodeIdsInEdgeList[$edge["userId"]]) ) {
                $nodeIdsInEdgeList[$edge["userId"]] = array('id'=>$edge["userId"],'follows'=>array());
            }
            $nodeIdsInEdgeList[$edge["userId"]]['follows'][] = array('id'=>$edge['feedId'],'w'=>$edge['weight']);
        }
        sort($nodeIdsInEdgeList);
        return $nodeIdsInEdgeList;
    }
    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $tableName
     * @param $tableColumn
     * @param $splitValue
     * @param $intValues
     * @return array
     */
    public function getNodeDataForChart($tableName,$tableColumn,$splitValue,$intValues=false)
    {
        $selectedGroupId = $this->getSelectedGroupId();

        if($intValues){
            if($splitValue == 1){
                $groupTitle = "$tableColumn AS range_start, " . $tableColumn;
            }else{
                $groupTitle = "FLOOR($tableColumn/$splitValue)*$splitValue AS range_start, CONCAT(FLOOR($tableColumn/$splitValue)*$splitValue,'-',(FLOOR($tableColumn/$splitValue)+1)*$splitValue-1)";
            }
        }else{
            $groupTitle = "FLOOR($tableColumn/$splitValue) *$splitValue AS range_start, CONCAT(FLOOR($tableColumn/$splitValue)*$splitValue,'-',FLOOR($tableColumn/$splitValue)*$splitValue-0.01)";
        }

        $q = "select groupValue, count(*) as cnt from
            (
                select $tableColumn, $groupTitle as groupValue 
                from ".$tableName."
                where groupId = $selectedGroupId
            ) as t1
            group by groupValue, range_start
            order by range_start
            ;";
        $list = OW::getDbo()->queryForList($q);
        $result = array();
        foreach($list as $row){
            if(isset($row['groupValue'])) {
                $result[$row['groupValue']] = $row['cnt'];
            }
        }
        return $result;
    }

    /***
     *
     * gets a question name and returns number of users according to that question in `base_question_data` table
     *
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $questionName
     * @return array
     */
    public function getUserStatisticsForChart($questionName)
    {
        $q = "SELECT `intValue`, COUNT(*) FROM  `" . OW_DB_PREFIX . "base_question_data`" ." WHERE `questionName` = '" . $questionName
            ."' GROUP BY `intValue`" ;
        $queryResult = OW::getDbo()->queryForList($q);
        $result = array();

        foreach ($queryResult as $res){
            $result[OW::getLanguage()->text('base', 'questions_question_' . $questionName . '_value_' . $res['intValue'])] = $res['COUNT(*)'];
        }
        return $result;
    }


    public function getUserStatusForChart()
    {
        $userBol = BOL_UserService::getInstance();
        $result = array();
        $result[OW::getLanguage()->text('frmgraph', 'chart_label_count')] = $userBol->count();
        $result[OW::getLanguage()->text('frmgraph', 'chart_label_countOnline')] = $userBol->countOnline();
        $result[OW::getLanguage()->text('frmgraph', 'chart_label_countSuspended')] = $userBol->countSuspended();
        $result[OW::getLanguage()->text('frmgraph', 'chart_label_countUnverified')] = $userBol->countUnverified();
        $result[OW::getLanguage()->text('frmgraph', 'chart_label_countUnapproved')] = $userBol->countUnapproved();
        return $result;
    }

    public function getGraphSections($sectionId){
        $sections = array();

        $sections[0] = array(
            'sectionId' => 0,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.graph_analytics.user'),
            'label' => OW::getLanguage()->text('frmgraph','graph_analytics'),
            'iconClass' => "ow_ic_analyze"

        );

        $sections[1] = array(
            'sectionId' => 1,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.user.all_users'),
            'label' => OW::getLanguage()->text('frmgraph','user_analytics'),
            'iconClass' => "ow_ic_single_user"
        );

        $sections[2] = array(
            'sectionId' => 2,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.group.all_groups'),
            'label' => OW::getLanguage()->text('frmgraph','group_analytics'),
            'iconClass' => "ow_ic_group"
        );

        $sections[3] = array(
            'sectionId' => 3,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.graph_view.user'),
            'label' => OW::getLanguage()->text('frmgraph','graph_view'),
            'iconClass' => "ow_ic_my_groups"
        );

        $sections[4] = array(
            'sectionId' => 4,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.graph_statistics.user'),
            'label' => OW::getLanguage()->text('frmgraph','user_statistics'),
            'iconClass' => "ow_ic_statistics"
        );

        $sections[5] = array(
            'sectionId' => 5,
            'active' => false,
            'url' => OW::getRouter()->urlForRoute('frmgraph.users_list'),
            'label' => OW::getLanguage()->text('frmgraph','users_list_label'),
            'iconClass' => "ow_ic_list"
        );

        $sections[$sectionId-1]['active'] = true;

        return $sections;
    }

    public function getGraphSubSections($sectionId, $subsectionId)
    {
        $subsections = array();

        if($sectionId==1) {
            $subsections[0] = array(
                'sectionId' => 0,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.graph_analytics.user'),
                'label' => OW::getLanguage()->text('frmgraph','user_graph'),
                'iconClass' => 'ow_ic_single_user'
            );

            $subsections[1] = array(
                'sectionId' => 1,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.graph_analytics.group'),
                'label' => OW::getLanguage()->text('frmgraph','group_graph'),
                'iconClass' => 'ow_ic_group'
            );
            $subsections[$subsectionId]['active'] = true;
        }
        else if($sectionId==2) {
            $subsections[0] = array(
                'sectionId' => 0,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.user.all_users'),
                'label' => OW::getLanguage()->text('frmgraph','all_users'),
                'iconClass' => 'ow_ic_single_user'
            );

            $subsections[1] = array(
                'sectionId' => 1,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.user.one_user'),
                'label' => OW::getLanguage()->text('frmgraph','one_user'),
                'iconClass' => 'ow_ic_single_user'
            );
            $subsections[$subsectionId]['active'] = true;
        }
        else if($sectionId==3) {
            $subsections[0] = array(
                'sectionId' => 0,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.group.all_groups'),
                'label' => OW::getLanguage()->text('frmgraph','all_groups'),
                'iconClass' => 'ow_ic_group'
            );

            $subsections[1] = array(
                'sectionId' => 1,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.group.one_group'),
                'label' => OW::getLanguage()->text('frmgraph','one_group'),
                'iconClass' => 'ow_ic_group'
            );
            $subsections[$subsectionId]['active'] = true;
        }
        else if($sectionId==4) {
            $subsections[0] = array(
                'sectionId' => 0,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.graph_view.user'),
                'label' => OW::getLanguage()->text('frmgraph','user_graph'),
                'iconClass' => 'ow_ic_single_user'
            );

            $subsections[1] = array(
                'sectionId' => 1,
                'active' => false,
                'url' => OW::getRouter()->urlForRoute('frmgraph.graph_view.group'),
                'label' => OW::getLanguage()->text('frmgraph','group_graph'),
                'iconClass' => 'ow_ic_group'
            );
            $subsections[$subsectionId]['active'] = true;
        }

        return $subsections;
    }

    public function makeDataFromArray($degreeDistributions){
        $degreeDistributionsData = "[";
        if(isset($degreeDistributions)) {
            foreach ($degreeDistributions as $key => $value) {
                $degreeDistributionsData .= "['" . $key . "', $value],";
            }
        }
        $degreeDistributionsData .= "]";
        return $degreeDistributionsData;
    }

    public function makeHighchartDistributionDiagram($title, $idWrapper, $xAxisTitle, $yAxisTitle, $dataName, $data, $type='column',$floatFormat=true){
        if  (!isset($type) || $type =='')
            $type = 'column';
        $chartOptions = "Highcharts.chart('".$idWrapper."', {
                chart: {
                    type: '" . $type ."'
                },
                title: {
                    text: '".$title."'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    labels: {
                        rotation: -45,
                        style: {
                            fontSize: '10px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    },
                    title: {
                        text: '".$xAxisTitle."'
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: '".$yAxisTitle."'
                    }
                },
                legend: {
                    enabled: false
                },
                tooltip: {";
        if ($floatFormat)
            $chartOptions .= "pointFormat: '<b>{point.y:.1f}</b>'";
        else
            $chartOptions .= "pointFormat: '<b>{point.y:f}</b>'";
        $chartOptions .= "},
                series: [{
                    name: '".$dataName."',
                    data: ".$data.",
                    dataLabels: {
                        enabled: true,
                        ";
        if ($type == 'pie')
            $chartOptions .= "rotation: 0,
             color: '#000',";
        else
            $chartOptions .= "rotation: -90,
             color: '#FFFFFF',";

        $chartOptions .= "align: 'right',";
        if ($floatFormat)
            $chartOptions .= "format: '{point.y:.1f}', // one decimal";
        $chartOptions .= " y: 10, // 10 pixels down from the top
                        style: {
                            fontSize: '10px',
                            fontFamily: 'Verdana, sans-serif'
                        }
                    }
                }]
            });";
        return $chartOptions;
    }

    public function checkUserPermission(){
        $haspermission = OW::getUser()->isAuthorized('frmgraph', 'graphshow');
        return $haspermission;
    }
}