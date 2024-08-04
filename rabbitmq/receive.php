<?php
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', substr(dirname(__FILE__), 0, - strlen('rabbitmq')));

$GLOBALS['LOG_FILE_NAME'] = 'rabbitmq.log';
require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
OW::getLogger()->addAdditionalData('ORIGIN', 'RABBITMQ');

OW::getRouter()->setBaseUrl(OW_URL_HOME);

date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));
OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

OW::getPluginManager()->initPlugins();
$event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
OW::getEventManager()->trigger($event);

if (!FRMSecurityProvider::isRabbitMQActive()) {
    return;
}

$rabbitConnection = new \PhpAmqpLib\Connection\AMQPStreamConnection(RABBIT_HOST, RABBIT_PORT, RABBIT_USER, RABBIT_PASSWORD);
$channel = $rabbitConnection->channel();
$queueName = 'queue';
if (defined('RABBIT_QUEUE_NAME')) {
    $queueName = RABBIT_QUEUE_NAME;
}
$channel->queue_declare($queueName, false, false, false, false);

$GLOBALS['RABBITMQ_TIME'] = time();

$channelCallback = function ($msg) {
    if ( defined('LOG_ALL_REQUESTS') && LOG_ALL_REQUESTS) {
        FRMSecurityProvider::logRequestToFile('rabbitmq_on_message', $msg);
    }
    echo 'receive:' . strftime("%y/%m/%#d, %H:%M", time()) . "\n";
    OW::getLogger()->addAdditionalData('RABBITMQ_MSG', $msg);
    OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array('startTime' => $GLOBALS['RABBITMQ_TIME']), $msg));
};

echo 'listening on queue:' . $queueName . "\n";
$channel->basic_consume($queueName, '', false, true, false, false, $channelCallback);

while (count($channel->callbacks)) {
    $channel->wait();
}
