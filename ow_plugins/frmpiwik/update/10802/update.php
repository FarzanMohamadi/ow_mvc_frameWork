<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmpiwik', 'frmpiwik-admin');
if (!OW::getConfig()->configExists('frmpiwik', 'siteId')){
    OW::getConfig()->saveConfig('frmpiwik', 'siteId', '');
}