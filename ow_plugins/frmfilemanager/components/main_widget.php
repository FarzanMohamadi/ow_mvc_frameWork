<?php
/**
 * frmfilemanager
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmfilemanager
 * @since 1.0
 */

class FRMFILEMANAGER_CMP_MainWidget extends BASE_CLASS_Widget
{
    private $pluginKey, $entityId;

    /***
     * FRMFILEMANAGER_CMP_MainWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        # init
        $entity = $params->additionalParamList['entity'];
        $hasWriteAccess = false;

        # special cases
        $startPath = '';
        $profileSave = '';
        if($entity == 'groups'){
            $this->pluginKey = 'group';
            $this->entityId = $params->additionalParamList['entityId'];
            if($params->additionalParamList['currentUserIsManager'] ||
                (!$params->additionalParamList['isChannel'] && $params->additionalParamList['currentUserIsMemberOfGroup'])){
                $hasWriteAccess = true;
            }
            $startPath = 'frm:groups/frm:groups:'.$this->entityId;
            $profileSave = (FRMFILEMANAGER_BOL_Service::getInstance()->hasProfileAccess())?", 'profileSave'":'';
        }
        elseif($entity == 'user'){
            $this->pluginKey = 'base';
            $this->entityId = $params->additionalParamList['entityId'];
            if( ! FRMFILEMANAGER_BOL_Service::getInstance()->hasProfileAccess($this->entityId)) {
                $this->setVisible(false);
                return;
            }
            $hasWriteAccess = true;
            $startPath = 'frm:profile/frm:profile:'.$this->entityId;
        }
        else{
            $this->setVisible(false);
            return;
        }
        $row = FRMFILEMANAGER_BOL_Service::getInstance()->getByPath($startPath);
        if (empty($row)){
            $this->setVisible(false);
            return;
        }

        // disable up button at root
        $rootPath = FRMFILEMANAGER_BOL_Service::getInstance()->getHashById($row->id);
        OW::getDocument()->addScriptDeclarationBeforeIncludes("var frmfilemanager_start_root='{$rootPath}'");

        // add static files
        $base_css_url = OW::getPluginManager()->getPlugin('frmfilemanager')->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($base_css_url . 'jquery-ui.css');
        OW::getDocument()->addStyleSheet($base_css_url . 'elfinder.min.css');

        // theme
//        OW::getDocument()->addStyleSheet($base_css_url . 'theme.css');
        OW::getDocument()->addStyleSheet($base_css_url . 'themes/windows-10/css/theme.css');

        $base_js_url = OW::getPluginManager()->getPlugin('frmfilemanager')->getStaticJsUrl();
//        OW::getDocument()->addScript($base_js_url . 'jquery.3.4.1.min.js');
        OW::getDocument()->addScript($base_js_url . 'jquery-ui.1.12.1.min.js');
        OW::getDocument()->addScript($base_js_url . 'elfinder.full.js');
        OW::getDocument()->addScript($base_js_url . 'extras/editors.default.min.js');
        if(!empty($profileSave)){
            $js = 'elFinder.prototype.commands.profileSave= function() {
                this.exec = function(select) {
                    console.log(select);
                    var hashes  = this.hashes(select);
                    $.ajax({
                        url: "' . OW::getRouter()->urlForRoute('frmfilemanager.saveToProfile') . '",
                        type: "post",
                        dataType : "json",
                        data: {"hashes":hashes},
                        success: function(result){
                            OW.info(result.message);
                        }
                    });
                    return $.Deferred();
                }
                this.getstate = function(select) {
                    // return 0 to enable, -1 to disable icon access
                    var sel    = this.hashes(select),
                        cnt    = sel.length,
                        files   = this.files(select);
                    
                    if (cnt < 1) {
                        return -1;
                    }
                    
                    // return if directory
                    for(i=0;i<files["length"];i++){
                        file = files[i]
                        if ( file["mime"] == "directory" ){
                            return -1;
                        }
                    }
                    
                    return 0;
                }
            }';
            OW::getDocument()->addOnloadScript($js);
        }


        /**
         * fileManager options
         * https://github.com/Studio-42/elFinder/wiki/Client-configuration-options
         */
        $staticURL = OW::getPluginManager()->getPlugin('frmfilemanager')->getStaticUrl();
        $backendURL = OW::getRouter()->urlFor('FRMFILEMANAGER_CTRL_Backend', 'connector');
        $hash = FRMFILEMANAGER_BOL_Service::getInstance()->getHashById($row->id);

        $extraButtons = "['fullscreen']";
        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $extraButtons = '';
        }

        $options = "
            commands: [
                'open', 'reload', 'home', 'up', 'back', 'forward', 'getfile', 'quicklook', 
                'download', 'rm', 'duplicate', 'rename', 'mkdir', 'mkfile', 'upload', 'copy', 
                'cut', 'paste', 'edit', 'extract', 'archive', 'search', 'info', 'view', 'help', 'resize', 'sort', 'netmount',
                'fullscreen'{$profileSave}
            ],
            cssAutoLoad : false,               // Disable CSS auto loading
            baseUrl : '{$staticURL}',                    // Base URL to css/*, js/*
            url : '{$backendURL}',  // connector URL (REQUIRED)
            lang: 'fa',                  // language (OPTIONAL)
            rememberLastDir: false,
            useBrowserHistory: false,
            ui: ['toolbar'], //['toolbar', 'places', 'tree', 'path', 'stat']
            resizable: false,
            height: 310,
            startPathHash: '$hash',
            ";

        if($hasWriteAccess){
            $options.="
            uiOptions : {
                toolbar: [  // toolbar configuration
                    ['up'],
                    ['mkdir', 'upload', 'cut', 'paste'],
                    ['download', 'rm'{$profileSave}],
                    {$extraButtons}
                ],
            },
            contextmenu : {
                navbar : ['open', '|', 'copy', 'cut', 'paste', '|', 'rm'],
                cwd    : ['reload',  '|', 'upload', 'mkdir', 'paste'],
                files  : ['download'{$profileSave}, '|', 'copy', 'cut', 'paste', '|', 'rename', 'rm']
            },";
        }else{
            $options.="
            uiOptions : {
                toolbar: [  // toolbar configuration
                    ['up'],
                    ['download'{$profileSave}],
                    {$extraButtons}
                ],
            },
            contextmenu : {
                navbar : ['open'],
                cwd    : ['reload'],
                files  : ['download'{$profileSave}]
            },";
        }

        $this->assign('options', $options);
    }

    public function getAttachmentUrl($name, $params = array())
    {
        return OW::getStorage()->getFileUrl($this->getAttachmentDir($name), false, $params);
    }

    public function getAttachmentDir($name)
    {
        return OW::getPluginManager()->getPlugin($this->pluginKey)->getUserFilesDir() . 'attachments' . DS .$name ;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmfilemanager', 'widget_files_title'),
            self::SETTING_ICON => self::ICON_FILE
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}