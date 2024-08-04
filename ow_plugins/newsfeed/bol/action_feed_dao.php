<?php
/**
 * Data Access Object for `newsfeed_action_feed` table.
 *
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_ActionFeedDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_ActionFeedDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_ActionFeedDao
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
        return 'NEWSFEED_BOL_ActionFeed';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'newsfeed_action_feed';
    }

    public function addIfNotExists( NEWSFEED_BOL_ActionFeed $dto )
    {
        $example = new OW_Example();
        $example->andFieldEqual('activityId', $dto->activityId);
        $example->andFieldEqual('feedId', $dto->feedId);
        $example->andFieldEqual('feedType', $dto->feedType);

        $existingDto = $this->findObjectByExample($example);

        if ( $existingDto === null )
        {
            $this->save($dto);
        }
        else
        {
            $dto->id = $existingDto->id;
        }
    }

    public function deleteByFeedAndActivityId( $feedType, $feedId, $activityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('activityId', $activityId);
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', $feedType);

        $this->deleteByExample($example);
    }

    public function deleteByActivityIds( $activityIds )
    {
        if ( empty($activityIds) )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray('activityId', $activityIds);

        $this->deleteByExample($example);
    }
    
    public function findByActivityIds( $activityIds )
    {
        if ( empty($activityIds) )
        {
            return array();
        }
        
        $example = new OW_Example();
        $example->andFieldInArray('activityId', $activityIds);

        return $this->findListByExample($example);
    }
    
    public function findByFeed( $feedType, $feedId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('feedType', $feedType);
        $example->andFieldEqual('feedId', $feedId);
        $example->setOrder('id DESC');
        return $this->findListByExample($example);
    }

    /**
     * @param $feedType
     * @param $feedId
     * @return mixed
     */
    public function findLastActionFeed($feedType, $feedId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('feedType', $feedType);
        $example->andFieldEqual('feedId', $feedId);
        $example->setOrder('id DESC');
        return $this->findObjectByExample($example);
    }

    /***
     * @param $feedId
     * @return mixed|string|null
     */
    public function findActionsCountByFeedId($feedId){
        $example = new OW_Example();
        $example->andFieldEqual('feedId', $feedId);
        $example->andFieldEqual('feedType', 'user');
        $example->setOrder('id DESC');
        return $this->countByExample($example);
    }
}