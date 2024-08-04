<?php
define('_OW_', true);
define('DS', DIRECTORY_SEPARATOR);
define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DS);
define('UPDATE_DIR_ROOT', OW_DIR_ROOT . 'ow_updates' . DS);

require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
require_once OW_DIR_UTIL . 'file.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'autoload.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'error_manager.php';
require_once UPDATE_DIR_ROOT . 'classes' . DS . 'updater.php';
spl_autoload_register(array('UPDATE_Autoload', 'autoload'));

UPDATE_ErrorManager::getInstance(true);

$autoloader = UPDATE_Autoload::getInstance();
$autoloader->addPackagePointer('BASE_CLASS', OW_DIR_SYSTEM_PLUGIN . 'base' . DS . 'classes' . DS);
$autoloader->addPackagePointer('UPDATE', UPDATE_DIR_ROOT . 'classes' . DS);

//---------check admin or valid posts
OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());
OW::getSession()->start();
if (!OW::getUser()->isAuthenticated() || !OW::getUser()->isAdmin())
{
    if(!isPostValid() && !isScriptRequest()) {
        header('Location: ' . OW_URL_HOME . '404');
        exit();
    }
}

//---------END OF check admin

/* ------------------- Initialize all plugins ------------------------ */
OW::getPluginManager()->initPlugins();
$event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
OW::getEventManager()->trigger($event);
/* ------------------- End Initialize all plugins ------------------------ */

/* ------------------- Files and backups UPDATE ------------------------ */
FRMSecurityProvider::createBackupTables(new OW_Event(''));
/* ------------------- End Files and backups UPDATE END ------------------------ */

$db = Updater::getDbo();
$showResult = !isScriptRequest();

/* ------------------- CORE UPDATE  ------------------------ */
$version = FRMSecurityProvider::updateCore($db);
/* ----------------- CORE UPDATE END ------------------------ */

/* ------------------- Install Plugins ------------------------ */
if((isset($_GET['install_plugins']) || isset($_POST['install_plugins'])) || isScriptNeedInstallPlugins()) {
    $plugins = getPluginsForInstall();
    FRMSecurityProvider::installPlugins($plugins);
}
/* ------------------- End Install Plugins END ------------------------ */


/* ------------------- Language UPDATE ------------------------ */
if((isset($_GET['update_languages']) && $_GET['update_languages']) || (isset($_POST['update_languages']) && $_POST['update_languages']) || isScriptNeedLanguageUpdate() || ($version != null && !empty($version))) {
    FRMSecurityProvider::updateLanguages(true);
}
/* ------------------- End Language UPDATE END ------------------------ */

/* ----------------- PLUGIN UPDATE ------------------------ */
if((isset($_GET['update_all']) && $_GET['update_all']) ||
    (isset($_POST['update_all']) && $_POST['update_all']) ||
    isScriptAllPluginsToUpdate()){
    //Update static files of all plugins
    $updateStaticFiles = true;
    if((isset($_GET['do_not_update_statics']) && $_GET['do_not_update_statics']) ||
        (isset($_POST['do_not_update_statics']) && $_POST['do_not_update_statics']) ||
        isScriptHasNoUpdateStatics()) {
        $updateStaticFiles = false;
    }

    if($updateStaticFiles){
        FRMSecurityProvider::updateStaticFiles();
    }
    FRMSecurityProvider::updateAllPlugins($db, $showResult);
}else if(isset($_GET['plugin'])) {
    //Update static files of requested plugin
    FRMSecurityProvider::updatePluginStaticFilesWithPluginKey($_GET['plugin']);
    FRMSecurityProvider::updatePlugin($db, $_GET['plugin'], $showResult);
    OW::getEventManager()->trigger(new OW_Event('base.code.change'));
}
/* ------------------ PLUGIN UPDATE END -------------------- */

/* ----------------- THEME UPDATE ------------------------ */
if(isset($_GET['theme'])) {
    FRMSecurityProvider::updateTheme($db, $_GET['theme'], $showResult);
}
/* ------------------ THEME UPDATE END -------------------- */

if(!$showResult){
    exit();
}else{
    $urlToRedirect = OW::getRouter()->urlForRoute('admin_plugins_installed');
    if (!empty($_GET['back-uri'])) {
        $urlToRedirect = urldecode($_GET['back-uri']);
    }
    OW::getApplication()->redirect($urlToRedirect, OW::CONTEXT_DESKTOP);
}

FRMSecurityProvider::showCoreUpdateResult($version);

/* functions */

function isScriptAllPluginsToUpdate()
{
    if(!isScriptRequest()){
        return false;
    }
    return sizeof($_SERVER['argv'])>1 && in_array('update_all', $_SERVER['argv']);
}

function isScriptHasNoUpdateStatics()
{
    if(!isScriptRequest()){
        return false;
    }
    return sizeof($_SERVER['argv'])>1 && in_array('do_not_update_statics', $_SERVER['argv']);
}

function isScriptNeedInstallPlugins(){
    if(!isScriptRequest()){
        return false;
    }
    foreach ($_SERVER['argv'] as $value) {
        if (strpos($value, 'install_plugins') !== false) {
            return true;
        }
    }
    return false;
}

function isScriptNeedLanguageUpdate(){
    if(!isScriptRequest()){
        return false;
    }
    return sizeof($_SERVER['argv'])>1 && in_array('update_languages', $_SERVER['argv']);
}

function isScriptRequest()
{
    return php_sapi_name() === 'cli';
}

function getPluginsForInstall(){
    $plugins = null;
    $pluginsString = "";
    if(isset($_GET['install_plugins'])){
        $pluginsString = $_GET['install_plugins'];
    }else if(isset($_POST['install_plugins'])){
        $pluginsString = $_POST['install_plugins'];
    }else if(isScriptNeedInstallPlugins()){
        foreach ($_SERVER['argv'] as $value){
            if(strpos($value ,'install_plugins') !== false){
                $pluginsString = $value;
                $pluginsString = str_replace('install_plugins', '', $pluginsString);
                $pluginsString = str_replace('=', '', $pluginsString);
            }
        }
    }

    $pluginsString = trim($pluginsString);
    $plugins = explode(',', $pluginsString);
    return $plugins;
}

function isPostValid(){
    return isset($_POST['OW_AUTHENTICATE']) && defined('OW_AUTHENTICATE_COMMAND') && OW_AUTHENTICATE_COMMAND != null && OW_AUTHENTICATE_COMMAND == $_POST['OW_AUTHENTICATE'];
}