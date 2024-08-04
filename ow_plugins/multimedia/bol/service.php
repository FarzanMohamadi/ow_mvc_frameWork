<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class MULTIMEDIA_BOL_Service
{
    private static $classInstance;
    public const CALL_MODE_VOICE = 'voice';
    public const CALL_MODE_VIDEO = 'video';
    public const CALL_USER_ROLE_CREATOR = 2;
    public const CALL_USER_ROLE_ADMIN = 1;
    public const CALL_USER_ROLE_USER = 0;

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
        $this->callUserDao = MULTIMEDIA_BOL_CallUserDao::getInstance();
        $this->callDao = MULTIMEDIA_BOL_CallDao::getInstance();
    }

    /***
     * @param $params
     * @return OW_Entity|void
     */
    public function callActionController($params){

        if(!isset($params['subType'])){
            return;
        }
        switch ($params['subType']) {
            case 'invite':
                return $this->inviteToCall($params);
            case 'accept':
                return $this->acceptCall($params);
            case 'dismiss':
                return $this->dismissCall($params);
            case 'hangout':
                return $this->hangoutCall($params);
            default:
                return $this->notAcceptCall($params);
        }
    }

    // voice and video call
    /***
     * @param $params
     * @return OW_Entity|void
     */
    public function inviteToCall( $params ) {
        if (!isset($params['opponentIds']) || $params['opponentIds'] == null) {
            return;
        }
        if(!isset($params['candidate']) || !isset($params['offer']) || !isset($params['callMode'])) {
            return;
        }

        $callMode = $params['callMode'];
        $opponentIds = $params['opponentIds'];
        $candidate = $params['candidate'];
        $offer = $params['offer'];
        $callId = $params['callId'] ?? 0;
        $userId = $params['userId'];
        $result = $this->inviteToCallSave($userId, $opponentIds, $callMode, $candidate, $offer, $callId);


        $socketData = $this->populateGeneralSocketDataForSend('invite', $userId);
        $socketData['candidate']= $candidate;
        $socketData['offer']= $offer;
        $socketData['mode']= $callMode;
        $socketData['userId'] = $userId;
        $socketData['callId'] = $result->id;

        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $opponentIds)));
        return $result;

    }

    /***
     * @param $params
     * @return void
     */
    public function acceptCall( $params ) {
        if (!isset($params['callId']) || $params['callId'] == null) {
            return;
        }
        if(!isset($params['candidate']) || !isset($params['offer'])) {
            return;
        }

        $call = MULTIMEDIA_BOL_CallDao::getInstance()->findById($params['callId']);
        if(!$call){
            return;
        }

        $candidate = $params['candidate'];
        $offer = $params['offer'];
        $userId = $params['userId'];

        $userIdsInCall =  MULTIMEDIA_BOL_CallUserDao::getInstance()->getUserIdsInCall($call->id);
        $result = $this->acceptCallSave($userId, $call->id, $candidate, $offer);

        $socketData = $this->populateGeneralSocketDataForSend('accepted', $userId);
        $socketData['candidate']= $candidate;
        $socketData['offer']= $offer;
        $socketData['callId'] = $result;
        $socketData['userId'] = $userId;

        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $userIdsInCall)));

    }

    /***
     * @param $params
     * @return void
     */
    public function notAcceptCall( $params ){
        if (!isset($params['callId']) || $params['callId'] == null) {
            return;
        }

        $call = MULTIMEDIA_BOL_CallDao::getInstance()->findById($params['callId']);
        if(!$call){
            return;
        }
        $subType = $params['subType'];
        if(!in_array($subType, ['reject', 'busy', 'away', 'offline'])){
            return;
        }

        $userIdsInCall = MULTIMEDIA_BOL_CallUserDao::getInstance()->getUserIdsInCall($call->id);
        $socketData = $this->populateGeneralSocketDataForSend($subType, $params['userId']);

        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $userIdsInCall)));

    }

    /***
     * @param $params
     * @return void
     */
    public function hangoutCall( $params ){
        if (!isset($params['callId']) || $params['callId'] == null) {
            return;
        }
        $call = MULTIMEDIA_BOL_CallDao::getInstance()->findById($params['callId']);
        if(!$call){
            return;
        }

        $userIdsInCall =  MULTIMEDIA_BOL_CallUserDao::getInstance()->getUserIdsInCall($call->id);
        MULTIMEDIA_BOL_CallUserDao::getInstance()->hangoutCallSave($call->id);

        $socketData = $this->populateGeneralSocketDataForSend('hangout', $params['userId']);

        OW::getEventManager()->trigger(new OW_Event('base.send_data_using_socket', array('data' => $socketData, 'userIds' => $userIdsInCall)));

    }

    public function dismissCall( $params ){
        // TODO
        // hangout all users from call if actor is admin
    }

    /***
     * @param $userId
     * @param $recipientIds
     * @param $mode
     * @param $candidate
     * @param $offer
     * @param $callId
     * @return OW_Entity
     */
    public function inviteToCallSave($userId, $recipientIds, $mode, $candidate, $offer, $callId = 0)
    {
        // create new call
        $call = new MULTIMEDIA_BOL_Call();
        $call->establishTimestamp = time();
        $call->senderId = $userId;
        $call->mode = $mode;
        $call->candidate = $candidate;
        $call->offer = $offer;
        $result = $this->callDao->save($call);
        $callId = $call->id;

        $callCreator = new MULTIMEDIA_BOL_CallUser();
        $callCreator->callId = $callId;
        $callCreator->userId = $userId;
        $callCreator->joinTimestamp = time();
        $callCreator->role = $this::CALL_USER_ROLE_CREATOR;
        $this->callUserDao->save($callCreator);

        foreach ($recipientIds as $recipientId){
            $callUser = new MULTIMEDIA_BOL_CallUser();
            $callUser->callId = $callId;
            $callUser->userId = $recipientId;
            $callUser->role = $this::CALL_USER_ROLE_USER;
            $this->callUserDao->save($callUser);
        }
        return $result;
    }

    /***
     * @param $userId
     * @param $callId
     * @param $candidate
     * @param $offer
     * @return OW_Entity
     */
    public function acceptCallSave($userId,$callId, $candidate, $offer){
        $this->callUserDao->joinUserToCall($callId, $userId);

        $this->callDao->setAnswerToCall($callId, $candidate, $offer);

        return $callId;
    }

    /***
     * @param $subType
     * @param $senderId
     * @return array
     */
    public function populateGeneralSocketDataForSend($subType, $senderId)
    {
        $socketData = array();
        $socketData['type'] = 'call';
        $socketData['subType'] = $subType;
        $socketData['user']['id'] = $senderId;
        $socketData['user']['username'] = BOL_UserService::getInstance()->getUserName($senderId);
        $socketData['user']['avatar'] = BOL_AvatarService::getInstance()->getAvatarUrl($senderId);
        return $socketData;
    }
    
}
