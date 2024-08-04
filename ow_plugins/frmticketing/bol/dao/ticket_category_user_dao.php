<?php
/**
 * FRM Ticketing
 */

/**
 *Data Access Object for `frmticket_orders` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketCategoryUserDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketCategoryUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketCategoryUserDao
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
        return 'FRMTICKETING_BOL_TicketCategoryUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_category_user';
    }

    public function findAllCategoriesWithActiveStatus()
    {
        $sql = "SELECT DISTINCT `categoryId` FROM ". $this->getTableName();
        return $this->dbo->queryForColumnList($sql);
    }

    public function findUsersForCategoriesByStatus($status='active')
    {
        $sql = "SELECT UC.`categoryId`, Category.`title`, UC.`userId` FROM ". $this->getTableName()." AS UC
                LEFT JOIN ".FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName()." AS Category 
                ON Category.`id` = UC.`categoryId`
                WHERE Category.`status` = '".$status."'";
        return $this->dbo->queryForList($sql);
    }

    public function findUsersOfCategory($categoryId)
    {
        $sql = "SELECT UC.`userId` FROM ". $this->getTableName()." AS UC
                LEFT JOIN ".FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName()." AS Category 
                ON Category.`id` = UC.`categoryId`
                WHERE Category.`status` = 'active'
                AND Category.id = ".$categoryId;
        return $this->dbo->queryForList($sql);
    }

    public function findCategoriesOfUser($user)
    {
        $sql = "SELECT DISTINCT UC.`categoryId` FROM ". $this->getTableName()." AS UC
                WHERE userId = ".$user;
        return array_column($this->dbo->queryForList($sql),'categoryId');
    }
}