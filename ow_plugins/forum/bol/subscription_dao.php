<?php
/**
 * Data Access Object for `forum_subscription` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_SubscriptionDao extends OW_BaseDao
{
    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var FORUM_BOL_SubscriptionDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_SubscriptionDao
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
        return 'FORUM_BOL_Subscription';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_subscription';
    }

    /**
     * Returns list of topic subscribers
     *
     * @param int $topicId
     * @return array
     */
    public function findTopicSubscribers( $topicId )
    {
        $sql = "SELECT `userId` FROM `".$this->getTableName()."` 
            WHERE `topicId`=:topicId";
        
        return $this->dbo->queryForColumnList($sql, array('topicId' => $topicId));
    }
    
    public function addSubscription( FORUM_BOL_Subscription $subscription )
    {
        if (!$this->isUserSubscribed($subscription->userId, $subscription->topicId)) {
            $this->save($subscription);
        }

        return $subscription->id;
    }

    /**
     * @param array $userIds
     * @param int $topicId
     */
    public function addMultipleSubscription( $userIds, $topicId )
    {
        $rowValues = array();
        foreach( $userIds as $userId )
        {
            $rowValues[] = "(". $userId .", ". $topicId .")";
        }
        $sql = "INSERT INTO `".$this->getTableName()."` (`userId`, `topicId`)
        VALUES ". implode(", ",$rowValues);
        $this->dbo->query($sql);
    }

    public function deleteSubscription( $userId, $topicId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('topicId', $topicId);
        
        $this->deleteByExample($example);
    }
    
    public function isUserSubscribed( $userId, $topicId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('topicId', $topicId);
        
        return $this->findObjectByExample($example) ? true : false; 
    }

    public function addSubscriptionForAllGroupTopics( $userId, $groupId )
    {
        $sql = "INSERT INTO `". $this->getTableName() ."` (`userId`, `topicId`)
                SELECT ". $userId .", `t`.`id` AS `topicId`
                FROM `". FORUM_BOL_TopicDao::getInstance()->getTableName() ."` `t`
                WHERE	`t`.`groupId` IN (
                    SELECT `g`.`id`
                    FROM `". FORUM_BOL_GroupDao::getInstance()->getTableName() ."` `g`
                    WHERE `g`.`entityId` = ". $groupId ."
                )";
        $this->dbo->query($sql);
    }

    public function deleteSubscriptionsUsersGroupTopics( $userIds, $groupId )
    {
        $sql = "DELETE FROM `". $this->getTableName() ."` WHERE `ID` IN (
                    SELECT `id` FROM
                        (
                            SELECT `s`.`id`, `s`.`userId`, `s`.`topicId`,`g`.`entityId`
                            FROM `". FORUM_BOL_GroupDao::getInstance()->getTableName()  ."` AS `g`
                            INNER JOIN `". FORUM_BOL_TopicDao::getInstance()->getTableName()  ."` AS `t`
                            ON `g`.`id` = `t`.`groupId`
                            INNER JOIN `".$this->getTableName() ."` AS `s`
                            ON `t`.`id` = `s`.`topicId`
                            WHERE `g`.`entityId` = :g
                            AND `s`.`userId` in (". OW::getDbo()->mergeInClause($userIds) .")
                        ) `a`
                    )";
        $this->dbo->query($sql, array("g" => $groupId));
    }
}