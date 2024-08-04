<?php
function ensure_session_active()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function ensure_no_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}
function exception_on_error($errno, $errstr, $errfile=null, $errline=null) {
    if (error_reporting() === 0) {
        return false;
    }
    fwrite(STDERR, "errno=$errno, errstr=$errstr, errfile=$errfile, errline=$errline\n");
    $message = strtok($errstr, "\n");
    throw new Exception("errno=$errno, message=$message, errfile=$errfile, errline=$errline");
}

try {
    set_error_handler('exception_on_error');
    define('_OW_', true);
    define('DS', DIRECTORY_SEPARATOR);
    define('OW_DIR_ROOT', dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
    define('OW_CRON', true);
    define('DEFAULT_TIMEOUT_MILLIS', 15000);
    require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
    OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());
    require_once(OW_DIR_ROOT . 'ow_frm' . DS . 'test' . DS . 'FRMTestUtilites.php');
    require_once(OW_DIR_ROOT . 'ow_unittest' . DS. 'ow_core' . DS . 'baseFunctions.php');
}catch (Exception $ex){
    fwrite(STDERR, "Error in PHPUnit bootstrap (init.php):\n".$ex."\nSolve the problem and run again\n");
    throw $ex;
}