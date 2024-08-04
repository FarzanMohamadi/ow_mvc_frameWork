<?php

try {
    $authorization = OW::getAuthorization();
    $authorization->deleteAction('frmgroupsplus', 'group_approve');
} catch (Exception $ex){
    OW::getLogger()->writeLog(OW_Log::ERROR, 'update_10921_frmgroupsplus_delete_group_approved_failed',
        ['actionType'=>OW_Log::UPDATE, 'enType'=>'plugin', 'enId'=> 'frmgroupsplus', 'error'=>'error in mysql', 'exception'=>$ex]);
}
