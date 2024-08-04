<?php
$authorization = OW::getAuthorization();
$groupName = 'frmgroupsrss';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add',false,false);


$query = 'delete from ow_frmgroupsrss_group_rss where rssLink=""';

$logger = Updater::getLogger();
$tblPrefix = OW_DB_PREFIX;
$query = "DELETE FROM `{$tblPrefix}frmgroupsrss_group_rss` where rssLink=''";
try
{
    Updater::getDbo()->query($query);
}
catch ( Exception $e )
{
    $logger->writeLog(OW_Log::ERROR,'frmgroupsrss update 11153 error',['error'=>$e->getMessage()]);
}

