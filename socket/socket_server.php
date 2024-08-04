<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package socket
 * @since 1.0
 */

define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', substr(dirname(__FILE__), 0, - strlen('socket')));

$GLOBALS['LOG_FILE_NAME'] = 'socket_server.log';
require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

$_SERVER['REQUEST_URI'] = '';

OW::getSession()->start();

$application = OW::getApplication();

$application->init();

//socket
require_once(OW_DIR_CORE . 'socket_ping.php');
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$loop   = React\EventLoop\Factory::create();
$pusher = OW_SocketPing::getInstance();
$context = new React\ZMQ\Context($loop);
$pull = $context->getSocket(ZMQ::SOCKET_PULL);

try {
    $pull->bind('tcp://127.0.0.1:' . FRMSecurityProvider::getTCPSocketPort());
} catch (ZMQSocketException $e) {
    echo "\nerror in message puller's connection";
} // Binding to 127.0.0.1 means the only client that can connect is itself

try {
    $pull->on('message', array($pusher, 'onSendMessageToOther'));

    $webSock = new React\Socket\Server('0.0.0.0:'.FRMSecurityProvider::getSocketPort(), $loop);

    $webServer = new IoServer(
        new HttpServer(
            new WsServer(
                $pusher
            )
        ),
        $webSock
    );

    $loop->run();
} catch (Exception $e) {
    OW::getLogger()->writeLog(OW_Log::ERROR, 'socket_fail', ['error'=>$e->getMessage()]);
}