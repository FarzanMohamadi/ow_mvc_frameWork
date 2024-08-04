<?php
/**
 * Data Access Object for `base_question_config` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionConfigDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_QuestionDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDataDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_QuestionConfig';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_config';
    }

    /**
     * Returns configs list
     *
     * @return array
     */
    public function getConfigListByPresentation( $presentation )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionPresentation', trim($presentation));

        return $this->findListByExample($example);
    }

    /**
     * Returns configs list
     *
     * @return array
     */
    public function getAllConfigs()
    {
        $example = new OW_Example();
        return $this->findListByExample($example);
    }
}

?>