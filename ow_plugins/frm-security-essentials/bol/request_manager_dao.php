<?php
class FRMSECURITYESSENTIALS_BOL_RequestManagerDao extends OW_BaseDao
{
    const SENDER_ID = 'senderId';
    const RECEIVER_ID = 'receiverId';
    const CODE = 'code';
    const ACTIVITY_TYPE = 'activityType';
    const EXPIRATION_TS = 'expirationTimeStamp';
    const UPDATE_TS = 'updateTimeStamp';

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }

    private static $classInstance;


    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmsecurityessentials_request_manager';
    }

    public function getDtoClassName()
    {
        return 'FRMSECURITYESSENTIALS_BOL_RequestManager';
    }

    /**
     * @param integer $senderId
     * @return FRMSECURITYESSENTIALS_BOL_RequestManager
     */
    public function findBySenderId( $senderId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::SENDER_ID, (int)$senderId);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $code
     * @return FRMSECURITYESSENTIALS_BOL_RequestManager
     */
    public function findByCode( $code )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::CODE, $code);

        return $this->findObjectByExample($example);
    }

    /**
     * @param string $senderId
     * @param string $receiverId
     * @return FRMSECURITYESSENTIALS_BOL_RequestManager
     */
    public function findBySenderIdAndReceiverIdAndActivityType($senderId,$receiverId,$activityType)
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::SENDER_ID, (int)$senderId);
        $example->andFieldEqual(self::RECEIVER_ID, (int)$receiverId);
        $example->andFieldEqual(self::ACTIVITY_TYPE, $activityType);
        return $this->findObjectByExample($example);
    }

    /**
     * @param string $senderId
     * @param string $code
     * @return FRMSECURITYESSENTIALS_BOL_RequestManager
     */
    public function findBySenderIdAndCodeAndActivityType($senderId,$code,$activityType )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::SENDER_ID, (int)$senderId);
        $example->andFieldEqual(self::CODE, $code);
        $example->andFieldEqual(self::ACTIVITY_TYPE, $activityType);
        return $this->findObjectByExample($example);
    }

    public function deleteExpiredRequests()
    {
        $example = new OW_Example();
        $example->andFieldLessOrEqual(self::EXPIRATION_TS, time());
        $this->deleteByExample($example);
    }

}
