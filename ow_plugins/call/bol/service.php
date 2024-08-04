<?php
/**
 * Call service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.call.bol
 * @since 1.0
 */
class CALL_BOL_Service
{

    private $callDao;
    private $callUserDao;

    /**
     * Singleton instance.
     *
     * @var CALL_BOL_Service
     */
    private static $classInstance;

    public const CALL_MODE_VOICE = '';
    public const CALL_MODE_VIDEO = '';
    public const CALL_USER_ROLE_CREATOR = '';
    public const CALL_USER_ROLE_USER = '';


    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return CALL_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        $this->callUserDao = CALL_BOL_CallUserDao::getInstance();
        $this->callDao = CALL_BOL_CallDao::getInstance();
    }

    public function inviteToCall($recipientIds, $mode, $candidate, $offer, $callId = 0)
    {

        // create new call
        $call = new CALL_BOL_Call();
        $call->establishTimestamp = time();
        $call->senderId = OW::getUser()->getId();
        $call->mode = $mode;
        $call->candidate = $candidate;
        $call->offer = $offer;
        $this->callDao->save($call);
        $callId = $call->id;

        $callCreator = new CALL_BOL_CallUser();
        $callCreator->callId = $callId;
        $callCreator->userId = OW::getUser()->getId();
        $callCreator->role = $this::CALL_USER_ROLE_CREATOR;
        $this->callUserDao->save($callCreator);

        foreach ($recipientIds as $recipientId){
            $callUser = new CALL_BOL_CallUser();
            $callUser->callId = $callId;
            $callUser->userId = $recipientId;
            $callUser->role = $this::CALL_USER_ROLE_USER;
            $this->callUserDao->save($callUser);
        }
    }

    public function acceptCall($callId, $candidate, $offer){
        $callUser = new CALL_BOL_CallUser();
        $callUser->callId = $callId;
        $callUser->userId = OW::getUser()->getId();
        $callUser->role = $this::CALL_USER_ROLE_USER;
        $this->callUserDao->save($callUser);

        $call = new CALL_BOL_Call();
        $call->id = $callId;
        $call->candidate = $candidate;
        $call->offer = $offer;
        $this->callDao->save($call);

    }

    public function getUserIdsInCall($callId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('callId', $callId);
        $example->andFieldIsNull('leaveTimestamp');
        return $this->callUserDao->findListByExample($example);
    }

    public function hangoutCall($callId)
    {
        $example = new OW_Example();
        $example->andFieldEqual('callId', $callId);
        $example->andFieldEqual('userId', OW::getUser()->getId());
        $currentCallUser = $this->callUserDao->findListByExample($example);

        $callUser = new CALL_BOL_CallUser();
        $callUser->id = $currentCallUser['id'];
        $callUser->leaveTimestamp = time();
        $this->callUserDao->save($callUser);
    }

}