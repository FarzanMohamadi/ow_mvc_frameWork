<?php
try
{
    Updater::getDbo()->query('CREATE TABLE IF NOT EXISTS `' . OW_DB_PREFIX . 'frminvite_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderId` int(11) NOT NULL,
  `invitedEmail` varchar(512) NOT NULL,
  `timeStamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
  ) DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;');
} catch (Exception $ex) {
    // Pass
}
OW::getPluginManager()->addPluginSettingsRouteName('frminvite', 'frminvite.admin');
$config = OW::getConfig();
if($config->configExists('frminvite', 'invitation_view_count'))
{
    $config->deleteConfig('frminvite', 'invitation_view_count');
}
if ( !$config->configExists('frminvite', 'invitation_view_count') )
{
    $config->addConfig('frminvite', 'invitation_view_count',15);
}
$updateDir = dirname(__FILE__) . DS;
Updater::getLanguageService()->importPrefixFromZip($updateDir . 'langs.zip', 'frminvite');
