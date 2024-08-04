<?php
class FRMJCSE_BOL_ArticleDao extends OW_BaseDao{
    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     *
     * Singleton instance.
     *
     * @var FRMJCSE_BOL_ArticleDao
     */
    private static $classInstance;
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMJCSE_BOL_ArticleDao
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
        return 'FRMJCSE_BOL_Article';
    }
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmjcse_article';
    }

    public function getAllDownloadCount(){
        $table_name = $this->getTableName();
        $q = "SELECT SUM(dltimes) FROM `{$table_name}`";
        return ($this->dbo->queryForColumn($q));
    }

    public function getAllViewedCount(){
        $table_name = $this->getTableName();
        $q = "SELECT SUM(views) FROM `{$table_name}`";
        return ($this->dbo->queryForColumn($q));
    }

    public function countPublishedPapers(){
        $ex = new OW_Example();
        $ex->andFieldEqual('active', true);
        $ex->andFieldNotEqual('file', '');
        return ($this->countByExample($ex));
    }
}