<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmthememanager
 * @since 1.0
 */

class FRMTHEMEMANAGER_BOL_Service
{
    private static $classInstance;

    /*Public Variables hint: In order to add new custom field to these specifications only action is to add language keys in XML files with the same name*/
    public $colorsList = array('primaryColor',
        'secondaryColor',
        'backgroundAndBorderColor',
        'backgroundColor',
        'HeaderBackgroundColor',
        'footerBackgroundColor',
        'HeaderItemBackgroundColor',
        'HeaderItemHoverTextColor',
        'HeaderItemTextColor',
        'linksColor',
        'linksColorHover',
        'verifyColor');
    public $urlsList = array(
/*        array('name'=> 'tabIcons', 'class' => '.ow_content_menu li a span {background-image: url( $URL );}'),*/
        array('name'=> 'Header', 'class' => '.ow_header_pic {background-image: url( $URL ); background-blend-mode: initial;}'),
        array('name'=> 'background', 'class' => 'body {background: url( $URL );}'),
        array('name'=> 'mainLogo', 'class' => '.ow_logo {background: url( $URL ) no-repeat;} .ow.base_sign_in form .ow_sign_in_logo {background: url( $URL ) no-repeat; background-position: center; background-size: 120px; }'),
        array('name'=> 'mainWhiteLogo', 'class' => '.ow_copyright_logo {background-image: url( $URL );}'),
        array('name'=> 'headerLogo', 'class' => '.ow_site_panel.clearfix .ow_logo {background: url( $URL ) no-repeat center center;background-size: contain;}')
    );
    public $configList = array(
        array('name'=>'noHeader','fileName'=>'noHeader.css'),
        array('name'=>'no','fileName'=>'no.css'),
        array('name'=>'handLikeIcon','fileName'=>'handLikeIcon.css'),
        array('name'=>'blendHeader','fileName'=>'blendHeader.css'),
    );



    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function getParentThemes(){
        $parentThemes = array();
        $themeService = BOL_ThemeService::getInstance();
        $allThemes = $themeService->findAllThemes();
        foreach ($allThemes as $theme){
            $themeInformation = $themeService->getThemeXmlInfoForKey($theme->key);
            if(isset( $themeInformation['coreGeneration']) && $themeInformation['coreGeneration'] > 1){
                $parentThemes[]= $theme;
            }
        }
        return $parentThemes;
    }

    public function getParentColors( $key, $specificColor = null ){
        $colors = array();
        if($key == null){
            return false;
        }
        $themeService = BOL_ThemeService::getInstance();
        $parentThemeCSS = OW::getStorage()->fileGetContent($themeService->getRootDir($key) . BOL_ThemeService::CSS_FILE_NAME);
        $parentThemeControls = $themeService->getThemeControls($parentThemeCSS);
        if( $specificColor == null){
            foreach ($this->colorsList as $colorName){
                $values[$colorName] = $parentThemeControls[$colorName]['defaultValue'];
                $colors[$colorName] = $values[$colorName];
            }
        }else{
            $values[$specificColor] = $parentThemeControls[$specificColor]['defaultValue'];
            $colors = $values[$specificColor];
        }
        return $colors;
    }

    public function processFormValues($values){

        $colors = array();
        $urls = array();
        $values['themeKey'] = str_replace(" ","",$values['themeKey']);
        $filesDir = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesDir();

        if(isset($values['form_name'])){
            unset($values['form_name']);}
        if(isset($values['csrf_hash'])){
            unset($values['csrf_hash']);}
        if(!isset($values['csrf_token'])){
            $values['csrf_token']= UTIL_String::getRandomString(32);
        }

        foreach ($this->colorsList as $colorName){
            if( $values[$colorName] !=null ){
                if( $values[$colorName][0] != '#' ){
                    $values[$colorName]='#'.$values[$colorName];
                }
            }else{
                $values[$colorName] = $this -> getParentColors( $values['parentTheme'], $colorName );
            }
            $colors[$colorName] = $values[$colorName];
        }


        if( ow::getConfig()->configExists('frmthememanager', $values['themeKey']) ){
            $themeData = FRMTHEMEMANAGER_BOL_Service::getInstance()->getThemeArrayByKey( $values['themeKey'] ) ;
            $urls = $themeData['urls'] ;
        }

        $filesRemoveList = (array) json_decode( $_POST['fileRemoveList'] );
        if ( isset($filesRemoveList) && $filesRemoveList != null ){
            foreach ($filesRemoveList as $fileRemoveItem){
                unset( $urls[$fileRemoveItem] ) ;
            }
        }

        foreach ($this->urlsList as $urlName){

            if(isset($_FILES[$urlName['name']]['name']) && $_FILES[$urlName['name']]['name'] != null){
                $extension = UTIL_File::getExtension($_FILES[$urlName['name']]['name']);
                $name = $values['parentTheme']."_".$values['themeKey']."_".$urlName['name'];
                $fileDir = $filesDir . $name .".". $extension;
                if (OW::getStorage()->fileExists($fileDir)) {
                    OW::getStorage()->removeFile($fileDir);
                }
                if (isset($filesRemoveList) && in_array($urlName,$filesRemoveList)) {
                    continue;
                }else{
                    OW::getStorage()->copyFile( $_FILES[$urlName['name']]['tmp_name'], $fileDir, true);
                    $urls[$urlName['name']] =  $name .".". $extension;
                }
            }elseif(isset($urls[$urlName['name']])){
                $extension = UTIL_File::getExtension($urls[$urlName['name']]);
                $pluginUrl = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesUrl();
                $name = $values['parentTheme']."_".$values['themeKey']."_".$urlName['name'];
                $urls[$urlName['name']] = $pluginUrl.$name .".". $extension;
            }
        }

        $values['themeColors'] = $colors;
        $values['urls'] = $urls;
        $values['fileName'] = $values['parentTheme']."_".$values['themeKey'];
        $values['mobileFileName'] = $values['parentTheme']."_".$values['themeKey']."_mobile";
        $values['configs'] = $values['themeConfigs'];

        return $values;
    }

    public function saveNewTheme($values, $edit=false, $overwrite=false){
        if(!OW::getUser()->isAuthenticated() || !ow::getUser()->isAdmin() ){
            return null;
        }
        if($edit){
            ow::getConfig()->saveConfig('frmthememanager', $values['themeKey'], json_encode($values));
        }else {
            $formerTheme = $this->getThemeArrayByKey( $values['themeKey'] );
            if( $formerTheme!=null ){
                if ( $overwrite !== true ){
                    $values['themeKey'] = $values['themeKey'].'_'.UTIL_String::getRandomString(5);
                    $this->updateThemeList ( $values['themeKey'] );
                }
            }else{
                $this->updateThemeList ( $values['themeKey'] );
            }
            ow::getConfig()->saveConfig('frmthememanager', $values['themeKey'], json_encode($values));
        }
        $fileStatus = $this->generateNewCssFile($values);
        if ($fileStatus == false){
            return false;
        }
        FRMSecurityProvider::updateCachedEntities();
        return true;
    }

    public function generateNewCssFile($values){
        $themeService = BOL_ThemeService::getInstance();
        $parentThemeCSS = OW::getStorage()->fileGetContent($themeService->getRootDir($values['parentTheme']) . BOL_ThemeService::CSS_FILE_NAME);
        $parentThemeMobileCSS = OW::getStorage()->fileGetContent($themeService->getRootDir($values['parentTheme'], true) . BOL_ThemeService::CSS_FILE_NAME);
        $parentThemeTabsSvgIconSet = OW::getStorage()->fileGetContent($themeService->getRootDir($values['parentTheme']) . 'images/tab_icons_1.svg');
        $parentThemeControls = $themeService->getThemeControls($parentThemeCSS);

        $newThemeCSS = $parentThemeCSS;
        $newThemeMobileCSS = $parentThemeMobileCSS;
        foreach ($this->colorsList as $colorName){
            $Color = $parentThemeControls[$colorName]['defaultValue'];
            if( $Color !=null || $Color !=''){
                $newThemeCSS = str_replace( $Color, $values[$colorName], $newThemeCSS );
                $newThemeMobileCSS = str_replace( $Color, $values[$colorName], $newThemeMobileCSS );
                $parentThemeTabsSvgIconSet = str_replace( $Color, $values[$colorName], $parentThemeTabsSvgIconSet );
            }
        }

        $newThemeCSS = str_replace('url(','url('.BOL_ThemeService::getInstance()->getStaticUrl($values['parentTheme']) ,$newThemeCSS);
        $newThemeMobileCSS = str_replace('url(','url('.BOL_ThemeService::getInstance()->getStaticUrl($values['parentTheme'], true) ,$newThemeMobileCSS);
        $automaticStyleHeaderText =  "\r\n\r\n\r\n/*========================================================================================================
       		                    		 Custom Automatic Classes
  ========================================================================================================*/\r\n";
        $newThemeCSS = $newThemeCSS . $automaticStyleHeaderText;
        $newThemeMobileCSS = $newThemeMobileCSS . $automaticStyleHeaderText;
        $configList = $values['configs'];
        if($configList != null){
            foreach($configList as $configItem){
                $configStyle = OW::getStorage()->fileGetContent( OW::getPluginManager()->getPlugin('frmthememanager')->getPublicStaticDir() ."css". DS . $configItem);
                if($configItem == 'handLikeIcon.css'){
                    $configStyle = str_replace( '$URL', OW::getPluginManager()->getPlugin('frmthememanager')->getStaticUrl() , $configStyle );
                }
                $newThemeCSS = $newThemeCSS . "\r\n" .$configStyle  ;
                $newThemeMobileCSS = $newThemeMobileCSS . "\r\n" .$configStyle  ;
            }
        }
        $urlsList = $values['urls'];
        foreach($this->urlsList as $urlItem){
            if(isset($urlsList[$urlItem['name']]) && $urlsList[$urlItem['name']] != null ){
                $newImageCSS = str_replace( '$URL', $urlsList[$urlItem['name']], $urlItem['class'] );
                $newThemeCSS = $newThemeCSS . "\r\n" . $newImageCSS;
            }
        }

        $newThemeCSS = $newThemeCSS . "\r\n" . $values['themeStyle'];
        $name = $values['parentTheme']."_".$values['themeKey'];
        $this->writeFileToPluginPath($name,$newThemeCSS, '.css');
        $name = $values['parentTheme']."_".$values['themeKey']."_mobile";
        $this->writeFileToPluginPath($name,$newThemeMobileCSS, '.css');
        $name = $values['parentTheme']."_".$values['themeKey']."_tab_icons_1";
        $this->writeFileToPluginPath($name,$parentThemeTabsSvgIconSet, '.svg');
        return true;
    }


    public function writeFileToPluginPath( $name, $content, $extension ){
        $fileName = $name.$extension;
        $filesPath =  $pluginStaticUrl = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesDir();
        $CssFilePath = $filesPath . $fileName;
        if (OW::getStorage()->fileExists($CssFilePath)) {
            OW::getStorage()->removeFile($CssFilePath);
        }
        OW::getStorage()->fileSetContent($CssFilePath, $content);
    }

    public function getCustomTheme(OW_Event $event){
        $config = OW::getConfig();
        $pluginUrl = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesUrl();
        $styleSheetUrl = null;
        $mobileStyleSheetUrl = null;
        $childTheme = $config->getValue('frmthememanager', 'activeTheme');
        if( isset($childTheme) && $childTheme !=null ){
            $themeObject = json_decode( ow::getConfig()->getValue( 'frmthememanager', $childTheme) );
            $fileName = $themeObject->fileName . '.css';
            $mobileFileName = $themeObject->mobileFileName . '.css';
            $styleSheetUrl = $pluginUrl.$fileName;
            $mobileStyleSheetUrl = $pluginUrl.$mobileFileName;
        }
        $event->setData(array('url' => $styleSheetUrl,'mobileUrl' => $mobileStyleSheetUrl, 'CurrentActiveTheme'=>$childTheme ) );
    }

    public function removeTheme( $key ){
        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if(!OW::getUser()->isAuthenticated() && !$adminEvent){
            return null;
        }
        ow::getConfig()->deleteConfig( 'frmthememanager', $key);
        $this->updateThemeList ( $key, false );
        if(OW::getConfig()->getValue('frmthememanager', 'activeTheme') == $key){
            OW::getConfig()->saveConfig('frmthememanager', 'activeTheme', null);
        }
    }

    public function activateTheme( $key ){
        OW::getConfig()->saveConfig('frmthememanager', 'activeTheme', $key);
    }

    public function deactivateThemes( $key ){
        if($key){
            OW::getConfig()->saveConfig('frmthememanager', 'activeTheme', null);
        }
    }

    public function updateThemeList ( $key, $addOrRemove = true ){
        $themesList = (array) json_decode( ow::getConfig()->getValue('frmthememanager', 'themesList') );
        if($addOrRemove){
            $themesList[] = $key;
        }else{
            $themesList = array_diff($themesList, array($key));
        }
        ow::getConfig()->saveConfig('frmthememanager', 'themesList', json_encode($themesList));
        FRMSecurityProvider::updateCachedEntities();
    }

    public function findAllThemes(){
        $themesKeys = (array) json_decode( ow::getConfig()->getValue('frmthememanager', 'themesList') );
        $themesList = array();
        foreach ($themesKeys as $themesKey) {
            $themesList[] = $this->getThemeArrayByKey ($themesKey);
        }
        return $themesList;
    }

    public function getThemeArrayByKey ($themesKey){
        $themeArray = (array) json_decode( ow::getConfig()->getValue( 'frmthememanager', $themesKey) );
        if($themeArray == null){
            return null;
        }else{
            $themeArray['themeColors'] = (array) $themeArray['themeColors'];
            $themeArray['urls'] = (array) $themeArray['urls'];
            return $themeArray;
        }
    }

    public function updateThemesEvent(OW_Event $event){
        $params = $event->getParams();
        $parentThemes = $this ->getParentThemes();
        foreach ($parentThemes as $parentTheme) {
            if( $parentTheme->key == $params['themeKey'] ){
                $allChildThemes = (array) json_decode( OW::getConfig()->getValue('frmthememanager','themesList'));
                foreach ($allChildThemes as $childTheme) {
                    $themeArray = $this -> getThemeArrayByKey( $childTheme );
                    if($themeArray['parentTheme'] == $params['themeKey'] ){
                        $this -> saveNewTheme( $themeArray , true);
                    }
                }
            }
        }
        FRMSecurityProvider::updateCachedEntities();
    }

    public function updateAllThemesList( $list = null ){
        if($list == null ){
            $list = (array) json_decode( OW::getConfig()->getValue('frmthememanager','themesList'));
        }
        foreach ($list as $childTheme) {
            $themeArray = $this -> getThemeArrayByKey( $childTheme );
            $themeArray = $this -> processFormValues( $themeArray );
            $this -> saveNewTheme( $themeArray , true);
        }
        FRMSecurityProvider::updateCachedEntities();
    }

    public function addFooterCustomTags(OW_Event $event){
        $currentTheme = OW::getConfig()->getValue('frmthememanager','activeTheme');
        if ( isset($currentTheme) && $currentTheme != null ){
            $getThemeArray = $this -> getThemeArrayByKey($currentTheme);
            $event -> setData(array('hasCustomTag'=> true, 'tag'=>$getThemeArray['footerTags']));
        }else{
            $event -> setData(array('hasCustomTag'=> false) );
        }
    }

    public function getAllTheme(OW_Event $event){
        $allThemes = $this->findAllThemes();
        $activeTheme = OW::getConfig()->getValue('frmthememanager', 'activeTheme');
        $event -> setData(array('isPluginActive'=>true, 'allThemes'=> $allThemes, 'activeTheme'=> $activeTheme ) );
        OW::getLanguage()->addKeyForJs('frmthememanager', 'delete_theme_confirm');
    }

    public function exportTheme( $themeKey ){
        $themeObject = $this->getThemeArrayByKey($themeKey);
        $name = $themeObject['parentTheme']."_".$themeObject['themeKey'];
        $this-> writeFileToPluginPath( $name, json_encode($themeObject), '.txt' );
        return $this-> themeZipCreator($themeObject);
    }

    public function themeZipCreator($themeObject){
        $pluginDir = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesDir();
        $pluginUrl = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesUrl();
        $txtName = $themeObject['parentTheme'].'_'.$themeObject['themeKey'];
        $zipDir = $pluginDir.$txtName.'.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipDir, ZipArchive::CREATE)==TRUE) {
            $zip->addFile( $pluginDir.$txtName.'.txt', $txtName.'.txt' );
            foreach ($themeObject['urls'] as $url){
                $filename = str_replace($pluginUrl,'',$url);
                $file = $pluginDir.$filename;
                $zip->addFile( $file, $filename );
            }
        }
        $zip->close();
        return $pluginUrl.$txtName.'.zip';
    }

    public function extractTheme(){
        $fileName = UTIL_String::getRandomString(5);
        $path = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesDir();
        OW::getStorage()->copyFile( $_FILES['themeFile']['tmp_name'], $path.$fileName.'_temp.zip', true);
        OW::getStorage()->mkdir($path.$fileName);
        $zip = new ZipArchive();
        $zip->open($path.$fileName.'_temp.zip');
        $zip->extractTo($path.$fileName);
        $zip->close();
        $filesList = scandir ($path.$fileName);
        $extensions = array();
        foreach ($filesList as $item){
            $extension = UTIL_File::getExtension($path.$fileName.DS.$item);
            if( $extension == 'txt'){
                $values = (array) json_decode( OW::getStorage()->fileGetContent($path.$fileName.DS.$item) );
            }
            if(!in_array($extension,$extensions) && $extension !="" && $extension !="txt"){
                $extensions[] = $extension;
            }
            OW::getStorage()->removeFile($path.$fileName.DS.$item);
        }
        OW::getStorage()->removeDir($path.$fileName);
        $zip = new ZipArchive();
        $zip->open($path.$fileName.'_temp.zip');
        $zip->extractTo($path);
        $zip->close();
        $values['themeColors'] = (array) $values['themeColors'];
        $Urls = array();
        $filesDir = OW::getPluginManager()->getPlugin('frmthememanager')->getUserFilesDir();
        foreach ($this->urlsList as $urlName){
            foreach ($extensions as $extension){
                $name = $values['parentTheme']."_".$values['themeKey']."_".$urlName['name'];
                $fileDir = $filesDir . $name .".". $extension;
                if (OW::getStorage()->fileExists($fileDir)) {
                    $Urls[$urlName['name']]= OW::getStorage()->getFileUrl( $fileDir);
                }
            }
        }
        $values['urls'] = $Urls;
        return $values;
    }

    public function afterThemeActionRedirect($destination){
        if(isset($destination) && $destination == 'appearance'){
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('admin_themes_choose'));
        }elseif (isset($destination) && $destination == 'plugin'){
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('frmthememanager_admin_setting'));
        }else{
            return true;
        }
    }

}