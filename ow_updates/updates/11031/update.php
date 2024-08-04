<?php
try
{
    $config = OW::getConfig();
    if(!$config->configExists('base', 'socket_host')) {
        $config->addConfig('base', 'socket_host', 'ws://localhost:8880');
        $config->addConfig('base', 'socket_enabled', false);
    }
}
catch (Exception $e)
{
    OW::getLogger()->writeLog( OW_Log::ERROR, json_encode($e));
}
