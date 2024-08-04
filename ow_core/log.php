<?php
use Monolog\Logger;
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_core
 *
 * Logging functionality using monolog
 * this class replaced an old class with the same name
 *
 * Log Levels:
 * DEBUG (100): Detailed debug information.
 * INFO (200): Interesting events. Examples: User logs in, SQL logs.
 * NOTICE (250): Normal but significant events.
 * WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 * ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
 * CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
 * ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 * EMERGENCY (600): Emergency: system is unusable.
 *
 */
class OW_Log
{
    // Log Levels
    const DEBUG     = 'debug';
    const INFO      = 'info';
    const NOTICE    = 'notice';
    const WARNING   = 'warning';
    const ERROR     = 'error';
    const CRITICAL  = 'critical';
    const ALERT     = 'alert';
    const EMERGENCY = 'emergency';

    //General Log Types
    const CREATE    = 'create';
    const READ      = 'read';
    const UPDATE    = 'update';
    const DELETE    = 'delete';

    private $logger;
    private $additional_data;

    private $channelName;
    private static $classInstances;

    /**
     * Returns an instance of class (singleton pattern implementation).
     * @param string $name
     * @return OW_Log
     */
    public static function getInstance($name)
    {
        if ( self::$classInstances === null )
        {
            self::$classInstances = array();
        }

        if ( empty(self::$classInstances[$name]) )
        {
            self::$classInstances[$name] = new self($name);
        }

        return self::$classInstances[$name];
    }

    /**
     * OW_Log constructor.
     * @param string $name
     */
    private function __construct($name)
    {
        $this->channelName = $name;
        $this->additional_data = [];
        $this->logger = new Logger($this->channelName);
    }

    /**
     * @param $handler
     */
    public function addLogHandler( $handler )
    {
        $this->logger->pushHandler($handler);
    }

    /***
     * @return string
     */
    private function getCurrentIP(){
        $ip = OW::getRequest()->getRemoteAddress();
        if($ip == '::1' || empty($ip)){
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    public static function getShortStackTrace(){
        $stack_trace = [];
        $trace = debug_backtrace();
        for($i = 1; $i<count($trace);$i++){
            $item = ' ';
            $item .= isset($trace[$i]['line'])?'L'.$trace[$i]['line']:'L_';
            $item .=' => #'.(count($trace)-$i).' ';
            $item .= isset($trace[$i]['class'])?$trace[$i]['class']:$trace[$i]['file'];
            $item .= isset($trace[$i]['function'])?':'.$trace[$i]['function']:':_';
            array_unshift($stack_trace, $item);
        }
        return implode('', $stack_trace);
    }

    /***
     * @return string
     */
    private function getCurrentMode(){
        $res = 'desktop';
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $res = 'mobile';
        }
        else if (isset($_COOKIE['UsingMobileApp'])){
            $res = $_COOKIE['UsingMobileApp'];
        }
        return $res;
    }

    public static function getCurrentURL(){
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
            (isset($_SERVER['HTTP_HOST'])?"://$_SERVER[HTTP_HOST]":"://../").
            (isset($_SERVER['REQUEST_URI'])?$_SERVER["REQUEST_URI"]:"");
    }

    public static function getPostData(){
        $post_data = $_POST;
        // filter some values
        $starred_items = ['password', 'changedPassword', 'repeatPassword', 'oldPassword',
            'login_cookie', 'access_token', 'csrf_token', 'csrf_hash'];
        foreach ($starred_items as $p_name) {
            if (isset($post_data[$p_name])) {
                $post_data[$p_name] = '*****';
            }
        }
        return $post_data;
    }

    /***
     * @param $type
     */
    private function sendNotificationToModerators($type){
        $url = OW_URL_HOME;
        $userService = BOL_UserService::getInstance();
        $moderators = BOL_AuthorizationService::getInstance()->getModeratorList();
        foreach ( $moderators as $moderator ) {
            $userId = $moderator->userId;
            $avatars = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($userId), true, true, false, false);
            $avatar = $avatars[$userId];
            $event = new OW_Event('notifications.add', array(
                'pluginKey' => 'admin',
                'entityType' => 'log_failed_'.$type,
                'entityId' => '1',
                'userId' => $userId,
                'action' => 'log_failed'
            ), array(
                'format' => "text",
                'avatar' => $avatar,
                'string' => array(
                    'key' => 'base+logger_failed',
                    'vars' => array(
                        'userName' => $userService->getDisplayName($userId),
                        'userUrl' => $userService->getUserUrl($userId),
                        'type' => $type,
                        'url' => $url
                    )
                ),
                'url' => $url
            ));

            OW::getEventManager()->trigger($event);
        }
    }

    /**
     * @param $logLevel
     * @param string $title
     * @param array $data
     */
    public function directWriteLog($logLevel, $title='', $data=array())
    {
        try {
            $this->logger->{$logLevel}($title, $data);
        } catch (UnexpectedValueException $exception) {
            $this->sendNotificationToModerators('file');
        } catch (\RuntimeException $exception) {
//            $this->sendNotificationToModerators('elasticsearch');
            @file_put_contents(OW_DIR_ROOT . 'ow_log' . DS . 'unk.txt', $exception->getMessage());
        }
    }

    public function WriteActionLog($logLevel, $title='', $data=array())
    {
        try {
            @file_put_contents(OW_DIR_ROOT . 'ow_log' . DS . 'actionLog.log',
                implode(" ", array('level' => $logLevel, 'title' => $title, 'data' => implode(" ", $data))) . "\n",
                FILE_APPEND | LOCK_EX);
        } catch (Exception $exception) {
            $this->sendNotificationToModerators('file');
        }
    }

    /**
     * @param $logLevel
     * @param string $title
     * @param array $data
     * @param bool $addUserId
     * @param bool $addIP
     * @param bool $addVersion
     * @param bool $addPOST
     */
    public function writeLog($logLevel, $title='', $data=array(), $addUserId=true, $addIP=true, $addVersion=true,
                                $addPOST=true, $isActionLog = false){
        if(isset($GLOBALS['LOG_HANDLER_IS_SET']) && !$GLOBALS['LOG_HANDLER_IS_SET']){
            return;
        }
        if(isset($GLOBALS['LOGGING']) && $GLOBALS['LOGGING']){
            return;
        }
        $GLOBALS['LOGGING'] = true;

        # ------------------------------------------ MORE DATA
        $prepend_data = [];
        $append_data = [];

        if (file_exists('testcasename')){
            $prepend_data['testcase'] = @file_get_contents('testcasename');
        }
        if (php_sapi_name() === 'cli') {
            if ($addVersion) {
                $prepend_data['mode'] = 'cli';
            }
        } else {
            $auth = OW_Auth::getInstance()->getAuthenticator();
            if (isset($auth)) {
                if (class_exists('OW_EventManager') && $addVersion) {
                    try {
                        $prepend_data['mode'] = $this->getCurrentMode();
                    } catch (Exception $ex) {}
                }
                if (class_exists('OW_User') && $addUserId) {
                    try {
                        $user = OW::getUser();
                        $prepend_data['user_id'] = isset($user) ? $user->getId() : -1;
                    } catch (Exception $ex) {}
                }
            }
        }
        if(isset($_SERVER['REQUEST_URI'])){
            $prepend_data['URL'] = self::getCurrentURL();
        }
        if($addIP){
            $append_data['ip'] = $this->getCurrentIP();
        }
        if($addPOST){
            $append_data['POST'] = self::getPostData();
        }
        $append_data['stack_trace'] = self::getShortStackTrace();

        $data = $prepend_data + $data + $append_data + $this->additional_data;
        # ------------------------------------------

        # disabling sending with rabbitmq: for conflict of log files
        if($isActionLog){
            $this->WriteActionLog($logLevel, $title, $data);
        } else{
            $this->directWriteLog($logLevel, $title, $data);
        }

        $GLOBALS['LOGGING'] = false;
    }

    /***
     * @deprecated This is a legacy function. Use writeLog instead.
     * @param string $title
     */
    public function addEntry( $title='')
    {
        $this->writeLog(self::ERROR, $title);
    }

    /***
     * @param $key
     * @param $value
     */
    public function addAdditionalData( $key, $value ){
        $this->additional_data[$key] = $value;
    }

    /***
     * @param $array
     */
    public function addAdditionalDataArray( $array ){
        $this->additional_data = array_merge($this->additional_data, $array);
    }
}