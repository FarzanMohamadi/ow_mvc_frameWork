<?php
/**
 * Data Access Object for `slideshow_slide` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.bol
 * @since 1.4.0
 */
class SLIDESHOW_BOL_SlideDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var SLIDESHOW_BOL_SlideDao
     */
    private static $classInstance;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an instance of class.
     *
     * @return SLIDESHOW_BOL_SlideDao
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
        return 'SLIDESHOW_BOL_Slide';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'slideshow_slide';
    }
    
    /**
     * Returns active slide list for widget
     * 
     * @param string $uniqueName
     */
    public function findListByUniqueName( $uniqueName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('widgetId', $uniqueName);
        $example->andFieldEqual('status', 'active');
        $example->setOrder('`order` ASC');
        
        return $this->findListByExample($example);
    }
    
    /**
     * Returns all slide list for widget
     * 
     * @param string $uniqueName
     */
    public function findAllByUniqueName( $uniqueName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('widgetId', $uniqueName);

        return $this->findListByExample($example);
    }
    
    /**
     * Returns next slide order number
     * 
     * @param string $uniqName
     */
    public function getNextOrder( $uniqName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('widgetId', $uniqName);
        $example->setOrder('`order` DESC');
        $example->setLimitClause(0, 1);
        
        $last = $this->findObjectByExample($example);
        
        return $last ? $last->order + 1 : 1;
    }
    
    /**
     * Returns all slides marked for removal with limit
     * 
     * @param int $limit
     */
    public function getListForRemoval( $limit )
    {
        $example = new OW_Example();
        $example->andFieldEqual('status', 'delete');
        $example->setOrder('`order` ASC');
        $example->setLimitClause(0, $limit);
        
        return $this->findListByExample($example);
    }
}