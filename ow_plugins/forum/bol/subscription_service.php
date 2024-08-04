<?php
/**
 * Forum Subscription Service Class
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
final class FORUM_BOL_SubscriptionService
{
    /**
     * @var FORUM_BOL_SubscriptionService
     */
    private static $classInstance;
    /**
     * @var FORUM_BOL_SubscriptionDao
     */
    private $subscriptionDao;

    /**
     * Class constructor
     */
    private function __construct()
    {
        $this->subscriptionDao = FORUM_BOL_SubscriptionDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_SubscriptionService
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }
    
    public function findTopicSubscribers( $topicId )
    {
        return $this->subscriptionDao->findTopicSubscribers($topicId);
    }
    
    public function addSubscription( FORUM_BOL_Subscription $subscription )
    {
        return $this->subscriptionDao->addSubscription($subscription);
    }
    
    public function deleteSubscription( $userId, $topicId )
    {
        $this->subscriptionDao->deleteSubscription($userId, $topicId);
    }
    
    public function isUserSubscribed( $userId, $topicId )
    {
        return $this->subscriptionDao->isUserSubscribed($userId, $topicId);
    }

    public function addMultipleSubscription( $userIds, $topicId )
    {
        if(count($userIds) > 0) {
            $this->subscriptionDao->addMultipleSubscription($userIds, $topicId);
        }
    }

    public function addSubscriptionForAllGroupTopics( $userId, $groupId )
    {
        $this->subscriptionDao->addSubscriptionForAllGroupTopics($userId, $groupId);
    }
    public function unsubscribeUsersFromGroupTopics( $userIds, $groupId )
    {
        $this->subscriptionDao->deleteSubscriptionsUsersGroupTopics($userIds, $groupId);
    }
}