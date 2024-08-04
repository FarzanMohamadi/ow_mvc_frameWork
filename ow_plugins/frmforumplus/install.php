<?php
/**
 * FRM Forum Plus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmpiwik
 * @since 1.0
 */

$config = OW::getConfig();
if (!$config->configExists('frmforumplus', 'mobile_forum_group_visibile')) {
    $config->addConfig('frmforumplus', 'mobile_forum_group_visibile', false);
}
if (!$config->configExists('frmforumplus', 'subscribe_group_users_to_topic')) {
    $config->saveConfig('frmforumplus', 'subscribe_group_users_to_topic', false);
}
