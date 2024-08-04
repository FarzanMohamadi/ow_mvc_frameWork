<?php
/**
 * Data Access Object for `frmeventplus_information table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmeventplus.bol
 * @since 1.0
 */
class FRMEVENTPLUS_BOL_EventInformationDao extends OW_BaseDao
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
     * @var FRMEVENTPLUS_BOL_EventInformationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMEVENTPLUS_BOL_EventInformationDao
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
        return 'FRMEVENTPLUS_BOL_EventInformation';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmeventplus_event_information';
    }

    public function getEventInformationByCategoryId($categoryId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('categoryId', $categoryId);
        return $this->findListByExample($example);
    }
    public function addCategoryToEvent($eventId,$categoryId)
    {
        $eventInfo = new FRMEVENTPLUS_BOL_EventInformation();
        $this->deleteByEventId($eventId);
        if($categoryId!=null) {
            $eventInfo->setCategoryId($categoryId);
            $eventInfo->setEventId($eventId);
            $this->save($eventInfo);
        }
    }

    public function deleteByCategoryId( $categoryId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        return $this->deleteByExample($ex);
    }

    /***
     * @param $eventId
     * @param $userId
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * user can leave an event except owner
     */
    public function leaveUserFromEvent($eventId,$userId){
        $eventId = (int) $eventId;
        $userId = (int) $userId;
        if ( $eventId > 0  && $userId > 0 )
        {
            $sql = 'DELETE FROM ' . OW_DB_PREFIX . 'event_user WHERE `eventId` = ? AND `userId` = ?';
            $this->dbo->delete($sql, array($eventId,$userId));
        }
    }

    public function deleteByEventId( $eventId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('eventId', $eventId);
        return $this->deleteByExample($ex);
    }

    public function getEventInformationByEventId($eventId)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('eventId', $eventId);
        return $this->findObjectByExample($ex);
    }
}