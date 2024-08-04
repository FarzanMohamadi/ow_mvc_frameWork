<?php
$config = OW::getConfig();
if ($config->configExists('frmgroupsplus', 'groupFileAndJoinFeed')){
    $config->deleteConfig('frmgroupsplus', 'groupFileAndJoinFeed');
}
if (!$config->configExists('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed')) {
    $config->addConfig('frmgroupsplus', 'groupFileAndJoinAndLeaveFeed', '["fileFeed","joinFeed","leaveFeed"]');
}