<?php
try
{
    $authorization = OW::getAuthorization();
    $groupName = 'base';
    $authorization->addAction($groupName, 'edit_user_profile',false,false);
}
catch (Exception $e)
{
    $logger->writeLog(OW_Log::ERROR, 'Core update_failed', array('exception'=>$e));
}

