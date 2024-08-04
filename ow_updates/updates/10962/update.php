<?php
try {
    //Add all languages and refresh Cache
    FRMSecurityProvider::updateLanguages(false);
}
catch (Exception $e)
{
    $logger->writeLog(OW_Log::ERROR, 'Core update_failed', array('exception'=>$e));
}
