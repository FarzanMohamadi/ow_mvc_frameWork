<?php
define('OW_DIR_STATIC_PLUGIN', OW_DIR_STATIC . 'plugins' . DS);
define('OW_DIR_STATIC_THEME', OW_DIR_STATIC . 'themes' . DS);
define('OW_DIR_PLUGIN_USERFILES', OW_DIR_USERFILES . 'plugins' . DS);
define('OW_DIR_THEME_USERFILES', OW_DIR_USERFILES . 'themes' . DS);
define('OW_DIR_LOG', OW_DIR_ROOT . 'ow_log' . DS);

if ( defined('OW_URL_STATIC') )
{
    define('OW_URL_STATIC_THEMES', OW_URL_STATIC . 'themes/');
    define('OW_URL_STATIC_PLUGINS', OW_URL_STATIC . 'plugins/');
}

if ( defined('OW_URL_USERFILES') )
{
    define('OW_URL_PLUGIN_USERFILES', OW_URL_USERFILES . 'plugins/');
    define('OW_URL_THEME_USERFILES', OW_URL_USERFILES . 'themes/');
}

define("OW_DIR_LIB_VENDOR", OW_DIR_LIB . "vendor" . DS);

if ( !defined("OW_SQL_LIMIT_USERS_COUNT") )
{
    define("OW_SQL_LIMIT_USERS_COUNT", 10000);
}
