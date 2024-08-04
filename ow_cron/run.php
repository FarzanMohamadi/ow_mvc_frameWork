<?php
/**
 * @package ow_cron
 * @since 1.0
 */
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', substr(dirname(__FILE__), 0, - strlen('ow_cron')));

define('OW_CRON', true);

$GLOBALS['LOG_FILE_NAME'] = 'cron.log';
require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

//if ( !isset($_GET['ow-light-cron']) && !OW::getConfig()->getValue('base', 'cron_is_configured') )
//{
//    OW::getConfig()->saveConfig('base', 'cron_is_configured', 1, null, false);
//}

OW::getRouter()->setBaseUrl(OW_URL_HOME);

date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));
OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

OW::getPluginManager()->initPlugins();
$event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
OW::getEventManager()->trigger($event);

//init cache manager
$beckend = OW::getEventManager()->call('base.cache_backend_init');

if ( $beckend !== null )
{
    OW::getCacheManager()->setCacheBackend($beckend);
    OW::getCacheManager()->setLifetime(3600);
    OW::getDbo()->setUseCashe(true);
}

OW::getThemeManager()->initDefaultTheme();

// setting current theme
$activeThemeName = OW::getConfig()->getValue('base', 'selectedTheme');

if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME)
{
    try{
        $activeTheme = BOL_ThemeService::getInstance()->getThemeObjectByKey(trim($activeThemeName), OW::getApplication()->isMobile());
    }catch (InvalidArgumentException $e) {
        $activeTheme = null;
    }
    if (isset($activeTheme)) {
        OW_ThemeManager::getInstance()->setCurrentTheme($activeTheme);
    }
}

function loadPluginCron($plugin_key, &$className, &$classInstance)
{
    $pluginRootDir = OW::getPluginManager()->getPlugin($plugin_key)->getRootDir();
    if (OW::getStorage()->fileExists($pluginRootDir . 'cron.php')) {
        include $pluginRootDir . 'cron.php';
        $className = strtoupper($plugin_key) . '_Cron';
        $classInstance = new $className;
    } else {
        $className = null;
        $classInstance = null;
    }
}

function runJob($className, $classInstance, $job, $lastExecutionBound)
{
    $methodName = $className . '::' . $job;
    $runJob = BOL_CronService::getInstance()->getJobByMethodName($methodName);
    $runStamp = isset($runJob) ? $runJob->runStamp : 0;
    if ($runStamp <= $lastExecutionBound) {
        if(empty($runJob)){
            $runJob = new BOL_CronJob();
            $runJob->methodName = $methodName;
        }
        $runJob->runStamp = time();
        BOL_CronService::getInstance()->batchSave(Array($runJob));

        try {
            $classInstance->$job();
        } catch (Exception $e) {
            OW::getLogger()->writeLog(OW_Log::ERROR, "Error in running $className::$job: $e.");
        }
    }
}

function getNamedArguments()
{
    $result = Array();
    if (isset($_SERVER['argv'])) {
        foreach ($_SERVER['argv'] as $arg) {
            $index = strpos($arg, '=');
            if ($index !== false) {
                $name = substr($arg, 0, $index);
                $value = substr($arg, $index + 1);
                if (isset($result[$name])) {
                    OW::getLogger()->writeLog(OW_Log::ERROR, "Error: Multiple values for argument $name.");
                    exit(1);
                } else {
                    $result[$name] = $value;
                }
            }
        }
    }
    return $result;
}

$args = getNamedArguments();
if (isset($args['plugin_key'])) {
    if (!isset($args['job']) || !isset($args['timestamp'])) {
        OW::getLogger()->writeLog(OW_Log::ERROR, "Error: Missing some required arguments.");
        exit(2);
    }

    $plugin_key = $args['plugin_key'];
    $plugin = BOL_PluginService::getInstance()->findPluginByKey($plugin_key);
    if ($plugin->isActive()) {
        loadPluginCron($plugin_key, $className, $classInstance);
        runJob($className, $classInstance, $args['job'], intval($args['timestamp']));
    } else {
        OW::getLogger()->writeLog(OW_Log::WARNING, "Warning: Ignoring a cron job for plugin $plugin_key since it's inactive.");
    }
} else {
    $plugins = BOL_PluginService::getInstance()->findActivePlugins();
    /* @var $plugin BOL_Plugin */
    foreach ($plugins as $plugin) {
        /* @var $classInstance OW_Cron */
        loadPluginCron($plugin->getKey(), $className, $classInstance);

        // Ignore plugins without a cron class
        if ($classInstance === null) {
            continue;
        }

        $jobs = $classInstance->getJobList();
        foreach ($jobs as $job => $interval) {
            runJob($className, $classInstance, $job, time() - 60 * $interval);
        }
    }
}
