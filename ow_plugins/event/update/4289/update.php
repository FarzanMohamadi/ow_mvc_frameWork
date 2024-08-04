<?php
Updater::getLanguageService()->importPrefixFromZip(dirname(__FILE__) . DS . 'langs.zip', 'event');

if( defined('OW_PLUGIN_XP') )
{
    Updater::getStorage()->copyDir(OW_DIR_USERFILES . 'plugins' . DS . 'event' . DS , OW_DIR_USERFILES . 'plugins' . DS . 'event' . DS);
}
