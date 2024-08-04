<?php
$config = OW::getConfig();
if (!$config->configExists('frmgroupsplus', 'showFileUploadSettings')) {
    $config->addConfig('frmgroupsplus', 'showFileUploadSettings', 1);
}
if (!$config->configExists('frmgroupsplus', 'showAddTopic')) {
    $config->addConfig('frmgroupsplus', 'showAddTopic', 1);
}