<?php
/**
 * FRM Invite
 */
$config = OW::getConfig();
if($config->configExists('frminvite', 'invitation_view_count'))
{
    $config->deleteConfig('frminvite', 'invitation_view_count');
}

if ($config->configExists('frminvite', 'invite_daily_limit'))
{
    $config->deleteConfig('frminvite', 'invite_daily_limit');
}