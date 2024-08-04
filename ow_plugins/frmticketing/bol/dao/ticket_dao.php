<?php
/**
 * FRM Ticketing
 */

/**
 *Data Access Object for `frmticket` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
class FRMTICKETING_BOL_TicketDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketDao
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
        return 'FRMTICKETING_BOL_Ticket';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmticketing_tickets';
    }

    public function deleteTicketCategoryInformation($categoryId)
    {
        $query = "UPDATE ".$this->getTableName()." SET categoryId=NULL WHERE categoryId=:categoryId";
        $this->dbo->query($query, array('categoryId' => $categoryId));
    }

    public function deleteTicketOrderInformation($orderId)
    {
        $query = "UPDATE ".$this->getTableName()." SET orderId=NULL WHERE orderId=:orderId";
        $this->dbo->query($query, array('orderId' => $orderId));
    }

    public function findAllTickets($title,$category,$lock,$orderId,$first,$count)
    {
        $whereClause='WHERE 1=1 ';
        $params=array();
        if(isset($title) && trim($title)!="")
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $whereClause.='AND `t`.`categoryId` = :categoryId';
            $params['categoryId']= $category;
        }
        if(isset($orderId) && trim($orderId)!="")
        {
            $whereClause.=' AND `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }
        if(isset($lock) && trim($lock)!="")
        {
            $whereClause.=' AND `t`.`locked` =:lock';
            $params['lock']= ((int) $lock);
        }
        $params['first']=$first;
        $params['count']=$count;

        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();
        $query = "
            SELECT `t`.`id`,`t`.`ticketTrackingNumber`, `t`.`userId`, `t`.`timeStamp`, `t`.`title`, `t`.`description`, `t`.`addition`, `t`.`categoryId`, `t`.`categoryId`, `t`.`locked`, `c`.`title` as `categoryTitle`, `o`.`title` as `orderTitle`".
            " FROM ".$this->getTableName()." as `t` 
            LEFT JOIN ". $categoryTable. " as `c` ON `t`.`categoryId` = `c`.`id` 
            LEFT JOIN ". $orderTable. " as `o` ON `t`.`orderId` = `o`.`id`".
            $whereClause. " ORDER BY `t`.`timeStamp` DESC LIMIT :first, :count
        ";
        return $this->dbo->queryForList($query, $params);
    }

    public function findAllTicketsCount($title,$category,$lock,$orderId)
    {
        $whereClause='AND 1=1 ';
        $params=array();
        if(isset($title) && trim($title)!="" )
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $whereClause.=' AND `t`.`categoryId` = :categoryId';
            $params['categoryId']= $category;
        }
        if(isset($orderId) && trim($orderId)!="")
        {
            $whereClause.=' AND `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }
        if(isset($lock) && trim($lock)!="")
        {
            $whereClause.=' AND `t`.`locked` =:lock';
            $params['lock']= ((int) $lock);
        }
        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();
         $query = "
        SELECT COUNT(*) FROM ".$this->getTableName()." AS `t` 
        LEFT JOIN ". $categoryTable." as `c` ON `t`.`categoryId` = `c`.`id`  
        LEFT JOIN ". $orderTable." as `o` ON `t`.`orderId` = `o`.`id`".$whereClause;
        return (int)$this->dbo->queryForColumn($query,$params);
    }

    public function findTicketInfoById($ticketId)
    {
        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();
        $query = "
            SELECT `t`.`id`,`t`.`ticketTrackingNumber`, `t`.`userId`, `t`.`timeStamp`, `t`.`title`, `t`.`description`, `t`.`addition`, `t`.`categoryId`, `t`.`categoryId`, `t`.`locked`, `c`.`title` as `categoryTitle`,`c`.`id` as `categoryId`, `o`.`title` as `orderTitle`, `o`.`id` as `orderId` FROM ".$this->getTableName()." as `t` 
            LEFT JOIN ". $categoryTable. " as `c` ON `t`.`categoryId` = `c`.`id` 
            LEFT JOIN ". $orderTable. " as `o` ON `t`.`orderId` = `o`.`id` 
            WHERE `t`.`id`=:ti ORDER BY `t`.`timeStamp`
        ";
        return $this->dbo->queryForRow($query,array(
            'ti' => $ticketId
        ));
    }

    public function findTicketByCategoryList($categories,$title,$category,$lock,$orderId,$first,$count)
    {
        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();

        $whereClause='WHERE t.categoryId IN ('.implode(",", $categories).') ';
        $params=array();
        if(isset($title) && trim($title)!="")
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $whereClause.='AND `t`.`categoryId` = :categoryId';
            $params['categoryId']= $category;
        }
        if(isset($orderId) && trim($orderId)!="")
        {
            $whereClause.=' AND `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }
        if(isset($lock) && trim($lock)!="")
        {
            $whereClause.=' AND `t`.`locked` =:lock';
            $params['lock']= ((int) $lock);
        }

        $params['first'] = $first;
        $params['count'] = $count;

        $query = "SELECT `t`.`id`,`t`.`ticketTrackingNumber`, `t`.`userId`, `t`.`timeStamp`, `t`.`title`, `t`.`description`, `t`.`addition`, `t`.`categoryId`, `t`.`categoryId`, `t`.`locked`, `c`.`title` as `categoryTitle`, `o`.`title` as `orderTitle`".
            " FROM ".$this->getTableName()." as `t`
            LEFT JOIN ". $categoryTable." as `c` ON `t`.`categoryId` = `c`.`id`  
            LEFT JOIN ". $orderTable." as `o` ON `t`.`orderId` = `o`.`id` ".
            $whereClause.
            " ORDER BY `t`.`timeStamp` DESC LIMIT :first, :count";
        return $this->dbo->queryForList($query,$params);
    }

    public function findTicketByCategoryListCount($categories,$title,$category,$lock,$orderId)
    {
        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();

        $whereClause='WHERE t.categoryId IN ('.implode(",", $categories).') ';
        $params=array();
        if(isset($title) && trim($title)!="")
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $whereClause.='AND `t`.`categoryId` = :categoryId';
            $params['categoryId']= $category;
        }
        if(isset($orderId) && trim($orderId)!="")
        {
            $whereClause.=' AND `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }
        if(isset($lock) && trim($lock)!="")
        {
            $whereClause.=' AND `t`.`locked` =:lock';
            $params['lock']= ((int) $lock);
        }

        $query = "SELECT COUNT(*) 
            FROM ".$this->getTableName()." as `t`
            LEFT JOIN ". $categoryTable." as `c` ON `t`.`categoryId` = `c`.`id`  
            LEFT JOIN ". $orderTable." as `o` ON `t`.`orderId` = `o`.`id`".$whereClause;
        return  $this->dbo->queryForColumn($query,$params);
    }

    public function findTicketsByAuthorId($userId,$title,$category,$searchLock,$orderId,$first,$count)
    {
        $whereClause='WHERE 1=1 ';
        $params=array();
        if(isset($title) && trim($title)!="")
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $categoryData = explode('_',$category);
            switch ($categoryData[0])
            {
                case 'category':
                    $whereClause.=' `t`.`categoryId` = :categoryId';
                    $params['categoryId']= $categoryData[1] ;
                    break;
            }
        }
        if(isset($order) && trim($order)!="")
        {
            $whereClause.=' `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }

        if(isset($searchLock) && trim($searchLock)!="")
        {
            $whereClause.='AND `t`.`locked` =:locked';
            $params['locked']= $searchLock ;
        }

        $whereClause.=' AND `t`.userId=:userId';
        $params['userId']=$userId;

        $params['first']=$first;
        $params['count']=$count;

        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();
        $query = "
            SELECT `t`.`id`, `t`.`ticketTrackingNumber`, `t`.`userId`, `t`.`timeStamp`, `t`.`title`, `t`.`description`, `t`.`addition`, `t`.`categoryId`, `t`.`categoryId`, `t`.`locked`, `c`.`title` as `categoryTitle`, `o`.`title` as `orderTitle` 
            FROM ".$this->getTableName()." as `t` 
            LEFT JOIN ". $categoryTable. " as `c` ON `t`.`categoryId` = `c`.`id` 
            LEFT JOIN ". $orderTable. " as `o` ON `t`.`orderId` = `o`.`id` "
            .$whereClause. " ORDER BY `t`.`timeStamp` DESC LIMIT :first, :count
        ";
        return $this->dbo->queryForList($query, $params);

    }

    public function findTicketsByUserIdCount($userId,$title,$category,$orderId)
    {
        $whereClause='AND 1=1 ';
        $params=array();
        if(isset($title) && trim($title)!="" )
        {
            $whereClause.=' AND UPPER(`t`.`title`) like UPPER (:searchTitle)';
            $params['searchTitle']= '%'. $title . '%';
        }
        if(isset($category) && trim($category)!="")
        {
            $categoryData = explode('_',$category);
            switch ($categoryData[0])
            {
                case 'category':
                    $whereClause.=' `t`.`categoryId` = :categoryId';
                    $params['categoryId']= $categoryData[1] ;
                    break;
            }
        }
        if(isset($order) && trim($order)!="")
        {
            $whereClause.=' `t`.`orderId` =:orderId';
            $params['orderId']= $orderId ;
        }
        $whereClause.=' AND `t`.userId=:userId';
        $params['userId']=$userId;

        $categoryTable = FRMTICKETING_BOL_TicketCategoryDao::getInstance()->getTableName();
        $orderTable = FRMTICKETING_BOL_TicketOrderDao::getInstance()->getTableName();
        $query = "
        SELECT COUNT(*) FROM ".$this->getTableName()." as `t` 
        LEFT JOIN ". $categoryTable. " as `c` ON `t`.`categoryId` = `c`.`id`  
        LEFT JOIN ". $orderTable. " as `o` ON `t`.`orderId` = `o`.`id` ".$whereClause;
        return (int)$this->dbo->queryForColumn($query,$params);
    }

}