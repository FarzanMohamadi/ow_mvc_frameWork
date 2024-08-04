<?php
/**
 * Data Access Object for `newsfeed_action_set` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_ActionSetDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_ActionSetDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_ActionSetDao
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
        return 'NEWSFEED_BOL_ActionSetDao';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'newsfeed_action_set';
    }

    public function getActionUserActionIdList($userId, $startTime = null){
        $followDao = NEWSFEED_BOL_FollowDao::getInstance();
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $query = 'WITH actionSet AS ( select distinct actionId, userId from (
                SELECT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                INNER JOIN ' . $followDao->getTableName() . ' sfollow ON saction_feed.feedId = sfollow.feedId AND saction_feed.feedType = sfollow.feedType
                WHERE sactivity.status=:s AND sactivity.activityType=:ac AND sactivity.timeStamp<:st AND
                        sfollow.userId=:u AND
                        ( sactivity.privacy=sfollow.permission OR sactivity.privacy=:peb)
                        AND sactivity.visibility & :vf
                UNION

                SELECT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND cactivity.visibility & :va
                    AND sactivity.userId=:u
                    AND sactivity.visibility & :va
                    AND cactivity.status=:s

                UNION

                SELECT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND sactivity.visibility & :vfeed
                    AND saction_feed.feedId=:u
                    AND saction_feed.feedType="user"
                    AND cactivity.status=:s) as actionSetUnions )';
        $params =  array(
            'u' => (int)$userId,
            'va' => NEWSFEED_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => NEWSFEED_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        );
        return array('query' => $query, 'params' => $params, 'tableName' => 'actionSet');
    }

    /**
     * @param int $userId
     * @param int $startTime
     */
    public function generateActionSet( $userId, $startTime = null )
    {
        $followDao = NEWSFEED_BOL_FollowDao::getInstance();
        $actionFeedDao = NEWSFEED_BOL_ActionFeedDao::getInstance();
        $activityDao = NEWSFEED_BOL_ActivityDao::getInstance();

        $query = ' REPLACE INTO '. $this->getTableName() . ' ( `actionId`, `userId`, `timestamp` )
                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                INNER JOIN ' . $followDao->getTableName() . ' sfollow ON saction_feed.feedId = sfollow.feedId AND saction_feed.feedType = sfollow.feedType
                WHERE sactivity.status=:s AND sactivity.activityType=:ac AND sactivity.timeStamp<:st AND
                        sfollow.userId=:u AND
                        ( sactivity.privacy=sfollow.permission OR sactivity.privacy=:peb)
                        AND sactivity.visibility & :vf
                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND cactivity.visibility & :va
                    AND sactivity.userId=:u
                    AND sactivity.visibility & :va
                    AND cactivity.status=:s

                UNION

                SELECT DISTINCT sactivity.actionId, :u as `userId`, :st FROM ' . $activityDao->getTableName() . ' sactivity
                    INNER JOIN ' . $actionFeedDao->getTableName() . ' saction_feed ON sactivity.id=saction_feed.activityId
                    INNER JOIN ' . $activityDao->getTableName() . ' cactivity ON sactivity.actionId = cactivity.actionId
                WHERE sactivity.status=:s AND sactivity.timeStamp<:st
                    AND cactivity.activityType=:ac
                    AND sactivity.visibility & :vfeed
                    AND saction_feed.feedId=:u
                    AND saction_feed.feedType="user"
                    AND cactivity.status=:s';

        $this->dbo->update($query, array(
            'u' => (int)$userId,
            'va' => NEWSFEED_BOL_Service::VISIBILITY_AUTHOR,
            'vf' => NEWSFEED_BOL_Service::VISIBILITY_FOLLOW,
            'vfeed' => NEWSFEED_BOL_Service::VISIBILITY_FEED,
            's' => NEWSFEED_BOL_Service::ACTION_STATUS_ACTIVE,
            'st' => empty($startTime) ? time() : $startTime,
            'peb' => NEWSFEED_BOL_Service::PRIVACY_EVERYBODY,
            'ac' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_CREATE,
            'as' => NEWSFEED_BOL_Service::SYSTEM_ACTIVITY_SUBSCRIBE
        ));
    }

    /*
     * @param int $userId
     */
    public function deleteActionSetUserId( $userId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', (int)$userId);

        $this->deleteByExample($ex);
    }

    /**
     * @param int $startTime
     */
    public function deleteActionSetByTimestamp( $timestamp )
    {
        $ex = new OW_Example();
        $ex->andFieldLessOrEqual('timestamp', (int)$timestamp);

        $this->deleteByExample($ex);
    }
}