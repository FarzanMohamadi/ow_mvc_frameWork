<?php
$config = OW::getConfig();
if (!$config->configExists('frmforumplus', 'subscribe_group_users_to_topic')) {
    $config->saveConfig('frmforumplus', 'subscribe_group_users_to_topic', false);
}
