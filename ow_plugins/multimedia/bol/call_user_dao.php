<?php
/**
 * Data Access Object for `call_call_user` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.call.bol
 * @since 1.0
 */
class MULTIMEDIA_BOL_CallUserDao extends OW_BaseDao
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
     * @var MULTIMEDIA_BOL_CallUserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return MULTIMEDIA_BOL_CallUserDao
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
        return 'MULTIMEDIA_BOL_CallUser';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'multimedia_call_user';
    }

    /***
     * @param $callId
     * @return array|string
     */
    public function getUserIdsInCall($callId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('callId', $callId);
        $example->andFieldIsNull('leaveTimestamp');
        $callUsers = $this->findListByExample($example);
        $userIds = [];
        foreach ($callUsers as $callUser){
            $userIds[] = $callUser->userId;
        }
        return array_unique($userIds);
    }

    /***
     * @param $callId
     * @return void
     */
    public function hangoutCallSave($callId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('callId', $callId);
        $example->andFieldEqual('userId', OW::getUser()->getId());
        $currentCallUser = $this->findListByExample($example);

        $callUser = new MULTIMEDIA_BOL_CallUser();
        $callUser->id = $currentCallUser['id'];
        $callUser->leaveTimestamp = time();
        $this->save($callUser);
    }

    public function joinUserToCall( $callId, $userId )
    {
        $query = 'UPDATE ' . $this->getTableName() . ' SET joinTimestamp=:time WHERE userId=:user AND callId=:call';

        $this->dbo->query($query, array(
            'time' => time(),
            'call' => $callId,
            'user' => $userId
        ));
    }
}