<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvanceeditor.bol
 * @since 1.0
 */
class FRMADVANCEEDITOR_CLASS_EventHandler
{
    const GET_MAX_SYMBOLS_COUNT = "frmadvanceeditor.get.max.symbols.count";
    private static $classInstance;
    private $pluginKey='base';
    private static $stylesheet = "";
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        self::$stylesheet = "span.cke_button__ow_image_icon,
            span.cke_button__ow_video_icon,
            span.cke_button__ow_html_icon,
            span.cke_button__ow_switchHtml_icon,
            span.cke_button__ow_more_icon
            { background: url(". OW_URL_STATIC."plugins/base/css/wysiwyg.png ) no-repeat 0 0; }
            
            span.cke_button__ow_image_icon
            { background-position: -178px -2px;}
            span.cke_button__ow_video_icon
            { background-position: -200px -2px;}
            span.cke_button__ow_html_icon
            { background-position: -156px -2px;}
            span.cke_button__ow_switchHtml_icon
            { background-position: -134px -2px;}
            span.cke_button__ow_more_icon
            { background-position: -112px -2px;}
            
            
            iframe.cke_wysiwyg_frame.cke_reset {
                height: 100% !important;
                max-height: initial !important;
            }";
    }

    public function setPluginKey()
    {
        $attr = OW::getRequestHandler()->getHandlerAttributes();

        if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FRMNEWS') !== false)
        {
            $this->pluginKey='frmnews';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FRMVITRIN') !== false)
        {
            $this->pluginKey='frmvitrin';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FORUM') !== false)
        {
            $this->pluginKey='forum';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FRMCOMPETITION') !== false)
        {
            $this->pluginKey='frmcompetition';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FRMSLIDESHOW') !== false)
        {
            $this->pluginKey='frmslideshow';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'FRMTICKETING') !== false)
        {
            $this->pluginKey='frmticketing';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'BLOGS') !== false)
        {
            $this->pluginKey='blogs';
        }
        else if (strpos($attr[OW_RequestHandler::ATTRS_KEY_CTRL], 'BASE') !== false)
        {
            $this->pluginKey='base';
        }
    }

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_FINALIZE, array($this, 'onFinalize'));
        $eventManager->bind(OW_EventManager::ON_AFTER_ROUTE, array($this, 'onAfterRoute'));
        $eventManager->bind("frmadvanceeditor.get.max.symbols.count",array($this,'onGetMaxSymbolsCount'));
    }

    public function onFinalize(OW_Event $event)
    {
        $requri = OW::getRequest()->getRequestUri();
        $config = OW::getConfig();
        $this->setPluginKey();
        $htmlDisableStatus = false;
        $mediaDisableStatus = false;
        $ck_enabled = false;
        if(OW::getConfig()->getValue('frmadvanceeditor', 'isCustomHtmlWidgetEditorAdvance'))
        {
            $conditionForCustomHtmlWidget = strpos($requri, 'index/customize') !== false;
        }
        else{
            $conditionForCustomHtmlWidget = false;
        }
        if ((strpos($requri, 'edit') !== false && strpos($requri, 'admin') === false) ||
            (strpos($requri, 'add') !== false && strpos($requri, 'admin') === false) ||
            strpos($requri, 'create') !== false ||
            strpos($requri, 'new') !== false ||
            strpos($requri, 'admin/guideline') !== false ||
            strpos($requri, 'admin/questions') !== false ||
            strpos($requri, 'admin/edit-question') !== false ||
            strpos($requri, 'admin/mass-mailing') !== false ||
            strpos($requri, 'frmvitrin/admin') !== false ||
            strpos($requri, 'frmterms/admin') !== false ||
            strpos($requri, 'forum/topic/') !== false ||
            strpos($requri, 'frmcompetition/') !== false ||
            strpos($requri, 'frmslideshow/admin') !== false ||
            strpos($requri, 'frmticketing/ticket') !== false ||
            $conditionForCustomHtmlWidget
        ) {

            $ck_enabled = true;
        }
        if($config->configExists('base','tf_user_custom_html_disable'))
        {
            $htmlDisableStatus= $config->getValue('base','tf_user_custom_html_disable');
        }
        if($config->configExists('base','tf_user_rich_media_disable'))
        {
            $mediaDisableStatus= $config->getValue('base','tf_user_rich_media_disable');
        }
        if ($ck_enabled === true && !$htmlDisableStatus) {
            $mediaPlugins = '';

             if(!$mediaDisableStatus && !(strpos($requri, 'event') !== false
                     || strpos($requri, 'group') !== false
                     || strpos($requri, 'video') !== false)){
                $mediaPlugins = 'ow_video,ow_image';
            }
            $more = "";
            if(!(strpos($requri, 'event') !== false ||
                 strpos($requri, 'group') !== false ||
                 strpos($requri, 'video') !== false)){
                if($mediaPlugins!=''){
                    $more = ",";
                }
                $more .= "ow_more";
            }

            //choose CKEditor language based on user's specified language
            $lang = 'fa';
            $currentLanguageTag = BOL_LanguageService::getInstance()->getCurrent()->getTag();
            if($currentLanguageTag=='en'){
                $lang = 'en';
            }

            OW::getDocument()->addStyleDeclaration(self::$stylesheet);
            OW::getDocument()->addScript(OW_URL_STATIC_PLUGINS . 'frmadvanceeditor/js/ckeditor/ckeditor.js');
            OW::getDocument()->addScript(OW_URL_STATIC_PLUGINS . 'frmadvanceeditor/js/init.js');
            OW::getDocument()->addOnloadScript("
                window.CKCONFIG=
                {
                toolbar: 'Basic',
                customConfig : '',
                ow_imagesUrl : '" . OW::getRouter()->urlFor('BASE_CTRL_MediaPanel', 'index', array('pluginKey' =>   $this->pluginKey, 'id' => '__id__')) . "',
                language : '" . BOL_LanguageService::getInstance()->getCurrent()->getTag() . "',
                disableNativeSpellChecker : false,
                extraPlugins: '" . $mediaPlugins . $more .  "',
                removePlugins : 'image,contextmenu,liststyle,tabletools,tableselection',
                linkShowAdvancedTab : false,
                allowedContent:'h1 h2 h3 h4 h5 h6 ul ol blockquote div p li table tbody tr th td strong em i b u span; a[href,target]; img[src,height,width]; *[id,alt,title,dir]{*}(*); table[*]',
                autoGrow_onStartup: true,
                uiColor: '#fdfdfd',
                language: '$lang'
                };
                frmadvanceeditor_textarea_check();
            ", 900);
        }
    }

    public function onAfterRoute(OW_Event $event)
    {
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin('frmadvanceeditor')->getStaticJsUrl() . 'ckeditor/contents.css');
        OW::getDocument()->addStyleDeclaration(self::$stylesheet);
    }
    public function onGetMaxSymbolsCount(OW_Event $event){
        if(OW::getConfig()->configExists('frmadvanceeditor','MaxSymbolsCount'))
        {
            $msxSymbolsCount = OW::getConfig()->getValue('frmadvanceeditor','MaxSymbolsCount');
            $event->setData(array('maxSymbolsCount' => $msxSymbolsCount));
        }
    }
}