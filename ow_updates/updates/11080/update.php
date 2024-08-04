<?php
$filesToDelete = [
    OW_DIR_ROOT.'ow_system_plugins'.DS.'base'. DS . 'controllers' . DS . 'ajax_update_status.php',
    OW_DIR_ROOT.'ow_system_plugins'.DS.'base'. DS . 'controllers' . DS . 'api_responder.php',
    OW_DIR_ROOT.'ow_system_plugins'.DS.'base'. DS . 'controllers' . DS . 'api_server.php',
];

foreach($filesToDelete as $filepath){
    try{
        if(file_exists($filepath)){
            unlink($filepath);
        }
    }catch (Exception $e){
        Updater::getLogger()->writeLog(OW_Log::ERROR, 'update_11080_file_delete', ['e'=>$e->getMessage()]);
    }
}