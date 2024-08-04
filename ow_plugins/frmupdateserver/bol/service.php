<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmupdateserver.bol
 * @since 1.0
 */
final class FRMUPDATESERVER_BOL_Service
{
    private $pluginInformationDao;
    private $categoryDao;

    private $essentialPlugins;
    private $ignorePluginsKeyList;
    private $ignoreThemesKeyList;

    private $whiteDirs, $whiteFiles, $ignoreDirs, $ignoreFiles;

    /**
     * @var FRMUPDATESERVER_BOL_UpdateInformationDao
     */
    private $updateInformationDao;

    /**
     * @var FRMUPDATESERVER_BOL_UsersInformationDao
     */
    private $usersInformationDao;

    /**
     * @var FRMUPDATESERVER_BOL_DownloadFileDao
     */
    private $downloadFileDao;

    /**
     * @var FRMUPDATESERVER_BOL_ItemDao
     */
    private $itemDao;

    public $SETTINGS_SECTION = 1;
    public $PLUGIN_ITEMS_SECTION = 2;
    public $THEME_ITEMS_SECTION = 3;
    public $ADD_ITEM_SECTION = 4;
    public $DELETE_ITEM_SECTION = 5;
    public $CHECK_ITEM_SECTION = 6;
    public $PLUGIN_CATEGORY = 7;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->updateInformationDao = FRMUPDATESERVER_BOL_UpdateInformationDao::getInstance();
        $this->usersInformationDao = FRMUPDATESERVER_BOL_UsersInformationDao::getInstance();
        $this->itemDao = FRMUPDATESERVER_BOL_ItemDao::getInstance();
        $this->downloadFileDao = FRMUPDATESERVER_BOL_DownloadFileDao::getInstance();
        $this->pluginInformationDao = FRMUPDATESERVER_BOL_PluginInformationDao::getInstance();
        $this->categoryDao = FRMUPDATESERVER_BOL_CategoryDao::getInstance();

        // faster build
        $this->essentialPlugins = BOL_PluginService::getInstance()->getByCategory('essential');
        $this->ignorePluginsKeyList = BOL_PluginService::getInstance()->getByCategory('private');
        sort($this->ignorePluginsKeyList);
        $this->ignoreThemesKeyList = BOL_PluginService::getInstance()->getByCategory('private');
        sort($this->ignoreThemesKeyList);

        // to build CORE releases
        $this->ignoreDirs = array(
            '.git' . DS,
            '.idea' . DS,
            'docker' . DS,
            'ow_smarty' . DS . 'template_c' . DS,
            'ow_log' . DS,
            'ow_unittest' . DS,
            'ow_frm'.DS.'test' . DS,
            'ow_userfiles' . DS,
            'ow_pluginfiles' . DS,
            'ow_themes' . DS,
            'ow_plugins' . DS,
            'ow_static' . DS . 'themes' . DS,
            'ow_static' . DS . 'plugins' . DS
        );
        $this->ignoreFiles = array(
            '.gitignore',
            'ow_includes' . DS . 'config.php',
            'composer.phar',
            'composer.lock',
            'saas_provider.php',
            'composer.json',
            'favicon.ico'
        );
        $this->whiteDirs = array(
            'ow_themes' . DS . 'frmsocialcity' . DS ,
            'ow_static' . DS . 'themes' . DS . 'frmsocialcity' . DS ,
            'ow_static' .DS. 'plugins' .DS. 'base' . DS ,
            'ow_static' .DS. 'plugins' .DS. 'admin' . DS
        );
        foreach($this->essentialPlugins as $essentialPlugin){
            $this->whiteDirs[] = 'ow_plugins' . DS . $essentialPlugin;
        }
        $this->whiteFiles = array(
            'ow_includes' . DS . 'config.php.default',
        );

    }

    /**
     * Singleton instance.
     *
     * @var FRMUPDATESERVER_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMUPDATESERVER_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $key
     * @param $buildNumber
     * @param $version
     */
    public function addVersion($key, $buildNumber, $version = null)
    {
        $updateInformation = new FRMUPDATESERVER_BOL_UpdateInformation();
        $updateInformation->key= $key;
        $updateInformation->buildNumber = $buildNumber;
        $updateInformation->time = time();
        $updateInformation->version = $version;
        $this->updateInformationDao->save($updateInformation);
    }

    /***
     * @param $name
     * @param $description
     * @param $key
     * @param $image
     * @param $type
     * @param $guidelineurl
     * @return FRMUPDATESERVER_BOL_Item|mixed|null
     */
    public function addItem($name, $description, $key, $image, $type, $guidelineurl)
    {
        if(!isset($key)){
            return null;
        }
/*        $allVersionsOfSelectedKey = $this->getAllVersion($key);
        if($allVersionsOfSelectedKey==null){
            return null;
        }*/

        $hasItem = $this->itemDao->getItemByKey($key);
        if($hasItem){
            $this->updateItem($name, $description, $key, $image, $type, $guidelineurl);
            return $hasItem;
        }
        $order = $this->getMaxOrderOfItem() + 1;
        $item = new FRMUPDATESERVER_BOL_Item();
        $item->name= $name;
        $item->description = $description;
        $item->key = $key;
        $item->image = $image;
        $item->type = $type;
        $item->order = $order;
        $item->guidelineurl = $guidelineurl;
        $this->itemDao->save($item);
        return $item;
    }

    /***
     * @param $item
     */
    public function saveItem($item){
        if($item!=null){
            $this->itemDao->save($item);
        }
    }

    /***
     * @return int|mixed
     */
    public function getMaxOrderOfItem(){
        return $this->itemDao->getMaxOrder();
    }

    public function saveFile($imagePostedName){
        if(!((int)$_FILES[$imagePostedName]['error'] !== 0 || !is_uploaded_file($_FILES[$imagePostedName]['tmp_name']))){
            $iconName = FRMSecurityProvider::generateUniqueId() . '.' . UTIL_File::getExtension($_FILES[$imagePostedName]['name']);
            $userfilesDir = OW::getPluginManager()->getPlugin('frmupdateserver')->getUserFilesDir();
            $tmpImgPath = $userfilesDir . $iconName;
            $storage = new BASE_CLASS_FileStorage();
            $storage->copyFile($_FILES[$imagePostedName]['tmp_name'], $tmpImgPath);
            return $iconName;
        }

        return null;
    }

    /***
     * @param $name
     * @param $description
     * @param $key
     * @param $image
     * @param $type
     * @param $guidelineurl
     * @return mixed|null
     */
    public function updateItem($name, $description, $key, $image, $type, $guidelineurl){
        if(!isset($key)){
            return null;
        }
        $newItem = $this->itemDao->getItemByKey($key);
        if($newItem!=null){
            $newItem->name= $name;
            $newItem->description = $description;
            $newItem->key = $key;
            $newItem->image = $image;
            $newItem->type = $type;
            $newItem->guidelineurl = $guidelineurl;
            $this->itemDao->save($newItem);
        }

        return $newItem;
    }

    /***
     * @param $key
     * @param $version
     */
    public function saveDownloadFileHistory($key, $version){
        $downloadLogItem = new FRMUPDATESERVER_BOL_DownloadFile();
        $downloadLogItem->key = $key;
        $downloadLogItem->version = $version;
        $downloadLogItem->time = time();
        $downloadLogItem->ip = $this->getCurrentIP();
        $this->downloadFileDao->save($downloadLogItem);
    }

    /***
     * @param null $type
     * @return array
     */
    public function getItems($type = null){
        return $this->itemDao->getItems($type);
    }

    /***
     * @param $id
     * @return mixed|void
     */
    public function deleteItem($id){
        if(!isset($id)){
            return;
        }

        $item = $this->itemDao->getItemById($id);
        $this->itemDao->deleteById($id);

        return $item;
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getItemById($id){
        return $this->itemDao->getItemById($id);
    }

    /***
     * @param $key
     * @return mixed
     */
    public function getItemByKey($key){
        return $this->itemDao->getItemByKey($key);
    }

    public function getItemByKeyAndBuildNumber($key,$buildNumber){
        return $this->updateInformationDao->getItemByKeyAndBuildNumber($key,$buildNumber);
    }

    /***
     * @param $action
     * @param null $nameValue
     * @param null $descriptionValue
     * @param null $keyValue
     * @param null $typeValue
     * @param null $guidelineurl
     * @return Form
     */
    public function getItemForm($action, $nameValue = null, $descriptionValue = null, $keyValue=null, $typeValue=null, $guidelineurl=null, $category=null, $pluginImage=null){
        $form = new Form('item');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $name = new TextField('name');
        $name->setRequired();
        $name->setValue($nameValue);
        $name->setLabel(OW::getLanguage()->text('frmupdateserver', 'name'));
        $name->setHasInvitation(false);
        $form->addElement($name);

        $description = new Textarea('description');
        $description->setValue($descriptionValue);
        $description->setLabel(OW::getLanguage()->text('frmupdateserver', 'description'));
        $description->setHasInvitation(false);
        $form->addElement($description);

        $name = new TextField('key');
        $name->setRequired();
        $name->setValue($keyValue);
        $name->setLabel(OW::getLanguage()->text('frmupdateserver', 'key'));
        $name->setHasInvitation(false);
        $form->addElement($name);

        $image = new FileField('image');
        $image->setLabel(OW::getLanguage()->text('frmupdateserver', 'image'));
        $image->setValue($pluginImage);
        if(!isset($pluginImage)){
            $image->setRequired();
        }
        $form->addElement($image);

        $typeField = new Selectbox('type');
        $typeField->setLabel(OW::getLanguage()->text('frmupdateserver', 'type'));
        $typeField->setHasInvitation(false);
        $options = array();
        $options['plugin'] = OW::getLanguage()->text('frmupdateserver', 'plugin');
        $options['theme'] = OW::getLanguage()->text('frmupdateserver', 'theme');
        $typeField->setOptions($options);
        $typeField->setRequired();
        $typeField->setValue($typeValue);
        $form->addElement($typeField);

        $categoryField = new CheckboxGroup('categoryFieldCheck');
        $categories=FRMUPDATESERVER_BOL_CategoryDao::getInstance()->findAll();
        foreach ($categories as $categoryItem ){
            $categoryField->addOption($categoryItem->id,$categoryItem->label);
        }
        $categoryField->setValue($category);
        $categoryField->setLabel(OW::getLanguage()->text('frmupdateserver', 'category_label'));
        $form->addElement($categoryField);

        $guidelineurlField = new TextField('guidelineurl');
        $guidelineurlField->setValue($guidelineurl);
        $guidelineurlField->setLabel(OW::getLanguage()->text('frmupdateserver', 'guidelineurl_label'));
        $guidelineurlField->setHasInvitation(false);
        $form->addElement($guidelineurlField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param null $keyValue
     * @param null $buildNum
     * @return Form
     */
    public function getDeleteItemForm($keyValue=null,$buildNum=null){
        $form = new Form('deleteItem');
        $form->setMethod(Form::METHOD_POST);
        $form->setAjaxResetOnSuccess(true);
        $key = new TextField('key');
        $key->setRequired();
        $key->setValue($keyValue);
        $key->setLabel(OW::getLanguage()->text('frmupdateserver', 'key'));
        $key->setHasInvitation(false);
        $form->addElement($key);

        $build = new Textarea('build');
        $build->setRequired();
        $build->setValue($buildNum);
        $build->setLabel(OW::getLanguage()->text('frmupdateserver', 'buildNumber'));
        $build->setHasInvitation(false);
        $form->addElement($build);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param null $keyValue
     * @return Form
     */
    public function getCheckItemForm($keyValue=null){
        $form = new Form('checkItem');
        $form->setMethod(Form::METHOD_POST);
        $form->setAjaxResetOnSuccess(true);
        $key = new TextField('key');
        $key->setRequired();
        $key->setValue($keyValue);
        $key->setLabel(OW::getLanguage()->text('frmupdateserver', 'key'));
        $key->setHasInvitation(false);
        $form->addElement($key);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    public function deleteAllVersions()
    {
        $this->updateInformationDao->deleteAllVersions();
    }

    public function deleteItemByIDAndBuildNumAndKey($item,$buildNumber,$key)
    {
        return $this->updateInformationDao->deleteItem($item,$buildNumber,$key);
    }

    /***
     * @param $key
     * @param $developerKey
     */
    public function addUser($key, $developerKey)
    {
        $usersInformation = new FRMUPDATESERVER_BOL_UsersInformation();
        $usersInformation->key= $key;
        $usersInformation->developerKey = $developerKey;
        $usersInformation->time = time();
        $usersInformation->ip = $this->getCurrentIP();
        $this->usersInformationDao->save($usersInformation);
    }

    public function getCurrentIP(){
        $ip = OW::getRequest()->getRemoteAddress();
        if($ip == '::1'){
            $ip = '127.0.0.1';
        }
        return $ip;
    }

    /***
     * @param $key
     * @param $buildNumber
     * @return FRMUPDATESERVER_BOL_UpdateInformation
     */
    public function hasExist( $key, $buildNumber)
    {
        return $this->updateInformationDao->hasExist($key, $buildNumber);
    }

    /***
     * @param null $key
     * @return array
     */
    public function getAllVersion($key = null){
        return $this->updateInformationDao->getAllVersion($key);
    }

    /***
     * @param null $key
     * @return FRMUPDATESERVER_BOL_UpdateInformation
     */
    public function getLatestVersion($key = null){
        $allVersions = $this->updateInformationDao->getAllVersion($key);
        if(sizeof($allVersions)>0){
            return $allVersions[0];
        }
        return null;
    }

    /**
     * @param $sectionId
     * @return array
     */
    public function getAdminSections($sectionId)
    {
        $sections = array();

        $sections[] = array(
            'sectionId' => $this->getInstance()->SETTINGS_SECTION,
            'active' => $sectionId == $this->getInstance()->SETTINGS_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin'),
            'label' => OW::getLanguage()->text('frmupdateserver', 'settings')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->PLUGIN_ITEMS_SECTION,
            'active' => $sectionId == $this->getInstance()->PLUGIN_ITEMS_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => 'plugin')),
            'label' => OW::getLanguage()->text('frmupdateserver', 'plugins')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->THEME_ITEMS_SECTION,
            'active' => $sectionId == $this->getInstance()->THEME_ITEMS_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.items', array('type' => 'theme')),
            'label' => OW::getLanguage()->text('frmupdateserver', 'themes')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->ADD_ITEM_SECTION,
            'active' => $sectionId == $this->getInstance()->ADD_ITEM_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.add.item'),
            'label' => OW::getLanguage()->text('frmupdateserver', 'add_item')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->DELETE_ITEM_SECTION,
            'active' => $sectionId == $this->getInstance()->DELETE_ITEM_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.delete.by.name.and.version'),
            'label' => OW::getLanguage()->text('frmupdateserver', 'delete_item')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->CHECK_ITEM_SECTION,
            'active' => $sectionId == $this->getInstance()->CHECK_ITEM_SECTION ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.check.update.by.name'),
            'label' => OW::getLanguage()->text('frmupdateserver', 'check_item')
        );

        $sections[] = array(
            'sectionId' => $this->getInstance()->PLUGIN_CATEGORY,
            'active' => $sectionId == $this->getInstance()->PLUGIN_CATEGORY ? true : false,
            'url' => OW::getRouter()->urlForRoute('frmupdateserver.admin.categories'),
            'label' => OW::getLanguage()->text('frmupdateserver', 'categories')
        );

        return $sections;
    }


    public function getZipPathByKey($buildNumber, $type){
        if($type==null || $buildNumber == null){
            return null;
        }
        return OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir() . $type . DS . $buildNumber;
    }

    /***
     * @param $name
     * @return string
     */
    public function getReplacedItemName($name){
        return 'ms-'.$name;
    }

    public function isInPathList($whitePaths, $relativePath, $isFileList){
        if($isFileList) {
            foreach ($whitePaths as $whiteDir) {
                if (strpos($relativePath, $whiteDir) > -1) {
                    return true;
                }
            }
        }else{
            foreach ($whitePaths as $whiteDir) {
                if (strpos($relativePath, $whiteDir) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $relativePath
     * @param bool $forUpdate
     * @return bool
     */
    public function isInIgnoreDirectoryList($relativePath, $forUpdate = true){
        $ignoreDirs = $this->ignoreDirs;
        $ignoreFiles = $this->ignoreFiles;
        $whiteDirs = $this->whiteDirs;
        $whiteFiles = $this->whiteFiles;
        if($forUpdate){
            $ignoreDirs[] = 'ow_install' . DS;
        }else{
            $whiteDirs = array_merge($whiteDirs, [
                'ow_pluginfiles' . DS . 'admin' . DS ,
                'ow_pluginfiles' . DS . 'ow' . DS ,
                'ow_pluginfiles' . DS . 'plugin' . DS ,
                'ow_pluginfiles' . DS . 'plugins' . DS
            ]);
            $whiteFiles = array_merge($whiteFiles, [
                'ow_pluginfiles' . DS . 'base' . DS . 'avatars' . DS . 'index.html'
            ]);
        }

        if($this->isInPathList($whiteFiles, $relativePath, true)){
            return false;
        }
        if($this->isInPathList($ignoreFiles, $relativePath, true)){
            return true;
        }
        if($this->isInPathList($whiteDirs, $relativePath, false)){
            return false;
        }
        if($this->isInPathList($ignoreDirs, $relativePath, false)){
            return true;
        }

        return false;
    }


    public function addStaticFile($dir, $toDir, $type, $name){
        $mdFiles = UTIL_File::findFiles($dir, array($type), 1);
        foreach ( $mdFiles as $mdFile )
        {
            if ( basename($mdFile) === $name.$type )
            {
                OW::getStorage()->copyFile($mdFile, $toDir . DS . $name.$type);
            }
        }
    }

    public function isConfigFileNeededtoInstall($filePath,$relativePath,$forUpdate)
    {
        if($forUpdate==null && (strcmp('ow_includes' . DS . 'config.php.default',$relativePath)==0))
        {
            return true;
        }
        return false;
    }

    public function getPluginDir(){
        return OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir();
    }

    public function downloadFile($key, $version){
        $result = array();
        FRMUPDATESERVER_BOL_Service::getInstance()->saveDownloadFileHistory($key, $version);
        $result['returnIconUrl'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticUrl(). 'images/return.png';
        $result['returnLabel'] = OW::getLanguage()->text('frmupdateserver','return');
        return $result;
    }

    public function getPublicFilesOfSource($path = null){
        $path = str_replace('.', '', $path);
        $selectedDir = self::getPluginDir();
        $selectedUrl = self::getPathOfFTP();
        $iconUrl = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticUrl() . 'images/';
        if($path!=null){
            $pathDir = str_replace('/', DS, $path);
            $selectedDir = $selectedDir . $pathDir . DS;
            $selectedUrl = $selectedUrl . $path . '/';
        }

        $files = scandir($selectedDir);
        $filesInformation = array();
        $dirsInformation = array();
        $values = explode('/', $path);
        foreach($files as $file){
            if($file=='.' || $file=='..'){
                continue;
            }
            if(OW::getStorage()->isDir($selectedDir . $file)){
                $dirInfo = array();
                $dirInfo['name'] = $file;
                $dirInfo['icon'] = $iconUrl . 'archive.png';
                $dirInfo['path'] = $path . '/' . $file;
                $dirsInformation[] = $dirInfo;
            }else{
                $fileInfo = array();
                $fileInfo['key'] = $file;
                $fileInfo['version'] = $file;
                $values = explode('/', $path);
                if(sizeof($values)>1){
                    $fileInfo['version'] = $values[sizeof($values)-1];
                }
                $fileInfo['name'] = $file;

                $fileInfo['href'] =  $selectedUrl . $file;
                $fileInfo['icon'] = $iconUrl . $this->getFileIconLabel($file);
                $filesInformation[] = $fileInfo;
            }
        }
        $result = array();
        $result['files'] = $filesInformation;
        $result['dirs'] = $dirsInformation;
        $returnUrl = dirname($path);
        $result['returnUrl'] = "";
        if(sizeof($values)>1){
            $result['headerLabel'] = $values[sizeof($values)-1];
        }else{
            $result['headerLabel'] = OW::getLanguage()->text('frmupdateserver','files');
        }
        $result['version'] = false;
        $returnable = false;
        if($path!=null && $returnUrl!='.' && $returnUrl!='..'){
            $returnable = true;
            $result['returnUrl'] = $returnUrl;
        }

        $result['returnable'] = $returnable;
        $result['urlOfDownload'] = OW::getRouter()->urlForRoute('frmupdateserver.download-file');
        $result['returnIconUrl'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticUrl(). 'images/return.png';
        $result['returnLabel'] = OW::getLanguage()->text('frmupdateserver','return');
        return $result;
    }

    public function getFileIconLabel($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if($ext=='excel' || $ext=='csv'){
            return 'csv.png';
        }else if($ext=='zip'){
            return 'zip.png';
        }else if($ext=='txt'){
            return 'txt.png';
        }else if($ext=='pdf'){
            return 'pdf.png';
        }else if($ext=='sha256'){
            return 'sha256.png';
        }

        return 'file.png';
    }

    public function getFilesOfSource($type = null, $key = null, $version = null){

        $selectedDir = self::getPluginDir();
        $selectedUrl = self::getPathOfFTP();
        $iconUrl = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticUrl() . 'images/';
        if($type!=null){
            $selectedDir = $selectedDir . $type . DS;
            $selectedUrl = $selectedUrl . $type . '/';
        }
        if($key!=null){
            $selectedDir = $selectedDir . $key . DS;
            $selectedUrl = $selectedUrl . $key . '/';
        }
        if($version!=null){
            $selectedDir = $selectedDir . $version . DS;
            $selectedUrl = $selectedUrl . $version . '/';
        }

        $files = scandir($selectedDir);
        $filesInformation = array();
        $dirsInformation = array();
        foreach($files as $file){
            if($file=='.' || $file=='..'){
                continue;
            }
            if(OW::getStorage()->isDir($selectedDir . $file)){
                $dirInfo = array();
                $dirInfo['name'] = $file;
                if(is_numeric($file)){
                    if($type=="plugins" || $type=="themes"){
                        $updateDao= $this->updateInformationDao->getItemByKeyAndBuildNumber($key,(int)$file);
                    }else if($type=="core"){
                        $updateDao= $this->updateInformationDao->getItemByKeyAndBuildNumber($type,(int)$file);
                    }
                    $dirInfo['version'] = $file;
                    $dirInfo['time'] = ' ('. OW::getLanguage()->text('frmupdateserver', 'date_released') . ' ' . UTIL_DateTime::formatSimpleDate(isset($updateDao->time)? $updateDao->time : filemtime($selectedDir . $file)). ')';
                }else{
                    $dirInfo['version'] = "";
                    $dirInfo['time'] = "";
                }
                $dirInfo['key'] = $key;
                if($key== null && $type!= null){
                    $dirInfo['key'] = $file;
                }
                $dirInfo['type'] = $type;
                if($type== null){
                    $dirInfo['type'] = $file;
                }
                $dirInfo['icon'] = $iconUrl . 'archive.png';
                $dirsInformation[] = $dirInfo;
            }else{
                $fileInfo = array();
                $fileInfo['name'] = $file;
                $fileInfo['key'] = $key;
                $fileInfo['version'] = $version;
                $fileInfo['href'] =  $selectedUrl . $file;
                $fileInfo['icon'] = $iconUrl . $this->getFileIconLabel($file);
                $filesInformation[] = $fileInfo;
            }
        }
        $result = array();
        $result['files'] = $filesInformation;
        $result['dirs'] = $dirsInformation;
        $result['returnUrl'] = '';//dirname();

        $result['version'] = false;
        $returnable = true;
        if($version!=null){
            $result['version'] = true;
        }else if($type!='core' || ($type=='core' && $key ==null)){
            $returnable = false;
        }

        $result['key'] = false;
        if($key!=null){
            $result['key'] = true;
        }

        $result['type'] = false;
        if($type!=null){
            $result['type'] = true;
        }

        $headerLabel = '';
        if($type == 'plugins' && $key==null){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','plugins_sample');
        }else if($type == 'plugins' && $key!=null){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','plugin') . ' ' . $key;
        }else if($type == 'themes' && $key==null){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','themes_sample');
        }else if($type == 'themes' && $key!=null){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','theme') . ' ' . $key;
        }else if($type == 'core' && $key==null){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','core');
        }else if($type == 'core' && $key=='main'){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','core');
        }else if($type == 'core' && $key=='updates'){
            $headerLabel = OW::getLanguage()->text('frmupdateserver','updater');
        }else{
            $headerLabel = OW::getLanguage()->text('frmupdateserver','view_versions');
        }

        $result['headerLabel'] = $headerLabel;
        $result['returnable'] = $returnable;
        $result['urlOfDownload'] = OW::getRouter()->urlForRoute('frmupdateserver.download-file');
        $result['returnIconUrl'] = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticUrl(). 'images/return.png';
        $result['returnLabel'] = OW::getLanguage()->text('frmupdateserver','return');
        return $result;
    }


    public function addSha256Hashfile($toDir,$filename){
        $hashCode = hash_file("sha256",$toDir . DS . $filename);
        $hashCodeFile = fopen($toDir . DS . $filename .'.sha256', "w");
        $txt = $hashCode;
        fwrite($hashCodeFile, $txt);
        fclose($hashCodeFile);
    }

    private function addFileToZipArchive($zip, $filePath, $relativePath)
    {
        $stat = stat($filePath);
        $zip->addFile($filePath, $relativePath);
        $zip->setExternalAttributesName($relativePath, ZipArchive::OPSYS_UNIX, $stat['mode'] << 16);
    }

    public function zipFolder($dir, $zipPath, $forUpdate = true, $key = null, $isCore = true){
        // Get real path for our folder
        $rootPath = realpath($dir);

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $keyPath = '';
        if($key!=null && !$isCore){
            $zip->addEmptyDir($key);
            $keyPath = $key . DS;
        }

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        if($forUpdate==null){
            $zip->addEmptyDir('ow_pluginfiles');
        }
        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                if(!$this->isInIgnoreDirectoryList($relativePath, $forUpdate)) {
                    // Add current file to archive
                    if($this->isConfigFileNeededtoInstall($filePath,$relativePath,$forUpdate)){
                        $this->addFileToZipArchive($zip, $filePath, 'ow_includes' . DS . 'config.php');
                    }
                    $this->addFileToZipArchive($zip, $filePath, $keyPath . $relativePath);
                }
            }
        }
        if($forUpdate==null) {
            $zip->addEmptyDir('ow_userfiles');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'admin');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'base');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'base' . DS . 'attachments');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'base' . DS . 'attachments' . DS . 'temp');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'base' . DS . 'avatars');
            $zip->addEmptyDir('ow_userfiles' .DS .'plugins' .DS .'base' . DS . 'avatars' . DS . 'tmp');
            $zip->addEmptyDir('ow_log');
            $zip->addEmptyDir('ow_smarty' . DS . 'template_c');
        }
        if($isCore) {
            $zip->addEmptyDir('ow_userfiles' . DS . 'themes');
        }

        // Zip archive will be created only after closing object
        $zip->close();
    }

    public function checkPluginForUpdate($key, $buildNumber, $sourceDir, $rootZipDirectory){
        $add_version = false;
        $allVersion = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion($key);
        if(sizeof($allVersion)>0){
            $allVersion = $allVersion[0];
            if($allVersion->buildNumber < $buildNumber){
                $add_version = true;
            }
        }else{
            $add_version = true;
        }

        if($add_version){
            $pluginDir = 'plugins' . DS . $key;
            if (!OW::getStorage()->fileExists($rootZipDirectory . $pluginDir)) {
                OW::getStorage()->mkdir($rootZipDirectory . $pluginDir);
            }
            $pluginDir = 'plugins' . DS . $key . DS . $buildNumber;
            if (!OW::getStorage()->fileExists($rootZipDirectory . $pluginDir)) {
                OW::getStorage()->mkdir($rootZipDirectory . $pluginDir);
            }
            $zipPath = $this->getZipPathByKey($this->getReplacedItemName($key) . '-' . $buildNumber . '.zip', $pluginDir);
            $this->zipFolder($sourceDir, $zipPath, true, $key, false);
            $this->addStaticFile($sourceDir, $rootZipDirectory . $pluginDir, 'txt', 'CHANGELOG.');
            $this->addStaticFile($sourceDir, $rootZipDirectory . $pluginDir, 'md', 'CHANGELOG.');
            $this->addStaticFile($sourceDir, $rootZipDirectory . $pluginDir, 'md', 'README.');
            $fileName = $this->getReplacedItemName($key) . '-' . $buildNumber . '.zip';
            $this->addSha256Hashfile($rootZipDirectory . $pluginDir,$fileName);
            FRMUPDATESERVER_BOL_Service::getInstance()->addVersion($key, $buildNumber);

        }
    }

    public function checkThemeForUpdate($key, $buildNumber, $rootZipDirectory){
        $dir = OW_DIR_THEME  . $key;
        $add_version = false;
        $allVersion = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion($key);
        if(sizeof($allVersion)>0){
            $allVersion = $allVersion[0];
            if($allVersion->buildNumber < $buildNumber){
                $add_version = true;
            }
        }else{
            $add_version = true;
        }

        if($add_version){
            $themeDir = 'themes' . DS . $key;
            if (!OW::getStorage()->fileExists($rootZipDirectory . $themeDir)) {
                OW::getStorage()->mkdir($rootZipDirectory . $themeDir);
            }
            $themeDir = 'themes' . DS . $key . DS . $buildNumber;
            if (!OW::getStorage()->fileExists($rootZipDirectory . $themeDir)) {
                OW::getStorage()->mkdir($rootZipDirectory . $themeDir);
            }
            $zipPath = $this->getZipPathByKey($this->getReplacedItemName($key) . '-' . $buildNumber . '.zip', $themeDir);
            $this->zipFolder($dir, $zipPath, true, $key, false);
            $fileName = $this->getReplacedItemName($key) . '-' . $buildNumber . '.zip';
            $this->addSha256Hashfile($rootZipDirectory . $themeDir,$fileName);
            FRMUPDATESERVER_BOL_Service::getInstance()->addVersion($key, $buildNumber);
        }
    }

    public function checkShubMobileVersion(){
        $this->manageMobileCodes('shub-mobile');
    }

    public function checkMobileDevelopmentSourceCodes(){
        $this->manageMobileCodes('mobile-development');
    }

    public function manageMobileCodes($mobileDevelopmentFolderName){
        $rootZipDirectory = OW::getPluginManager()->getPlugin('frmupdateserver')->getPluginFilesDir();
        $mobileDevelopmentPath = $rootZipDirectory . $mobileDevelopmentFolderName . DS;
        if (!OW::getStorage()->fileExists($mobileDevelopmentPath)) {
            OW::getStorage()->mkdir($mobileDevelopmentPath);
        }

        $androidSourceCodePluginFilePath = $mobileDevelopmentPath . 'android';
        $iosSourceCodePluginFilePath = $mobileDevelopmentPath . 'ios';

        if (!OW::getStorage()->fileExists($androidSourceCodePluginFilePath)) {
            OW::getStorage()->mkdir($androidSourceCodePluginFilePath);
        }

        if (!OW::getStorage()->fileExists($iosSourceCodePluginFilePath)) {
            OW::getStorage()->mkdir($iosSourceCodePluginFilePath);
        }

        $androidSourceCodeStaticPath = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticDir() . $mobileDevelopmentFolderName . DS . 'android';
        $files = scandir($androidSourceCodeStaticPath);
        foreach($files as $file) {
            if($file == '.' || $file == '..'){
                continue;
            }
            if (!OW::getStorage()->fileExists($androidSourceCodePluginFilePath . DS . $file)) {
                OW::getStorage()->copyFile($androidSourceCodeStaticPath . DS . $file, $androidSourceCodePluginFilePath . DS . $file);
            }
        }

        $iosSourceCodeStaticPath = OW::getPluginManager()->getPlugin('frmupdateserver')->getStaticDir() . $mobileDevelopmentFolderName . DS . 'ios';
        $files = scandir($iosSourceCodeStaticPath);
        foreach($files as $file) {
            if($file == '.' || $file == '..'){
                continue;
            }
            if (!OW::getStorage()->fileExists($iosSourceCodePluginFilePath . DS . $file)) {
                OW::getStorage()->copyFile($iosSourceCodeStaticPath . DS . $file, $iosSourceCodePluginFilePath . DS . $file);
            }
        }
    }

    public function checkCoreForUpdate($rootZipDirectory, $forUpdate = null, $addVersionManually = null){
        $dir = OW_DIR_ROOT;
        if (!OW::getStorage()->fileExists($rootZipDirectory . 'core')) {
            OW::getStorage()->mkdir($rootZipDirectory . 'core');
        }
        $add_version = false;
        $core_information = (array) (simplexml_load_file(OW_DIR_ROOT . 'ow_version.xml'));
        $allVersion = FRMUPDATESERVER_BOL_Service::getInstance()->getAllVersion('core');
        if(sizeof($allVersion)>0){
            $allVersion = $allVersion[0];
            if($allVersion->buildNumber < (string) $core_information['build']){
                $add_version = true;
            }
        }else{
            $add_version = true;
        }

        if($addVersionManually!=null && $addVersionManually){
            $add_version = true;
        }

        if($add_version){
            $coreDir = 'core' . DS . 'main' . DS . (string) $core_information['build'];
            if (!OW::getStorage()->fileExists($rootZipDirectory . 'core' . DS . 'main')) {
                OW::getStorage()->mkdir($rootZipDirectory . 'core' . DS . 'main');
            }
            if (!OW::getStorage()->fileExists($rootZipDirectory . 'core' . DS . 'main' . DS . (string) $core_information['build'])) {
                OW::getStorage()->mkdir($rootZipDirectory . 'core' . DS . 'main' . DS . (string) $core_information['build']);
            }
            if($forUpdate!=null && $forUpdate){
                $coreDir = 'core' . DS . 'updates' . DS . (string) $core_information['build'];
                if (!OW::getStorage()->fileExists($rootZipDirectory . 'core' . DS . 'updates')) {
                    OW::getStorage()->mkdir($rootZipDirectory . 'core' . DS . 'updates');
                }
                if (!OW::getStorage()->fileExists($rootZipDirectory . 'core' . DS . 'updates' . DS . (string) $core_information['build'])) {
                    OW::getStorage()->mkdir($rootZipDirectory . 'core' . DS . 'updates' . DS . (string) $core_information['build']);
                }
            }
            $zipPath = $this->getZipPathByKey( $this->getReplacedItemName('core') . '-' . (string) $core_information['build'] . '.zip', $coreDir);
            $this->zipFolder($dir, $zipPath, $forUpdate);
            if(OW::getStorage()->fileExists($zipPath)){
                $this->addStaticFile($dir, $rootZipDirectory . $coreDir, 'pdf', 'ReadMe.');
                $fileName = $this->getReplacedItemName('core') . '-' . (string) $core_information['build'] . '.zip';
                $this->addSha256Hashfile($rootZipDirectory . $coreDir,$fileName);
                if($forUpdate==null) {
                    FRMUPDATESERVER_BOL_Service::getInstance()->addVersion('core', (string)$core_information['build'], (string)$core_information['version']);
                }
            }else{
                //error
            }

            if($forUpdate==null){
                $this->checkCoreForUpdate($rootZipDirectory, true, true);
            }
        }
    }

    /***
     * @param $key
     * @param $allVersions
     * @param $path
     * @return string
     */
    public function getUrlOfLastVersionsOfItem($key, $allVersions, $path){
        $versionsOfSelectedKey = array();
        foreach($allVersions as $allVersion){
            if($allVersion->key == $key){
                $versionsOfSelectedKey[] = $allVersion;
            }
        }

        if(sizeof($versionsOfSelectedKey)>0){
            return $this->getPathOfFTP() . $path . '/' . $versionsOfSelectedKey[0]->buildNumber . '/' . FRMUPDATESERVER_BOL_Service::getInstance()->getReplacedItemName($key) . '-' . $versionsOfSelectedKey[0]->buildNumber . '.zip';
        }

        return $this->getPathOfFTP(). $path . '/';
    }

    /***
     * @param $key
     * @param $allVersions
     * @param $path
     * @return string
     */
    public function getJavascriptOfLastVersionsOfItem($key, $allVersions, $path){
        $versionsOfSelectedKey = array();
        foreach($allVersions as $allVersion){
            if($allVersion->key == $key){
                $versionsOfSelectedKey[] = $allVersion;
            }
        }

        $targetPath = $path;
        if(sizeof($versionsOfSelectedKey)>0){
            $targetPath =  $path . '/' . $versionsOfSelectedKey[0]->buildNumber;
        }

        return "openFileManagerFloatBox('".$targetPath."', '', '')";
    }

    /***
     * @return string
     */
    public function getPathOfFTP(){
        $path = OW::getConfig()->getValue('frmupdateserver', 'prefix_download_path');
        if(!isset($path) || $path == ''){
            $path = OW_URL_HOME;
        }else{
            $path = $path . '/';
        }

        return $path;
    }

    /***
     * @return array
     */
    public function getIgnorePluginsKeyList(){
        return $this->ignorePluginsKeyList;
    }

    /***
     * @return array
     */
    public function getIgnoreThemesKeyList(){
        return $this->ignoreThemesKeyList;
    }


    /***
     * @return array
     */
    public function getPluginCategoryList()
    {
        return $this->categoryDao->findAll();
    }

    public function getCategoryById($id)
    {
        return $this->categoryDao->findById($id);
    }

    public function getCategoryItemForm($id)
    {
        $item = $this->getCategoryById($id);
        $formName = 'edit-item';
        $submitLabel = 'edit';
        $actionRoute = OW::getRouter()->urlFor('FRMUPDATESERVER_CTRL_Admin', 'editCategoryItem');

        $form = new Form($formName);
        $form->setAction($actionRoute);

        if ($item != null) {
            $idField = new HiddenField('id');
            $idField->setValue($item->id);
            $form->addElement($idField);
        }

        $this->addCategoryField( $form, $item->label);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('frmupdateserver', 'edit_item'));
        $form->addElement($submit);

        return $form;
    }

    /**
     * @return int
     */
    public function addCategoryField($form,$value=null)
    {
        $fieldLabel = new TextField('label');
        $fieldLabel->setRequired();
        $fieldLabel->setInvitation(OW::getLanguage()->text('frmupdateserver', 'label_category_label'));
        $fieldLabel->setValue($value);
        $fieldLabel->setHasInvitation(true);
        $validator = new FRMUPDATESERVER_CLASS_LabelValidator();
        $language = OW::getLanguage();
        $validator->setErrorMessage($language->text('frmupdateserver', 'label_error_already_exist'));
        $fieldLabel->addValidator($validator);
        $form->addElement($fieldLabel);
        return $form;
    }

    public function addItemCategory($label)
    {
        $category = new FRMUPDATESERVER_BOL_Category();
        $category->label = $label;
        FRMUPDATESERVER_BOL_CategoryDao::getInstance()->save($category);
    }

    public function deleteItemCategory( $categoryId )
    {
        $categoryId = (int) $categoryId;
        if ( $categoryId > 0 )
        {
            $this->pluginInformationDao->deleteByCategoryId($categoryId);
            $this->categoryDao->deleteById($categoryId);
        }
    }

    public function editCategoryItem($id, $label)
    {
        $item = $this->getCategoryById($id);
        if ($item == null) {
            return;
        }
        if ($label == null) {
            $label = false;
        }
        $item->label = $label;

        $this->categoryDao->save($item);
        return $item;
    }

    public function isValid( $label )
    {
        if ( $label === null )
        {
            return false;
        }

        $alreadyExist = FRMUPDATESERVER_BOL_CategoryDao::getInstance()->findIsExistLabel($label);

        if ( !isset($alreadyExist) )
        {
            return true;
        }
        return false;
    }

}