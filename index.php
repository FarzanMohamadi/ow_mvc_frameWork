<?php
stream_wrapper_unregister('phar');
define('_OW_', true);

define('DS', DIRECTORY_SEPARATOR);

define('OW_DIR_ROOT', dirname(__FILE__) . DS);


require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');

if ( OW::getStorage()->fileExists(OW_DIR_ROOT . 'ow_install' . DS . 'install.php') )
{
    include OW_DIR_ROOT . 'ow_install' . DS . 'install.php';
}

OW::getSession()->start();

$application = OW::getApplication();


if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('before_app_init');
}

$application->init();

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('after_app_init');
}

$event = new OW_Event(OW_EventManager::ON_APPLICATION_INIT);
OW::getEventManager()->trigger($event);

$application->route();

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('after_route');
}

$event = new OW_Event(OW_EventManager::ON_AFTER_ROUTE);
OW::getEventManager()->trigger($event);

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('before_controller_call');
}

$application->handleRequest();

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('after_controller_call');
}

$event = new OW_Event(OW_EventManager::ON_AFTER_REQUEST_HANDLE);

OW::getEventManager()->trigger($event);

$application->finalize();

if ( OW_PROFILER_ENABLE )
{
    UTIL_Profiler::getInstance()->mark('after_finalize');
}

$application->returnResponse();

