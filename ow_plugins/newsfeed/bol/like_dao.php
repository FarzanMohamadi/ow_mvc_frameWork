<?php
/**
 * Data access Object for `newsfeed_like` table.
 *
 * @deprecated
 * @package ow_plugins.newsfeed.bol
 * @since 1.0
 */
class NEWSFEED_BOL_LikeDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var NEWSFEED_BOL_LikeDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NEWSFEED_BOL_LikeDao
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
        return 'NEWSFEED_BOL_Like';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_vote';
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::addLike
     * @param $userId
     * @param $entityType
     * @param $entityId
     * @return mixed|NEWSFEED_BOL_Like
     */
    public function addLike($userId, $entityType, $entityId )
    {
        $dto = $this->findLike($userId, $entityType, $entityId);

        if ( $dto !== null )
        {
            return $dto;
        }

        $dto = new NEWSFEED_BOL_Like();
        $dto->entityType = $entityType;
        $dto->entityId = $entityId;
        $dto->userId = $userId;
        $dto->timeStamp = time();

        $this->save($dto);

        return $dto;
    }

    /**
     * @deprecated 
     * @see BOL_VoteDao::findUserVote
     * @param $userId
     * @param $entityType
     * @param $entityId
     * @return mixed
     */
    public function findLike($userId, $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->findObjectByExample($example);
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::removeVote
     * @param $userId
     * @param $entityType
     * @param $entityId
     * @return int
     */
    public function removeLike($userId, $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->deleteByExample($example);
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::deleteUserVotes
     * @param $userId
     * @return int
     */
    public function removeLikesByUserId($userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->deleteByExample($example);
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::deleteEntityItemVotes
     * @param $entityType
     * @param $entityId
     * @return int
     */
    public function deleteByEntity($entityType, $entityId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('entityType', $entityType);

        return $this->deleteByExample($example);
    }

    public function findByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);

        return $this->findListByExample($example);
    }

    /**
     * @deprecated
     * @see BOL_VoteDao::findByEntity
     * @param $entityType
     * @param $entityId
     * @return array
     */
    public function findByEntity($entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findListByExample($example);
    }

    public function findByEntities( $entityTypes, $entityIds )
    {
        if (empty($entityTypes) || empty($entityIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray('entityType', $entityTypes);
        $example->andFieldInArray('entityId', $entityIds);

        return $this->findListByExample($example);
    }

    public function findByEntityList( $entityList )
    {
        if ( empty($entityList) )
        {
            return array();
        }

        $entityListCondition = array();

        foreach ( $entityList as $entity )
        {
            $entityListCondition[] = 'entityType="' . $entity['entityType'] . '" AND entityId="' . $entity['entityId'] . '"';
        }

        $query = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . implode(' OR ', $entityListCondition);

        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }



    public function findCountByEntity( $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->countByExample($example);
    }

    /**
     * get likes with pagination
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLikes($limit, $offset) {
        $query = "SELECT * FROM ". self::getTableName() ." LIMIT :offset, :limit;";
        $result = OW::getDbo()->queryForList($query, ['offset' => $offset, 'limit' => $limit]);
        return $result;
    }

    /**
     * drop table
     */
    public function dropTable() {
        $query = "DROP TABLE " . self::getTableName() ."; ";
        $result = OW::getDbo()->query($query);
        return $result;
    }
}