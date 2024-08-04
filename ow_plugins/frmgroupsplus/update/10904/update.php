
<?php
$config = OW::getConfig();

if (!$config->configExists('frmgroupsplus', 'groupApproveStatus')) {
    $config->saveConfig('frmgroupsplus', 'groupApproveStatus', 0);
}

if (!$config->configExists('frmgroupsplus', 'unapprovedGroupsList')) {
    $config->saveConfig('frmgroupsplus', 'unapprovedGroupsList', null);
}