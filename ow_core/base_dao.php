<?php
/**
 * Base Data Access Object class.
 *
 * @package ow_core
 * @since 1.0
 */
abstract class OW_BaseDao
{
    public abstract function getTableName();

    public abstract function getDtoClassName();
    /**
     *
     * @var OW_Database
     */
    protected $dbo;

    protected function __construct()
    {
        $this->dbo = OW::getDbo();
    }

    /**
     * @param $id
     * @param int $cacheLifeTime
     * @param array $tags
     * @return mixed
     */
    public function findById( $id, $cacheLifeTime = 0, $tags = array() )
    {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `id` = ?';

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array((int) $id), $cacheLifeTime, $tags);
    }

    /**
     * Finds and returns mapped entity list
     *
     * @param array $idList
     * @return array
     */
    public function findByIdList( array $idList, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return array();
        }
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE `id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    public function findListByExample( OW_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT * FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    public function countByExample( OW_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    public function findObjectByExample( OW_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $example->setLimitClause(0, 1);
        $sql = 'SELECT * FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForObject($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns all mapped entries of table
     *
     * @return array
     */
    public function findAll( $cacheLifeTime = 0, $tags = array() )
    {
        $sql = 'SELECT * FROM ' . $this->getTableName();

        return $this->dbo->queryForObjectList($sql, $this->getDtoClassName(), array(), $cacheLifeTime, $tags);
    }

    /**
     * Returns all mapped entries ids of table
     *
     * @return array
     */
    public function findAllIds()
    {
        $example = new OW_Example();
        return $this->findIdListByExample($example);
    }

    /**
     * Returns count of all rows
     *
     * @return array
     */
    public function countAll()
    {
        $sql = 'SELECT COUNT(*) FROM ' . $this->getTableName();

        return $this->dbo->queryForColumn($sql);
    }

    /**
     * Delete entity by id. Returns affected rows
     * @param int $id
     * @return int
     */
    public function deleteById( $id )
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `id` = ?';
            $result = $this->dbo->delete($sql, array($id));
            $this->clearCache();
            return $result;
        }

        return 0;
    }

    /**
     * Deletes list of entities by id list. Returns affected rows
     *
     * @param array $idList
     * @return int
     */
    public function deleteByIdList( array $idList )
    {
        if ( $idList === null || count($idList) === 0 )
        {
            return;
        }
        $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE `id` IN(' . $this->dbo->mergeInClause($idList) . ')';

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    public function deleteByExample( OW_Example $example )
    {
        if ( $example === null || mb_strlen($example->__toString()) === 0 )
        {
            throw new InvalidArgumentException('example must not be null or empty');
        }
        $sql = 'DELETE FROM ' . $this->getTableName() . $example;

        $this->clearCache();

        return $this->dbo->delete($sql);
    }

    /**
     * Saves and updates Entity item
     * throws InvalidArgumentException
     *
     * @param OW_Entity $entity
     * 
     * @throws InvalidArgumentException
     */
    public function save( $entity )
    {
        if ( $entity === null || !($entity instanceof OW_Entity) )
        {
            throw new InvalidArgumentException('Argument must be instance of OW_Entity and cannot be null');
        }
        $event = new OW_Event('frmsecurityessentials.on.check.object.before.save.or.update', array('entity'=>$entity,'entityClass'=>get_class($entity)));
        OW::getEventManager()->trigger($event);
        $entity->id = (int) $entity->id;

        if ( $entity->id > 0 )
        {
            $this->dbo->updateObject($this->getTableName(), $entity);
        }
        else
        {
            $entity->id = NULL;
            $entity->id = $this->dbo->insertObject($this->getTableName(), $entity);
        }

        $this->clearCache();

        return $entity;
    }

    /***
     * @param $entities
     */
    public function batchSave( $entities )
    {
        if ( $entities === null || !($entities[0] instanceof OW_Entity) )
        {
            throw new InvalidArgumentException('Argument must be instance of OW_Entity and cannot be null');
        }
        $event = new OW_Event('frmsecurityessentials.on.check.object.before.save.or.update', array('entity'=>$entities[0],'entityClass'=>get_class($entities[0])));
        OW::getEventManager()->trigger($event);

        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $entities, 10, 1);

        $this->clearCache();
    }

    public function delete( $entity )
    {
        $this->deleteById($entity->id);
        $this->clearCache();
    }

    public function findIdByExample( OW_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $example->setLimitClause(0, 1);
        $sql = 'SELECT `id` FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumn($sql, array(), $cacheLifeTime, $tags);
    }

    public function findIdListByExample( OW_Example $example, $cacheLifeTime = 0, $tags = array() )
    {
        if ( $example === null )
        {
            throw new InvalidArgumentException('argument must not be null');
        }

        $sql = 'SELECT `id` FROM ' . $this->getTableName() . $example;

        return $this->dbo->queryForColumnList($sql, array(), $cacheLifeTime, $tags);
    }

    protected function clearCache()
    {
        $tagsToClear = $this->getClearCacheTags();

        if ( $tagsToClear )
        {
            OW::getCacheManager()->clean($tagsToClear);
        }
    }

    /**
     * @return array
     */
    protected function getClearCacheTags()
    {
        return array();
    }
}
