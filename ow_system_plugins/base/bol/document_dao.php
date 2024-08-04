<?php
/**
 * Data Access Object for `document` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_DocumentDao extends OW_BaseDao
{
    const KEY = 'key';
    const CLAS_S = 'class';
    const ACTION = 'action';
    const URI = 'uri';
    const IS_STATIC = 'isStatic';
    const IS_MOBILE = 'isMobile';

    /**
     * Singleton instance.
     *
     * @var BOL_DocumentDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_DocumentDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

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
        return 'BOL_Document';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_document';
    }

    /**
     * Returns static document for provided `uri`.
     *
     * @param string $uri
     * @return BOL_Document
     */
    public function findStaticDocument( $uri )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::URI, $uri)->andFieldEqual(self::IS_STATIC, true);
        return $this->findObjectByExample($example);
    }

    /**
     * Returns all active static documents.
     *
     * @return array<BOL_Document>
     */
    public function findAllStaticDocuments()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_STATIC, true);
        $example->andFieldEqual(self::IS_MOBILE, false);
        
        return $this->findListByExample($example);
    }

    /**
     * Returns all active static documents.
     *
     * @return array<BOL_Document>
     */
    public function findAllMobileStaticDocuments()
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::IS_STATIC, true);
        $example->andFieldEqual(self::IS_MOBILE, true);

        return $this->findListByExample($example);
    }

    /**
     * Returns document object for provided controller and action.
     *
     * @param string $controller
     * @param string $action
     * @return BOL_Document
     */
    public function findDocumentByDispatchAttrs( $controller, $action )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::CLAS_S, $controller);

        if ( $action === null )
            $example->andFieldIsNull(self::ACTION);
        else
            $example->andFieldEqual(self::ACTION, $action);

        return $this->findObjectByExample($example);
    }

    public function findDocumentByKey( $key )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::KEY, $key);

        return $this->findObjectByExample($example);
    }

    public function isDocumentUriUnique( $uri )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('uri', $uri);
        $ex->setLimitClause(0, 1);

        return $this->findObjectByExample($ex) === null;
    }
}
