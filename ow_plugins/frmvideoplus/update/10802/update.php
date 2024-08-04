<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'frmvideoplus');
OW::getPluginManager()->addPluginSettingsRouteName('frmvideoplus','frmvideoplus_admin_config');
OW::getPluginManager()->addUninstallRouteName('frmvideoplus', 'frmvideoplus_uninstall');
$maxUploadMaxFilesize = BOL_FileService::getInstance()->getUploadMaxFilesize();
$config =  OW::getConfig();
if($config->configExists('frmvideoplus', 'maximum_video_file_upload'))
{
    $config->deleteConfig('frmvideoplus', 'maximum_video_file_upload');
}
if ( !$config->configExists('frmvideoplus', 'maximum_video_file_upload'))
{
    $config->addConfig('frmvideoplus', 'maximum_video_file_upload',$maxUploadMaxFilesize);
}
if ( !$config->configExists('frmvideoplus', 'uninstall_inprogress') )
{
    $config->addConfig('frmvideoplus', 'uninstall_inprogress', 0, 'Plugin is being uninstalled');
}
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
