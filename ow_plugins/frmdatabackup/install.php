<?php
/**
 * 
 * All rights reserved.
 */

$config = OW::getConfig();

if ( !$config->configExists('frmdatabackup', 'deadline') )
{
    $config->addConfig('frmdatabackup', 'deadline', 10);
}
if ( !$config->configExists('frmdatabackup', 'tables') )
{
    $config->addConfig('frmdatabackup', 'tables', 'newsfeed_status');
}
if ( !$config->configExists('frmdatabackup', 'numberOfData') )
{
    $config->addConfig('frmdatabackup', 'numberOfData', 100);
}

