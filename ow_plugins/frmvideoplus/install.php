<?php
/**
 * frmvideoplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvideoplus
 * @since 1.0
 */

$config =  OW::getConfig();
if ( !$config->configExists('frmvideoplus', 'uninstall_inprogress') )
{
    $config->addConfig('frmvideoplus', 'uninstall_inprogress', 0, 'Plugin is being uninstalled');
}

if ( !$config->configExists('frmvideoplus', 'uninstall_cron_busy') )
{
    $config->addConfig('frmvideoplus', 'uninstall_cron_busy', 0, 'Uninstall queue is busy');
}

if ( !$config->configExists('frmvideoplus', 'maintenance_mode_state') )
{
    $state = (int) $config->getValue('base', 'maintenance');
    $config->addConfig('frmvideoplus', 'maintenance_mode_state', $state, 'Stores site maintenance mode config before plugin uninstallation');
}
