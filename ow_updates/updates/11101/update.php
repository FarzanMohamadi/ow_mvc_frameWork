<?php
try
{
    $config = OW::getConfig();

    if($config->configExists('base','avatar_size')){
        $config->saveConfig('base', 'avatar_size', 40);
    }

    if($config->configExists('base','avatar_big_size')){
        $config->saveConfig('base', 'avatar_big_size', 160);
    }
}
catch (Exception $e)
{
    OW::getLogger()->writeLog( OW_Log::ERROR, json_encode($e));
}