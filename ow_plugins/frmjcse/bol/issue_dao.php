<?php
class FRMJCSE_BOL_IssueDao extends OW_BaseDao{
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
     * @var FRMJCSE_BOL_IssueDao
     */
    private static $classInstance;
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMJCSE_BOL_IssueDao
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
        return 'FRMJCSE_BOL_Issue';
    }
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmjcse_issue';
    }

    public function countPublishedIssues(){
        $ex = new OW_Example();
        $ex->andFieldNotEqual('volume', 1000);
        return ($this->countByExample($ex));
    }
}