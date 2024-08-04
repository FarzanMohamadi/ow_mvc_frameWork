<?php
/**
 * Data Access Object for `base_comment_entity` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_CommentEntityDao extends OW_BaseDao
{
    const ENTITY_TYPE = 'entityType';
    const ENTITY_ID = 'entityId';
    const PLUGIN_KEY = 'pluginKey';
    const ACTIVE = 'active';

    /**
     * Singleton instance.
     *
     * @var BOL_CommentEntityDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_CommentEntityDao
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
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_CommentEntity';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_comment_entity';
    }

    /**
     * 
     * @param string $entityType
     * @param integer $entityId
     * @return BOL_CommentEntity
     */
    public function findByEntityTypeAndEntityId( $entityType, $entityId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        $example->andFieldEqual(self::ENTITY_ID, $entityId);

        return $this->findObjectByExample($example);
    }

    public function findCommentedEntityCount( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        return (int) $this->countByExample($example);
    }

    public function deleteByEntityType( $entityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_TYPE, trim($entityType));

        $this->deleteByExample($example);
    }

    public function deleteByPluginKey( $pluginKey )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::PLUGIN_KEY, trim($pluginKey));

        $this->deleteByExample($example);
    }

    
}
