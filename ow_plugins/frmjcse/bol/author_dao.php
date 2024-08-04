<?php
class FRMJCSE_BOL_AuthorDao extends OW_BaseDao{
    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var FRMJCSE_BOL_KeywordDao
     */
    private static $classInstance;
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMJCSE_BOL_KeywordDao
     */
    public static function getInstance()
    {
        if(self::$classInstance===null)
        {
            self::$classInstance=new self();
        }
        return self::$classInstance;
    }
    public function getDtoClassName()
    {
        return 'FRMJCSE_BOL_Author';
    }
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmjcse_author';
    }
}