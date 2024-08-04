<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package socket
 * @since 1.0
 */

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class OW_SocketPing implements MessageComponentInterface {
    const PING_EVENT = 'base.ping';
    protected $clients;
    protected $usersSockets;
    protected $streamSockets;
    private static $classInstance;
    private $start_time;

    public static function getInstance()
    {
        OW::getLogger()->addAdditionalData('ORIGIN', 'OW_SocketPing');

        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    private function log($string,$type= OW_Log::INFO){
        OW::getLogger()->writeLog($type,'socket_log',['string'=>$string]);
        fwrite(STDERR, $string . "\n");
    }

    public function __construct() {
        $this->log("==================",OW_Log::INFO);
        $this->log("Socket Server Initiated!",OW_Log::INFO);
        $this->clients = new \SplObjectStorage;
        $this->usersSockets = array();
        $this->streamSockets = array();
        $this->start_time = time();
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->propagateStreamSocket();
    }

    public function getSocketNumbersData() {
        $date = date('H:i:s', time());
        return json_encode(['type' => 'streamOnlineUser', 'time'=> $date, 'number' => $this->clients->count()]);
    }

    public function propagateStreamSocket() {
        foreach ($this->streamSockets as $connection) {
            $connection->send($this->getSocketNumbersData());
        }
    }

    public static function getHash($userId){
        $hashWith = BOL_UserService::getInstance()->getUserSalt($userId);
        if (!isset($hashWith)){
            $hashWith = OW_PASSWORD_PEPPER;
        }
        return md5($userId . md5($hashWith));
    }

    private function checkAuth($request){
        $resp = false;
        if (!empty($request['auth_mobile'])) {
            $resp = BOL_UserService::getInstance()->findUserIdByCookie(trim($request['auth_mobile']));
            if ($resp == null) {
                $resp = false;
            }
        } else if(!empty($request['auth'])){
            $auth = $request['auth'];
            $userId = $auth[0];
            if( self::getHash($userId) == $auth[1]){
                $resp = $userId;
            }
        }
        return $resp;
    }

    public function onMessage(ConnectionInterface $conn, $request) {
        if ( defined('LOG_ALL_REQUESTS') && LOG_ALL_REQUESTS) {
            FRMSecurityProvider::logRequestToFile('socket_on_message', $request);
        }

        $request = json_decode($request, true);
        OW::getLogger()->addAdditionalData('SOCKET_REQUEST', $request);

        if (empty($request['type'])){
            $conn->send(json_encode(['error'=>'inputs']));
            return;
        }

        $userId = $this->checkAuth($request);
        $needLogin = true;

        if (isset($request['doNotNeedLogin']) && $request['doNotNeedLogin']) {
            $needLogin = false;
        }

        if($userId === false && $needLogin){
            $conn->send(json_encode(['error'=>'authentication']));
            return;
        }

        if ($request['type'] == "introduce") {
            $this->setUserConnection($userId, $conn);
            return;
        }

        if ($request['type'] == "call") {
            $this->handleCall((array)$request,$userId);
            return;
        }

        if ($needLogin) {
            OW::getUser()->login($userId, false);
        }

        if ($request['type'] == "streamOnlineUser") {
            if (OW::getUser()->isAdmin()) {
                $this->streamSockets[] = $conn;
                $conn->send($this->getSocketNumbersData());
                return;
            }

            $conn->send(json_encode(['error'=>'authentication']));
            return;
        }

        $message_event = OW::getEventManager()->trigger(new OW_Event("base.on_socket_message_received", array('data' => $request)));
        $data = $message_event->getData();

        if (is_string($data)) {
            $conn->send($data);
        }

        if ($needLogin) {
            OW::getUser()->logout(false);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $this->removeUserConnection($conn);
        $this->removeStreamConnection($conn);
        $this->propagateStreamSocket();
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log(  "{$conn->resourceId} : An error has occurred: {$e->getMessage()}",OW_Log::ERROR);
        $conn->close();
    }

    public function changeSocket($data) {
        $validChangeSocket = true;
        if (isset($data->time) && $data->time < $this->start_time) {
            $this->log("socket change failed because of legacy request. socket instantiated time: " . $this->start_time . '. requested time: ' . $data->time,OW_Log::INFO);
            $validChangeSocket = false;
        }
        if ($validChangeSocket) {
            $this->log("socket changed at: " . time(). '. Started at: ' . $this->start_time,OW_Log::INFO);
            if (isset($data->exit) && $data->exit) {
                exit(5);
            }
            if (isset($data->configReset) && $data->configReset) {
                OW::getConfig()->generateCache();
//                $this->log("config regenerated at: " . OW::getConfig()->getValue('base', 'last_code_change'));
            }
        }
    }

    public function onSendMessageToOther($message){
        $message = (array) json_decode($message);
        if (!isset($message['userId']) && !isset($message['userIds'])) {
            return;
        }
        if (isset($message['userId']) && isset($message['data']->type) && $message['userId'] == -1 && $message['data']->type == 'change_data') {
            $this->changeSocket($message['data']);
        } else {
            $userConnections = array();
            if (isset($message['userId'])) {
                $userConnections = $this->getUserConnections($message['userId']);
            } else if (isset($message['userIds'])) {
                $userConnections = $this->getUsersConnections($message['userIds']);
                unset($message['userIds']);
            }
            foreach ($userConnections as $connection) {
                $connection->send(json_encode($message));
            }
        }
    }

    public function existSocketByUser($userId) {
        return isset($this->usersSockets[$userId]);
    }

    public function setUserConnection($userId, $connection){
        $isFirst = false;
        if(!$this->existSocketByUser($userId)){
            $isFirst = true;
            $this->usersSockets[$userId] = array();
        }
        if (!$this->existConnectionInUserSockets($userId, $connection)) {
            $this->usersSockets[$userId][] = $connection;
            OW::getEventManager()->trigger(new OW_Event("socket.user_socket_created", array('user_id' => $userId, 'first' => $isFirst)));
        }

    }

    public function getUserConnections($userId){
        if ($this->existSocketByUser($userId)){
            return $this->usersSockets[$userId];
        }
        return [];
    }

    public function getUsersConnections($userIds){
        $connections = array();
        foreach ($userIds as $userId) {
            if ($this->existSocketByUser($userId)){
                $connections = array_merge($connections, $this->usersSockets[$userId]);
            }
        }
        return $connections;
    }

    public function existConnectionInUserSockets($userId, $connection) {
        $userSockets = $this->usersSockets;
        if (!isset($userSockets[$userId])) {
            return false;
        }
        if (($key = array_search($connection, $userSockets[$userId])) !== false) {
            return true;
        }
        return false;
    }

    public function removeUserConnection($connection){
        $userSockets = $this->usersSockets;
        $removedIndex = -1;
        $removedUserId = -1;
        foreach ($userSockets as $userId => $connections){
            if (($key = array_search($connection, $connections)) !== false) {
                $removedIndex = $key;
                $removedUserId = $userId;
                break;
            }
        }
        if ($removedIndex != -1 && $removedUserId != -1) {
            unset($this->usersSockets[$removedUserId][$removedIndex]);
            if(count($this->usersSockets[$removedUserId])==0){
                OW::getEventManager()->trigger(new OW_Event("socket.all_user_socket_closed", array('user_id' => $removedUserId)));
                unset($this->usersSockets[$removedUserId]);
            }
        }
    }

    public function removeStreamConnection($connection){
        $removedIndex = array_search($connection, $this->streamSockets);
        if ($removedIndex != -1) {
            unset($this->streamSockets[$removedIndex]);
        }
    }

    public function handleCall($request,$userId){
        if( !isset($request['type'])  || $request['type'] != 'call' || !isset($request['subType']) || !isset($userId) ){
            return;
        }
        if( !FRMSecurityProvider::checkPluginActive('multimedia', true) || !FRMSecurityProvider::isSocketEnable() ){
            return;
        }
        OW::getEventManager()->trigger(new OW_Event("call_actions", array('params' => $request, 'userId'=>$userId)));

    }

}