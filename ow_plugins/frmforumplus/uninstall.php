<?php
/**
 * FRM Forum Plus
 */
$config = OW::getConfig();
if($config->configExists('frmforumplus', 'mobile_forum_group_visibile'))
{
    $config->deleteConfig('frmforumplus', 'mobile_forum_group_visibile');
}
if ($config->configExists('frmforumplus', 'subscribe_group_users_to_topic'))
{
    $config->deleteConfig('frmforumplus', 'subscribe_group_users_to_topic');
}
