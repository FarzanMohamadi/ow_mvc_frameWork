<?php
//echo 'including init.php...';
define('CODECEPTION_UNIT', true);
define('_OW_', true);
define('OW_CRON', true);
//define('DS', DIRECTORY_SEPARATOR);
//define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DS . '..' . DS . '..' . DS . '..' . DS . '..' . DS);
define('DEFAULT_TIMEOUT_MILLIS', 15000);
require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
require_once(OW_DIR_ROOT . 'ow_libraries' . DS . 'vendor' . DS . 'autoload.php');
