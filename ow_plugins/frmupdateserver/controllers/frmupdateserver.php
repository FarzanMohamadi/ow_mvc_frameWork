<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.controllers
 * @since 1.0
 */
class FRMUPDATESERVER_CTRL_Iisupdateserver extends OW_ActionController
{
    private $service;

    public function __construct()
    {
        parent::__construct();
    }

    public function index( $params = NULL )
    {
    }

    public function platformInfo( $params = NULL )
    {
        $versions = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion('core');
        if(empty($versions)){
            exit();
        }
        $lastVersion = $versions[0];
        exit(json_encode(array( 'build' => (string) $lastVersion->buildNumber,
            'version' => (string) $lastVersion->version,
            'info' => '',
            'log' => array())));
    }

    public function downloadUpdatePlatform( $params = NULL )
    {
        $versions = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion('core');
        if(empty($versions)){
            exit();
        }
        $lastVersion = $versions[0];

        FRMUPDATESERVER_BOL_Service::getInstance()->addUser('core',(string) $lastVersion->buildNumber);
        $this->downloadZipFile('core.zip', 'core-' . (string) $lastVersion->buildNumber . '.zip', 'core' . DS . 'updates' . DS . (string) $lastVersion->buildNumber);
    }

    public function downloadFullPlatform( $params = NULL )
    {
        $versions = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion('core');
        if(empty($versions)){
            exit();
        }
        $lastVersion = $versions[0];

        FRMUPDATESERVER_BOL_Service::getInstance()->addUser('core',(string) $lastVersion->buildNumber);
        $this->downloadZipFile('core.zip', 'core-' . (string) $lastVersion->buildNumber . '.zip', 'core' . DS . 'main' . DS . (string) $lastVersion->buildNumber);
    }

    public function getItemsUpdateInfo( $params = NULL )
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();
        $items = array();
        $returnResult = array();
        $postInformations = isset($_POST['info'])?json_decode($_POST['info'], true):array();
        $postedItems = isset($postInformations['items'])?$postInformations['items']:array();
        if(isset($postInformations['platform']['build'])){
            $coreUpdateDto = $service->getLatestVersion('core');
            if (isset($coreUpdateDto) && $postInformations['platform']['build'] < $coreUpdateDto->buildNumber) { // < OW::getConfig()->getValue('base', 'soft_build')
                $returnResult['update']['platform'] = true;
            }
        }

        foreach($postedItems as $item) {
            if (isset($item['key'])) {
                $pluginUpdateDto = $service->getLatestVersion($item['key']);
                if (isset($pluginUpdateDto) && $item['build'] < $pluginUpdateDto->buildNumber) {
                    $items[] = $item;
                }
            }
        }

        $returnResult['update']['items'] = $items;
        exit(json_encode($returnResult));
    }

    /**
     * Get all ignoring themes key
     */
    public function getIgnoreThemes(){
        exit(json_encode(FRMUPDATESERVER_BOL_Service::getInstance()->getIgnoreThemesKeyList()));
    }

    public function getItemInfo( $params = NULL )
    {
        header('Content-Type: text/html; charset=utf-8');

        if(!isset($_GET['key']) || !isset($_GET['developerKey'])){
            exit(json_encode(array( 'freeware' => true)));
        }
        $key = $_GET['key'];
        $developerKey = $_GET['developerKey'];

        $requestedPlugin = $this->getPlugin($key, $developerKey);
        $requestedTheme = $this->getTheme($key, $developerKey);

        if($requestedPlugin!=null){
            $json = json_encode(array( 'type' => 'plugin',
                'title' => iconv('UTF-8', 'UTF-8//IGNORE', $requestedPlugin['title']),
                'description' => iconv('UTF-8', 'UTF-8//IGNORE', $requestedPlugin['description']),
                'freeware' => '1',
                'build' => $requestedPlugin['build'],
                'changeLog' => array()), JSON_UNESCAPED_UNICODE);
            exit($json);
        }

        if ($requestedTheme!=null) {
            $json = json_encode(array('type' => 'theme',
                'title' => iconv('UTF-8', 'UTF-8//IGNORE', (string) $requestedTheme->title),
                'description' => iconv('UTF-8', 'UTF-8//IGNORE', (string) $requestedTheme->description),
                'freeware' => '1',
                'build' => (string) $requestedTheme->build,
                'changeLog' => array()), JSON_UNESCAPED_UNICODE);
            exit($json);
        }

        exit(json_encode(array( 'Update Server' => '1')));
    }

    public function getItem( $params = NULL )
    {
        header('Content-Type: text/html; charset=utf-8');
        $emptyResult = '_empty_plugin_or_developer_key_';
        $key = $_GET['key'];
        $developerKey = $_GET['developerKey'];

        FRMUPDATESERVER_BOL_Service::getInstance()->addUser($key,$developerKey);
        if(!isset($key) || !isset($developerKey)){
            exit($emptyResult);
        }

        $rootZipDirectory = OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir();
        $requestedPlugin = $this->getPlugin($key, $developerKey);
        $requestedTheme = $this->getTheme($key, $developerKey);

        if($requestedPlugin!=null){
            $dir = $requestedPlugin['path'];
            FRMUPDATESERVER_BOL_Service::getInstance()->checkPluginForUpdate($requestedPlugin['key'], $requestedPlugin['build'], $dir, $rootZipDirectory);
            $this->downloadZipFile(FRMUPDATESERVER_BOL_Service::getInstance()->getReplacedItemName($requestedPlugin['key']).'.zip', $requestedPlugin['key'] . '-' .$requestedPlugin['build'] . '.zip', 'plugins' . DS . $requestedPlugin['key'] . DS . $requestedPlugin['build']);
        }else if($requestedTheme!=null){
            FRMUPDATESERVER_BOL_Service::getInstance()->checkThemeForUpdate((string) $requestedTheme->key, (string)$requestedTheme->build, $rootZipDirectory);
            $this->downloadZipFile(FRMUPDATESERVER_BOL_Service::getInstance()->getReplacedItemName((string) $requestedTheme->key).'.zip', (string) $requestedTheme->key . '-' .(string) $requestedTheme->build . '.zip', 'themes' . DS . (string) $requestedTheme->key . DS . (string) $requestedTheme->build);
        }

        exit($emptyResult);
    }

    public function deleteAllVersions($params){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()) {
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                if(!isset($params['code']) && !isset($_GET['code'])){
                    throw new Redirect404Exception();
                }
                $code = isset($params['code'])?$params['code']:$_GET['code'];
                OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                    array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'frmupdateserver_delete_all_server_files')));
            }
            $service = FRMUPDATESERVER_BOL_Service::getInstance();
            $service->deleteAllVersions();
            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'delete_all_versions_successfully'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin'));
        }
    }

    /***
     * Checking all plugins,themes and core to generate downloading files.
     */
    public function checkAllForUpdate($params){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()) {
            if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
                if(!isset($params['code']) && !isset($_GET['code'])){
                    throw new Redirect404Exception();
                }
                $code = isset($params['code'])?$params['code']:$_GET['code'];
                OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                    array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'frmupdateserver_update_server_files')));
            }
            $rootZipDirectory = OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir();

            //checking plugins for updating
            if (!OW::getStorage()->fileExists($rootZipDirectory . 'plugins')) {
                OW::getStorage()->mkdir($rootZipDirectory . 'plugins');
            }
            $xmlPlugins = BOL_PluginService::getInstance()->getPluginsXmlInfo();
            foreach ($xmlPlugins as $plugin) {
                if (!in_array($plugin['key'], FRMUPDATESERVER_BOL_Service::getInstance()->getIgnorePluginsKeyList())) {
                    $dir = $plugin['path'];
                    FRMUPDATESERVER_BOL_Service::getInstance()->checkPluginForUpdate($plugin['key'], $plugin['build'], $dir, $rootZipDirectory);
                }
            }


            //checking themes for updating
            if (!OW::getStorage()->fileExists($rootZipDirectory . 'themes')) {
                OW::getStorage()->mkdir($rootZipDirectory . 'themes');
            }
            $themes = UTIL_File::findFiles(OW_DIR_THEME, array('xml'), 1);
            foreach ($themes as $themeXml) {
                if (basename($themeXml) === BOL_ThemeService::THEME_XML) {
                    $theme = simplexml_load_file($themeXml);
                    if (!in_array((string)$theme->key, FRMUPDATESERVER_BOL_Service::getInstance()->getIgnoreThemesKeyList())) {
                        FRMUPDATESERVER_BOL_Service::getInstance()->checkThemeForUpdate((string)$theme->key, (string)$theme->build, $rootZipDirectory);
                    }
                }
            }

            //checking core for updating
            FRMUPDATESERVER_BOL_Service::getInstance()->checkCoreForUpdate($rootZipDirectory);

            //checking mobile source codes
            FRMUPDATESERVER_BOL_Service::getInstance()->checkMobileDevelopmentSourceCodes();

            //checking shub mobile versions
            FRMUPDATESERVER_BOL_Service::getInstance()->checkShubMobileVersion();

            OW::getFeedback()->info(OW::getLanguage()->text('frmupdateserver', 'all_items_checked'));
            $this->redirect(OW::getRouter()->urlForRoute('frmupdateserver.admin'));
        }
    }

    public function downloadZipFile($zipname, $buildNumber , $type=null){
        $zipPath =  FRMUPDATESERVER_BOL_Service::getInstance()->getZipPathByKey(FRMUPDATESERVER_BOL_Service::getInstance()->getReplacedItemName($buildNumber), $type);
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=" . $zipname);
        header("Content-length: " . filesize($zipPath));
        header("Pragma: no-cache");
        header("Expires: 0");
        set_time_limit(0);
        ob_clean();
        flush();
        readfile($zipPath);
        exit();
    }

    public function getTheme($key, $developerKey){
        $themes = UTIL_File::findFiles(OW_DIR_THEME, array('xml'), 1);
        foreach ($themes as $themeXml) {
            if ( basename($themeXml) === BOL_ThemeService::THEME_XML ) {
                $theme = simplexml_load_file($themeXml);
                if ((string) $theme->key == $key && (string) $theme->developerKey == $developerKey) {
                    return $theme;
                }
            }
        }

        return null;
    }

    public function getPlugin($key, $developerKey){
        $xmlPlugins = BOL_PluginService::getInstance()->getPluginsXmlInfo();

        foreach ($xmlPlugins as $plugin) {
            if($plugin['key'] == $key && $plugin['developerKey'] == $developerKey){
                return $plugin;
            }
        }

        return null;
    }

    public function updateStaticFiles(){
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAdmin()){
            FRMSecurityProvider::updateStaticFiles();
            OW::getFeedback()->info('Static files updated successfully');
        }
        $this->redirect(OW_URL_HOME);
    }


    public function viewDownloadPage(){
        $service = FRMUPDATESERVER_BOL_Service::getInstance();

        $this->assign('download_themes_description', OW::getLanguage()->text('frmupdateserver', 'download_themes_description', array('url' => $service->getPathOfFTP().'themes')));
        $this->assign('download_plugins_description', OW::getLanguage()->text('frmupdateserver', 'download_plugins_description', array('url' => $service->getPathOfFTP().'plugins')));

        $allVersionsOfCore = $service->getAllVersion('core');
        $versionNumber = '';
        $buildNumber = '';
        $time = '-';
        if(sizeof($allVersionsOfCore)>0){
            $time = UTIL_DateTime::formatSimpleDate($allVersionsOfCore[0]->time);
            $versionNumber = $allVersionsOfCore[0]->version;
            $buildNumber = $allVersionsOfCore[0]->buildNumber;
        }
        $allVersions = $service->getAllVersion();

        $this->assign('download_core_main_description', OW::getLanguage()->text('frmupdateserver', 'download_core_main_description'));
        $this->assign('download_core_update_description', OW::getLanguage()->text('frmupdateserver', 'download_core_update_description', array('version' => $versionNumber)));
        $this->assign('download_last_core_version', OW::getLanguage()->text('frmupdateserver', 'download_last_core_version', array('version' => $versionNumber)));
        $this->assign('download_last_core_update', OW::getLanguage()->text('frmupdateserver', 'download_last_core_update-version', array('version' => $versionNumber)));
        $this->assign('download_last_core_build_version', OW::getLanguage()->text('frmupdateserver', 'download_last_core_build_version', array('value' => $buildNumber)));
        $this->assign('download_last_build_version_label', OW::getLanguage()->text('frmupdateserver', 'download_last_build_version_label', array('value' => $buildNumber)));
        $this->assign('download_last_core_update_build_version', OW::getLanguage()->text('frmupdateserver', 'download_last_core_update_build_version', array('value' => $buildNumber)));
        $this->assign('urlOfCoreMainLatestVersions', $service->getJavascriptOfLastVersionsOfItem('core', $allVersions, 'core/main'));
        $this->assign('urlOfSha256CoreMainLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/main').'.sha256');
        $this->assign('urlOfCoreUpdateLatestVersions', $service->getJavascriptOfLastVersionsOfItem('core', $allVersions, 'core/updates'));
        $this->assign('urlOfSha256CoreUpdateLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/updates').'.sha256');
        $this->assign('urlOfCoreMainVersions', $service->getPathOfFTP() . 'core/main');
        $this->assign('urlOfCoreUpdateVersions', $service->getPathOfFTP() . 'core/updates');
        $this->assign('urlOfAllCoreVersions', $service->getPathOfFTP() . 'core');
        $this->assign('urlOfPluginsVersions', $service->getPathOfFTP() . 'plugins');
        $this->assign('urlOfThemesVersions', $service->getPathOfFTP() . 'themes');
        $this->assign('date_core_released', $time);

        $categoriesSelect=FRMUPDATESERVER_BOL_CategoryDao::getInstance()->findAll();
        foreach ($categoriesSelect as $categoriesSelectItem ){
            $categoriesItem['label']=$categoriesSelectItem->label;
            $categoriesItem['id']=$categoriesSelectItem->id;
            $categoryList[]=$categoriesItem;
        }

        $this->assign('categoryList',$categoryList);

        $pluginItems = $service->getItems('plugin');
        $pluginItemsInformation = array();
        foreach($pluginItems as $item){
            $itemInfo = $this->findItemInArrayListOfItems($allVersions, $item->key);
            $itemInformation = array();
            $categoryObject=FRMUPDATESERVER_BOL_PluginInformationDao::getInstance()->getItemInformationById($item->id);
            if (isset($categoryObject)) {
                $categories = json_decode($categoryObject->categories);
                $categoryString="";
                foreach ($categories as $category){
                    $categoryString=$categoryString."category_".$category." ";
                }
                $itemInformation['categories'] =$categoryString;
            }
            $itemInformation['name'] = $item->name;
            $itemInformation['key'] = $item->key;
            $itemInformation['description'] = $item->description;
            $itemInformation['header'] = OW::getLanguage()->text('frmupdateserver','plugin') . ' ' . $item->key;
            $itemInformation['versionsUrl'] = $service->getPathOfFTP() . 'plugins/'. $item->key;
            $itemInformation['downloadUrl'] =  $service->getJavascriptOfLastVersionsOfItem($item->key, $allVersions, 'plugins/'.$item->key);
            $itemInformation['downloadSha256Url'] =  $service->getUrlOfLastVersionsOfItem($item->key, $allVersions, 'plugins/'.$item->key).'.sha256';
            $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getUserFilesUrl() . $item->image;
            if($itemInfo!=null) {
                $itemInformation['releasedDate'] = UTIL_DateTime::formatSimpleDate($itemInfo->time);
                $itemInformation['version'] = $itemInfo->buildNumber;
            }
            if(isset($item->guidelineurl) && !empty($item->guidelineurl)){
                $itemInformation['guidelineUrl'] = $item->guidelineurl;
            }
            $pluginItemsInformation[] = $itemInformation;
        }

        $themeItems = $service->getItems('theme');
        $themeItemsInformation = array();
        foreach($themeItems as $item){
            $itemInformation = array();
            $itemInfo = $this->findItemInArrayListOfItems($allVersions, $item->key);
            $itemInformation['name'] = $item->name;
            $itemInformation['key'] = $item->key;
            $itemInformation['description'] = $item->description;
            $itemInformation['header'] = OW::getLanguage()->text('frmupdateserver','theme') . ' ' . $item->key;
            $itemInformation['versionsUrl'] = $service->getPathOfFTP() . 'themes/'. $item->key;
            $itemInformation['downloadUrl'] =  $service->getJavascriptOfLastVersionsOfItem($item->key, $allVersions, 'themes/'.$item->key);
            $itemInformation['downloadSha256Url'] =  $service->getUrlOfLastVersionsOfItem($item->key, $allVersions, 'themes/'.$item->key).'.sha256';
            $itemInformation['image'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getUserFilesUrl() . $item->image;
            if($itemInfo!=null) {
                $itemInformation['releasedDate'] = UTIL_DateTime::formatSimpleDate($itemInfo->time);
                $itemInformation['version'] = $itemInfo->buildNumber;
            }
            if(isset($item->guidelineurl)){
                $itemInformation['guidelineUrl'] = $item->guidelineurl;
            }
            $themeItemsInformation[] = $itemInformation;
        }

        $this->assign('pluginItems', $pluginItemsInformation);
        $this->assign('themeItems', $themeItemsInformation);

        $this->assign('coreImageUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/core.png');
        $this->assign('pluginsImageUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/plugins.png');
        $this->assign('sha256IconUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/sha256.png');
        $this->assign('themesImageUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/themes.png');
        $this->assign('downloadIconUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/download.png');
        $this->assign('archivesIconUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/archive.png');
        $this->assign('guidelineIconUrl', OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticUrl() . 'images/help.png');
        $this->assign('plugins_header', OW::getLanguage()->text('frmupdateserver','plugins_sample'));
        $this->assign('themes_header', OW::getLanguage()->text('frmupdateserver','themes_sample'));
        $this->assign('core_header', OW::getLanguage()->text('frmupdateserver','core'));
    }

    public function getDataPostUrl($params){
        if(isset($_POST['publicFile']) && $_POST['publicFile']){
            $files = FRMUPDATESERVER_BOL_Service::getInstance()->getPublicFilesOfSource($_POST['path']);
        }else{
            $files = FRMUPDATESERVER_BOL_Service::getInstance()->getFilesOfSource($_POST['type'], $_POST['key'], $_POST['version']);
        }
        $files['url'] = OW::getRouter()->urlForRoute('frmupdateserver.data-post-url');
        exit(json_encode($files));
    }

    public function downloadFile($params){
        $files = FRMUPDATESERVER_BOL_Service::getInstance()->downloadFile($_POST['key'], $_POST['version']);
        exit(json_encode($files));
    }

    public function findItemInArrayListOfItems($items, $key){
        foreach($items as $item){
            if($item->key == $key){
                return $item;
            }
        }

        return null;
    }
}
