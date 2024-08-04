<?php
/**
 * frmgroupsplus
 */

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMGROUPSPLUS_CMP_FileListWidget');

$eventIisGroupsPlusFiles = new OW_Event('frmgroupsplus.delete.files', array('allFiles'=>true));
OW::getEventManager()->trigger($eventIisGroupsPlusFiles);
$config = OW::getConfig();

if($config->configExists('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed'))
    $config->deleteConfig('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed');

if ($config->configExists('frmgroupsplus', 'showFileUploadSettings')) {
    $config->deleteConfig('frmgroupsplus', 'showFileUploadSettings');
}
if ($config->configExists('frmgroupsplus', 'showAddTopic')) {
    $config->deleteConfig('frmgroupsplus', 'showAddTopic');
}
if ($config->configExists('frmgroupsplus', 'show_otp_form')) {
    $config->deleteConfig('frmgroupsplus', 'show_otp_form');
}
if ($config->configExists('frmgroupsplus', 'groupApproveStatus')) {
    $config->deleteConfig('frmgroupsplus', 'groupApproveStatus');
}
try
{
    BOL_ComponentAdminService::getInstance()->deleteWidget('FRMGROUPSPLUS_CMP_PendingInvitation');
}
catch(Exception $e)
{

}