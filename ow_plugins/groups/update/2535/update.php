<?php
try
{
    if ( !Updater::getConfigService()->configExists('groups', 'is_forum_connected') )
    {
        Updater::getConfigService()->addConfig('groups', 'is_forum_connected', '0', 'Add Forum to Groups plugin');
    }
}
catch ( Exception $e ) { }

Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'groups');

