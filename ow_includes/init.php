<?php
require_once OW_DIR_ROOT . 'ow_includes/config.php';
require_once OW_DIR_ROOT . 'ow_includes/define.php';
require_once OW_DIR_UTIL . 'debug.php';
require_once OW_DIR_UTIL . 'string.php';
require_once OW_DIR_CORE . 'autoload.php';
require_once OW_DIR_CORE . 'exception.php';
require_once OW_DIR_INC . 'function.php';
require_once OW_DIR_CORE . 'ow.php';
require_once OW_DIR_CORE . 'plugin.php';
require_once OW_DIR_CORE . 'filter.php';

mb_internal_encoding('UTF-8');

if ( OW_DEBUG_MODE && !defined('CODECEPTION_UNIT') )
{
    ob_start();
}

spl_autoload_register(array('OW_Autoload', 'autoload'));
require_once OW_DIR_LIB_VENDOR . "autoload.php";

// adding standard package pointers
$autoloader = OW::getAutoloader();
$autoloader->addPackagePointer('OW', OW_DIR_CORE);
$autoloader->addPackagePointer('INC', OW_DIR_INC);
$autoloader->addPackagePointer('UTIL', OW_DIR_UTIL);
$autoloader->addPackagePointer('BOL', OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'bol');

// Force autoload of classes without package pointer
$classesToAutoload = array(
    'OW_Log' => OW_DIR_CORE . 'log.php',
    'Form' => OW_DIR_CORE . 'form.php',
    'TextField' => OW_DIR_CORE . 'form_element.php',
    'HiddenField' => OW_DIR_CORE . 'form_element.php',
    'FormElement' => OW_DIR_CORE . 'form_element.php',
    'RequiredValidator' => OW_DIR_CORE . 'validator.php',
    'StringValidator' => OW_DIR_CORE . 'validator.php',
    'RegExpValidator' => OW_DIR_CORE . 'validator.php',
    'EmailValidator' => OW_DIR_CORE . 'validator.php',
    'UrlValidator' => OW_DIR_CORE . 'validator.php',
    'AlphaNumericValidator' => OW_DIR_CORE . 'validator.php',
    'IntValidator' => OW_DIR_CORE . 'validator.php',
    'InArrayValidator' => OW_DIR_CORE . 'validator.php',
    'FloatValidator' => OW_DIR_CORE . 'validator.php',
    'DateValidator' => OW_DIR_CORE . 'validator.php',
    'CaptchaValidator' => OW_DIR_CORE . 'validator.php',
    'AbstractPasswordValidator' => OW_DIR_CORE . 'validator.php',
    'NewPasswordValidator' => OW_DIR_CORE . 'validator.php',
    'OldPasswordValidator' => OW_DIR_CORE . 'validator.php',
    'RadioField' => OW_DIR_CORE . 'form_element.php',
    'CheckboxField' => OW_DIR_CORE . 'form_element.php',
    'Selectbox' => OW_DIR_CORE . 'form_element.php',
    'CheckboxGroup' => OW_DIR_CORE . 'form_element.php',
    'PasswordField' => OW_DIR_CORE . 'form_element.php',
    'Submit' => OW_DIR_CORE . 'form_element.php',
    'Button' => OW_DIR_CORE . 'form_element.php',
    'Textarea' => OW_DIR_CORE . 'form_element.php',
    'FileField' => OW_DIR_CORE . 'form_element.php',
    'TagsField' => OW_DIR_CORE . 'form_element.php',
    'SuggestField' => OW_DIR_CORE . 'form_element.php',
    'MultiFileField' => OW_DIR_CORE . 'form_element.php',
    'Multiselect' => OW_DIR_CORE . 'form_element.php',
    'CaptchaField' => OW_DIR_CORE . 'form_element.php',
    'InvitationFormElement' => OW_DIR_CORE . 'form_element.php',
    'Range' => OW_DIR_CORE . 'form_element.php',
    'WyswygRequiredValidator' => OW_DIR_CORE . 'validator.php',
    'DateField' => OW_DIR_CORE . 'form_element.php',
    'DateRangeInterface' => OW_DIR_CORE . 'form_element.php',
    'EditUserNameValidator' => OW_DIR_CORE . 'validator.php',
    'EditEmailValidator' => OW_DIR_CORE . 'validator.php',
    'NewUserEmailValidator' => OW_DIR_CORE . 'validator.php',
    'NewUserUsernameValidator' => OW_DIR_CORE . 'validator.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

if ( defined("OW_URL_HOME") )
{
    OW::getRouter()->setBaseUrl(OW_URL_HOME);
}

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance();
}

require_once OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS . 'file_storage.php';
require_once OW_DIR_ROOT . 'ow_frm' . DS . 'security' . DS . 'provider.php';
require_once OW_DIR_ROOT . 'ow_frm' . DS . 'init.php';


/***
 * Logging mechanism using Monolog
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
if ( !defined('OW_ERROR_LOG_ENABLE') || (bool) OW_ERROR_LOG_ENABLE )
{
    $config_exists = false;
    try {
        $configs = OW::getConfig()->getValues('base');
        $config_exists = true;
    }catch (Exception $ex){}

    // log defaults
    $val_1 = defined('OW_LOG_LEVEL')? OW_LOG_LEVEL: Monolog\Logger::NOTICE;
    $val_2 = defined('OW_LOG_OUTPUT_HANDLER')? OW_LOG_OUTPUT_HANDLER: 'file';
    $val_3_def = defined('OW_LOG_OUTPUT_FORMAT')? OW_LOG_OUTPUT_FORMAT: 'line';
    $index_name = (defined('OW_URL_HOME'))? preg_replace( '/[\W]/', '', explode('/', OW_URL_HOME)[2]):'';
    $index_name = 'shub_'.$index_name;
    $handler_is_set = false;

    // file log
    if(!$config_exists || !isset($configs['file_log_enabled']) || $configs['file_log_enabled']){
        $val_1 = $config_exists && isset($configs['file_log_level']) ? $configs['file_log_level'] : $val_1;
        $val_3 = $config_exists && isset($configs['file_output_format']) ? $configs['file_output_format'] : $val_3_def;
        if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/mobile/services')!==false){
            $GLOBALS['LOG_FILE_NAME'] = 'mobile_services.log';
        }
        $LOG_FILE_NAME =  (isset($GLOBALS['LOG_FILE_NAME']))?$GLOBALS['LOG_FILE_NAME'] : 'log.log';
        $handler = new Monolog\Handler\StreamHandler(OW_DIR_LOG . $LOG_FILE_NAME, $val_1, true, 0666);
        $formatter = ($val_3=='json') ? new Monolog\Formatter\JsonFormatter() : new Monolog\Formatter\LineFormatter();
        $handler->setFormatter($formatter);
        OW::getLogger()->addLogHandler($handler);
        $handler_is_set = true;
    }

    // stdout log
    if($config_exists && isset($configs['stdout_log_enabled']) && $configs['stdout_log_enabled']){
        $val_1 = isset($configs['stdout_log_level']) ? $configs['stdout_log_level'] : $val_1;
        $val_3 = isset($configs['stdout_output_format']) ? $configs['stdout_output_format'] : $val_3_def;
        $handler = new Monolog\Handler\StreamHandler('php://stdout', $val_1);
        $formatter = ($val_3=='json') ? new Monolog\Formatter\JsonFormatter() : new Monolog\Formatter\LineFormatter();
        $handler->setFormatter($formatter);
        OW::getLogger()->addLogHandler($handler);
        $handler_is_set = true;
    }

    // syslog log
    if($config_exists && isset($configs['syslog_log_enabled']) && $configs['syslog_log_enabled']){
        $val_1 = isset($configs['syslog_log_level']) ? $configs['syslog_log_level'] : $val_1;
        $val_3 = isset($configs['syslog_output_format']) ? $configs['syslog_output_format'] : $val_3_def;
        $handler = new Monolog\Handler\SyslogHandler($index_name, LOG_USER, $val_1);
        $formatter = ($val_3=='json') ? new Monolog\Formatter\JsonFormatter() : new Monolog\Formatter\LineFormatter();
        $handler->setFormatter($formatter);
        OW::getLogger()->addLogHandler($handler);
        $handler_is_set = true;
    }

    // elastic log
    if($config_exists && isset($configs['elastic_log_enabled']) && $configs['elastic_log_enabled'] && class_exists("Elastica\Client")){
        $val_1 = isset($configs['elastic_log_level']) ? $configs['elastic_log_level'] : $val_1;
        $elk_host = isset($configs['elastic_host']) ? $configs['elastic_host']: 'localhost';
        $elk_port = isset($configs['elastic_port']) ? $configs['elastic_port']: 9200;
        $elk_un = isset($configs['elastic_username']) ? $configs['elastic_username']: null;
        $elk_pw = isset($configs['elastic_password']) ? $configs['elastic_password']: null;
        $config = ['host' => $elk_host, 'port' => $elk_port];
        if (isset($elk_pw)){
            $config['username'] = $elk_un;
            $config['password'] = $elk_pw;
        }
        $client = new Elastica\Client($config);
        $options = ['index' => $index_name, 'type' => 'log'];
        $handler = new Monolog\Handler\ElasticSearchHandler($client, $options, $val_1, true);
        $formatter = new Monolog\Formatter\ElasticaFormatter($options['index'], $options['type']);
        $handler->setFormatter($formatter);
        OW::getLogger()->addLogHandler($handler);
        $handler_is_set = true;
    }

    $GLOBALS['LOG_HANDLER_IS_SET'] = $handler_is_set;

    // disable logging to stderr
    if(!$handler_is_set){
        OW::getLogger()->addLogHandler(new \Monolog\Handler\NullHandler());
    }

    $errorManager = OW_ErrorManager::getInstance(OW_DEBUG_MODE);
}