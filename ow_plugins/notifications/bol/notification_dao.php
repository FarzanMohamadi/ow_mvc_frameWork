<?php
/**
 * Data Access Object for `notifications_notification` table.
 *
 * @package notifications.bol
 * @since 1.0
 */
class NOTIFICATIONS_BOL_NotificationDao extends OW_BaseDao
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
     * @var NOTIFICATIONS_BOL_NotificationDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return NOTIFICATIONS_BOL_NotificationDao
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
        return 'NOTIFICATIONS_BOL_Notification';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'notifications_notification';
    }

    public function findNotificationList( $userId, $beforeStamp, $ignoreIds, $count )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('active', 1);
        $example->andFieldLessOrEqual('timeStamp', $beforeStamp);

        if ( !empty($ignoreIds) )
        {
            $example->andFieldNotInArray('id', $ignoreIds);
        }

        $example->setLimitClause(0, $count);
        $example->setOrder('viewed, timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findNewNotificationList( $userId, $afterStamp = null )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('active', 1);
        $example->andFieldEqual('viewed', false);
        // TODO: uncomment
        if ( $afterStamp )
        {
            $example->andFieldGreaterThan('timeStamp', $afterStamp);
        }
        $example->setOrder('timeStamp DESC');

        return $this->findListByExample($example);
    }

    public function findNewNotificationCount( $userId, $afterStamp = null )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('viewed', false);
        // TODO: uncomment
        if ( $afterStamp )
        {
            $example->andFieldGreaterThan('timeStamp', $afterStamp);
        }
        $example->setOrder('timeStamp DESC');

        return $this->countByExample($example);
    }

    public function findNotificationListForSend( $userIdList )
    {
        if ( empty($userIdList) )
        {
            return array();
        }

        $example = new OW_Example();

        $example->andFieldInArray('userId', $userIdList);
        $example->andFieldEqual('viewed', 0);
        $example->andFieldEqual('sent', 0);

        return $this->findListByExample($example);
    }

    public function findNotificationCount( $userId, $viewed = null, $exclude = null )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('active', 1);

        if ( $viewed !== null )
        {
            $example->andFieldEqual('viewed', (int) (bool) $viewed);
        }

        if ( $exclude )
        {
            $example->andFieldNotInArray('id', $exclude);
        }

        return $this->countByExample($example);
    }

    public function findIgnoreNotifications()
    {
        $userId = OW::getUser()->getId();
        $ignoreTypeArr = array('user_invitation', 'event_invitation', 'friendship');
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldInArray('entityType', $ignoreTypeArr);
        return $this->findIdListByExample($example);
    }

    public function findNotification( $entityType, $entityId, $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findObjectByExample($example);
    }

    public function findNotificationsByUserIds( $entityType, $entityId, $userIds )
    {
        if (empty($userIds)) {
            return array();
        }
        $example = new OW_Example();

        $example->andFieldInArray('userId', $userIds);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        return $this->findListByExample($example);
    }

    public function findNotificationsByEntityIds( $entityType, $entityIds, $userId )
    {
        if (empty($entityIds)) {
            return array();
        }
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldInArray('entityId', $entityIds);

        return $this->findListByExample($example);
    }

    public function markViewedByIds( array $ids, $viewed = true )
    {
        if ( empty($ids) )
        {
            return;
        }

        $in = implode(',', $ids);

        $query = "UPDATE " . $this->getTableName() . " SET `viewed`=:viewed WHERE id IN ( " . $in . " )";

        $this->dbo->query($query, array(
            'viewed' => $viewed ? 1 : 0
        ));
    }

    public function markViewedByUserId( $userId, $viewed = true )
    {
        if ( !$userId )
        {
            return;
        }

        $query = "UPDATE " . $this->getTableName() . " SET `viewed` = :viewed WHERE userId = :userId";

        $this->dbo->query($query, array('viewed' => $viewed ? 1 : 0, 'userId' => $userId));
    }

    public function markSentByIds( array $ids, $sent = true )
    {
        if ( empty($ids) )
        {
            return;
        }

        $in = implode(',', $ids);

        $query = "UPDATE " . $this->getTableName() . " SET `sent`=:sent WHERE id IN ( " . $in . " )";

        $this->dbo->query($query, array(
            'sent' => $sent ? 1 : 0
        ));
    }

    /***
     * Hide notification permanently
     * @param $id
     * @return int
     */
    public function hideNotificationById( $id )
    {
        $query = "UPDATE " . $this->getTableName() . " SET `active`=0 WHERE id = :id ;";

        return $this->dbo->query($query, array(
            'id' => $id
        ));
    }

    public function saveNotification( NOTIFICATIONS_BOL_Notification $notification, $cache = array())
    {
        $this->save($notification);
        return $notification;
    }

    public function deleteExpired()
    {
        $example = new OW_Example();
        $example->andFieldEqual('viewed', 1);
        $time = time() - 24 * 3600 * OW::getConfig()->getValue('notifications','delete_days_for_viewed', 7);
        $example->andFieldLessThan('timeStamp', $time);
        $this->deleteByExample($example);


        $example = new OW_Example();
        $example->andFieldEqual('viewed', 0);
        $time = time() - 24 * 3600 * OW::getConfig()->getValue('notifications','delete_days_for_not_viewed', 7);
        $example->andFieldLessThan('timeStamp', $time);
        $this->deleteByExample($example);
    }

    /**
     * Deletes list of entities by id list. Returns affected rows
     *
     * @param array $idList
     * @return int
     */
    public function deleteByIdList( array $idList )
    {
        $query = "SELECT id, userId, entityType, entityId, pluginKey, `action` FROM " . $this->getTableName() . " WHERE `id` IN( " . $this->dbo->mergeInClause($idList) . ")";
        $deletedNotifications = $this->dbo->queryForList($query);
        foreach ($deletedNotifications as $item){
            OW::getLogger()->writeLog(OW_Log::INFO, 'notification_removed', ['actionType'=>OW_Log::DELETE, 'enType'=>'notification', 'enId'=>$item['id'], 'data'=>$item]);
        }
        return parent::deleteByIdList($idList);
    }

    public function deleteNotification( $entityType, $entityId, $userId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $deletedNotifications = $this->findListByExample($example);
        foreach ($deletedNotifications as $item){
            $data = (array)$item;
            unset($data['data']);
            OW::getLogger()->writeLog(OW_Log::INFO, 'notification_removed', ['actionType'=>OW_Log::DELETE, 'enType'=>'notification', 'enId'=>$data['id'], 'data'=>$data]);
        }

        $this->deleteByExample($example);
    }

    /**
     * @param $entityType
     * @param $entityId
     * @param $userIds
     */
    public function deleteNotificationByUniqueKeys( $entityType, $entityId, $userIds )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldInArray('userId',$userIds);
        $this->deleteByExample($example);
    }
    public function deleteNotificationByEntity( $entityType, $entityId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);

        $deletedNotifications = $this->findListByExample($example);
        foreach ($deletedNotifications as $item){
            $data = (array)$item;
            unset($data['data']);
            OW::getLogger()->writeLog(OW_Log::INFO, 'notification_removed', ['actionType'=>OW_Log::DELETE, 'enType'=>'notification', 'enId'=>$data['id'], 'data'=>$data]);
        }

        $this->deleteByExample($example);
    }

    public function deleteNotificationByEntityAndAction( $entityType, $entityId, $action )
    {
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);
        $example->andFieldEqual('action', $action);

        $deletedNotifications = $this->findListByExample($example);
        foreach ($deletedNotifications as $item){
            $data = (array)$item;
            unset($data['data']);
            OW::getLogger()->writeLog(OW_Log::INFO, 'notification_removed', ['actionType'=>OW_Log::DELETE, 'enType'=>'notification', 'enId'=>$data['id'], 'data'=>$data]);
        }

        $this->deleteByExample($example);
    }

    public function deleteCorruptedNotificationData($first, $count) {
        $notificationIds = $this->findLatestNotifications($first, $count);
        if (is_array($notificationIds)) {
            $corruptedIds = array();
            foreach ($notificationIds as $notificationId) {
                $notification = $this->findById($notificationId);
                $info = null;
                try {
                    $info = $notification->getData();
                } catch (Exception $e) {
                    $corruptedIds[] = $notificationId;
                }
                if ($info == false || $info == null) {
                    $corruptedIds[] = $notification->id;
                }
                $info = (array) $info;
                if (isset($info['cache'])) {
                    unset($info['cache']);
                    $notification->setData(json_encode($info));
                    NOTIFICATIONS_BOL_NotificationDao::getInstance()->save($notification);
                }
            }
            if (sizeof($corruptedIds) > 0) {
                $this->deleteByIdList($corruptedIds);
            }
        }
    }

    public function findLatestNotifications( $first, $count )
    {
        $query = 'select id from ' . $this->getTableName() . ' order by id desc limit ' . $first . ', ' . $count;
        return $this->dbo->queryForColumnList($query);
    }

    public function deleteNotificationByPluginKey( $pluginKey )
    {
        $example = new OW_Example();

        $example->andFieldEqual('pluginKey', $pluginKey);

        $this->deleteByExample($example);
    }
    public function updateNotification($entityType,$entityId,$userId,$data){
        $example = new OW_Example();

        $example->andFieldEqual('entityType', $entityType);
        $example->andFieldEqual('entityId', $entityId);
        if ( $userId !== null ){
            $example->andFieldEqual('userId', $userId);
        }
        $notifications = $this->findListByExample($example);
        foreach ($notifications as $notification){
            $notification->setData($data);
            $notification->timeStamp = time();
            $this->save($notification);
        }
    }
    public function setNotificationStatusByPluginKey( $pluginKey, $status )
    {
        $query = "UPDATE " . $this->getTableName() . " SET `active`=:s WHERE pluginKey=:pk";

        $this->dbo->query($query, array(
            's' => (int) $status,
            'pk' => $pluginKey
        ));
    }

    public function getLastViewedNotificationId($userId){
        $query = "SELECT MAX(id) FROM " . $this->getTableName() . " WHERE `viewed`=1 and `userId` = :userId";
        $res = $this->dbo->queryForColumn($query, array('userId'=>$userId));
        if(empty($res)){
            $res = 0;
        }
        return $res;
    }

    public function getLastViewedNotificationIdByUserIds($userIds){
        if (empty($userIds)) {
            return array();
        }
        $query = "SELECT MAX(id) as id, userId FROM " . $this->getTableName() . " WHERE `viewed`=1 and `userId` in (" . $this->dbo->mergeInClause($userIds) .") group by userId";
        $res = $this->dbo->queryForList($query);
        $data = array();
        foreach ($res as $item) {
            $data[$item['userId']] = $item['id'];
        }
        foreach ($userIds as $userId) {
            if (!isset($data[$userId])) {
                $data[$userId] = 0;
            }
        }
        return $data;
    }

}