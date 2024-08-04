<?php
/**
 * User: Hamed Tahmooresi
 * Date: 6/8/2016
 * Time: 4:59 PM
 */
require_once "init.php";

$query = '
SELECT b.`id` FROM ( SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM '.OW_DB_PREFIX.'newsfeed_action action
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity activity ON action.id = activity.actionId INNER JOIN `ow_newsfeed_action_set` cactivity ON action.id = cactivity.actionId
INNER JOIN `ow_base_user` base_user_table_alias ON base_user_table_alias.`id` = `cactivity`.`userId`
LEFT JOIN `ow_base_user_suspend` base_user_suspend_table_alias ON base_user_suspend_table_alias.`userId` = `base_user_table_alias`.`id`
LEFT JOIN '.OW_DB_PREFIX.'newsfeed_action_feed action_feed ON activity.id=action_feed.activityId
LEFT JOIN '.OW_DB_PREFIX.'newsfeed_follow follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
LEFT JOIN '.OW_DB_PREFIX.'newsfeed_activity subscribe ON activity.actionId=subscribe.actionId
WHERE (`base_user_suspend_table_alias`.`id` IS NULL) AND (`base_user_table_alias`.`emailVerify` = 1) AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st
AND ( ( follow.userId=:u AND activity.visibility & :vf ) OR
( activity.userId=:u AND activity.visibility & :va ) OR
( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) OR
(subscribe.activityType=:as AND subscribe.userId=:u) )) b
GROUP BY b.`id` ORDER BY MAX(b.timeStamp) DESC LIMIT 0, 10
';

$query2 = 'SELECT b.`id` FROM ( SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`,
activity.timeStamp FROM '.OW_DB_PREFIX.'newsfeed_action action
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity activity ON action.id = activity.actionId
INNER JOIN `ow_newsfeed_action_set` cactivity ON action.id = cactivity.actionId
INNER JOIN `ow_base_user` base_user_table_alias ON base_user_table_alias.`id` = `cactivity`.`userId`
LEFT JOIN `ow_base_user_suspend` base_user_suspend_table_alias ON base_user_suspend_table_alias.`userId` = `base_user_table_alias`.`id`
INNER JOIN '.OW_DB_PREFIX.'newsfeed_action_feed action_feed ON activity.id=action_feed.activityId
LEFT JOIN '.OW_DB_PREFIX.'newsfeed_follow follow ON action_feed.feedId = follow.feedId AND action_feed.feedType = follow.feedType
WHERE (`base_user_suspend_table_alias`.`id` IS NULL) AND (`base_user_table_alias`.`emailVerify` = 1)
AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND ( ( follow.userId=:u AND activity.visibility & :vf ) )
UNION SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM '.OW_DB_PREFIX.'newsfeed_action action
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity activity ON action.id = activity.actionId INNER JOIN `ow_newsfeed_action_set` cactivity ON action.id = cactivity.actionId
INNER JOIN `ow_base_user` base_user_table_alias ON base_user_table_alias.`id` = `cactivity`.`userId` LEFT JOIN `ow_base_user_suspend` base_user_suspend_table_alias
ON base_user_suspend_table_alias.`userId` = `base_user_table_alias`.`id` WHERE (`base_user_suspend_table_alias`.`id` IS NULL) AND (`base_user_table_alias`.`emailVerify` = 1)
AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st AND ( ( activity.userId=:u AND activity.visibility & :va ) )
UNION SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM '.OW_DB_PREFIX.'newsfeed_action action
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity activity ON action.id = activity.actionId INNER JOIN `ow_newsfeed_action_set` cactivity ON action.id = cactivity.actionId
INNER JOIN `ow_base_user` base_user_table_alias ON base_user_table_alias.`id` = `cactivity`.`userId` LEFT JOIN `ow_base_user_suspend` base_user_suspend_table_alias
ON base_user_suspend_table_alias.`userId` = `base_user_table_alias`.`id` INNER JOIN '.OW_DB_PREFIX.'newsfeed_action_feed action_feed ON activity.id=action_feed.activityId
WHERE (`base_user_suspend_table_alias`.`id` IS NULL) AND (`base_user_table_alias`.`emailVerify` = 1) AND cactivity.userId = :u AND activity.status=:s
AND activity.timeStamp<:st AND ( ( action_feed.feedId=:u AND action_feed.feedType="user" AND activity.visibility & :vfeed ) )
UNION SELECT action.`id`, action.`entityId`, action.`entityType`, action.`pluginKey`, action.`data`, activity.timeStamp FROM '.OW_DB_PREFIX.'newsfeed_action action
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity activity ON action.id = activity.actionId INNER JOIN `ow_newsfeed_action_set` cactivity ON action.id = cactivity.actionId
INNER JOIN `ow_base_user` base_user_table_alias ON base_user_table_alias.`id` = `cactivity`.`userId`
LEFT JOIN `ow_base_user_suspend` base_user_suspend_table_alias ON base_user_suspend_table_alias.`userId` = `base_user_table_alias`.`id`
INNER JOIN '.OW_DB_PREFIX.'newsfeed_activity subscribe ON activity.actionId=subscribe.actionId and subscribe.activityType=:as AND subscribe.userId=:u
WHERE (`base_user_suspend_table_alias`.`id` IS NULL) AND (`base_user_table_alias`.`emailVerify` = 1)
AND cactivity.userId = :u AND activity.status=:s AND activity.timeStamp<:st ) b
GROUP BY b.`id`
ORDER BY MAX(b.timeStamp) DESC LIMIT 0, 10';


$idList = array(
    'u' => 99997,
    'va' => NEWSFEED_BOL_Service::VISIBILITY_AUTHOR,
    'vf' => NEWSFEED_BOL_Service::VISIBILITY_FOLLOW,
    'vfeed' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
    's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
    'st' => empty($startTime) ? time() : $startTime,
    'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
    'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
    'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
);

$res = OW::getDbo()->queryForColumnList($query,$idList);
echo "1111111111111111111111";
$ar1 = array();
foreach ($res as $r){
    echo $r."\n";
    $ar1[] = $r;
}
echo "!!!!!!!!!!!!!!!!!!!!!";
$res2 = OW::getDbo()->queryForColumnList($query2,$idList);
echo "2222222222222222222222";
$ar2 = array();
foreach ($res2 as $r){
    echo $r."\n";
    $ar2[] = $r;
}
echo "!!!!!!!!!!!!!!!!!!!!!";

for ($i=0;$i<count($ar1);$i++){
    if ($ar1[$i]!=$ar2[$i]){
        echo "no! $ar1 = ".$ar1[$i]." but $ar2 = ".$ar2[$i] ;
        exit;
    }
}
echo "\n hast!";
