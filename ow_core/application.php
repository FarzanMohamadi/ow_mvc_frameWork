<?php
/**
 * @package ow_core
 * @since 1.0
 * @method static OW_Application getInstance()
 */
class OW_Application
{
    use OW_Singleton;
    
    const CONTEXT_MOBILE = BOL_UserService::USER_CONTEXT_MOBILE;
    const CONTEXT_DESKTOP = BOL_UserService::USER_CONTEXT_DESKTOP;
    const CONTEXT_NAME = 'owContext';

    /**
     * Current page document key.
     *
     * @var string
     */
    protected $documentKey;

    /**
     * @var string
     */
    protected $context;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->context = self::CONTEXT_DESKTOP;
    }

    /**
     * Sets site maintenance mode.
     *
     * @param boolean $mode
     */
    public function setMaintenanceMode( $mode )
    {
        OW::getConfig()->saveConfig('base', 'maintenance', (bool) $mode);
    }

    /**
     * @return string
     */
    public function getDocumentKey()
    {
        return $this->documentKey;
    }

    /**
     * @param string $key
     */
    public function setDocumentKey( $key )
    {
        $this->documentKey = $key;
    }

    /**
     * Application init actions.
     */
    public function init()
    {
        // router init - need to set current page uri and base url
        $router = OW::getRouter();
        $router->setBaseUrl(OW_URL_HOME);
        OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());
        $this->userAutoLogin();
        $this->detectLanguage();

        // setting default time zone
        date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));

        if ( OW::getUser()->isAuthenticated() )
        {
            $userId = OW::getUser()->getId();
            $timeZone = BOL_PreferenceService::getInstance()->getPreferenceValue('timeZoneSelect', $userId);

            if ( !empty($timeZone) )
            {
                date_default_timezone_set($timeZone);
            }
        }

        // synchronize the db's time zone
        OW::getDbo()->setTimezone();
        $this->initRequestHandler();
        $uri = OW::getRequest()->getRequestUri();

        // before setting in router need to remove get params
        if ( strstr($uri, '?') )
        {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        $router->setUri($uri);

        $router->setDefaultRoute(new OW_DefaultRoute());

        OW::getPluginManager()->initPlugins();

        $event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
        OW::getEventManager()->trigger($event);

        $navService = BOL_NavigationService::getInstance();

        // try to find static document with current uri
        $document = $navService->findStaticDocument($uri);

        if ( $document !== null )
        {
            $this->documentKey = $document->getKey();
        }

        $beckend = OW::getEventManager()->call('base.cache_backend_init');

        if ( $beckend !== null )
        {
            OW::getCacheManager()->setCacheBackend($beckend);
            OW::getCacheManager()->setLifetime(3600);
            OW::getDbo()->setUseCashe(true);
        }

        OW_DeveloperTools::getInstance()->init();

        OW::getThemeManager()->initDefaultTheme($this->isMobile());

        // setting current theme
        $activeThemeName = OW::getEventManager()->call('base.get_active_theme_name');
        $activeThemeName = $activeThemeName ? $activeThemeName : OW::getConfig()->getValue('base', 'selectedTheme');

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

        // adding static document routes
        $staticDocs = $this->findAllStaticDocs();
        $staticPageDispatchAttrs = OW::getRequestHandler()->getStaticPageAttributes();

        /* @var $value BOL_Document */
        foreach ( $staticDocs as $value )
        {
            OW::getRouter()->addRoute(new OW_Route($value->getKey(), $value->getUri(),
                $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'],
                array('documentKey' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => $value->getKey()))));

            // TODO refactor - hotfix for TOS page
            if ( in_array(UTIL_String::removeFirstAndLastSlashes($value->getUri()),
                    array("terms-of-use", "privacy", "privacy-policy")) )
            {
                OW::getRequestHandler()->addCatchAllRequestsExclude('base.members_only',
                    $staticPageDispatchAttrs['controller'], $staticPageDispatchAttrs['action'],
                    array('documentKey' => $value->getKey()));
            }
        }

        //adding index page route
        $availableFor = OW::getUser()->isAuthenticated() ? BOL_NavigationService::VISIBLE_FOR_MEMBER : BOL_NavigationService::VISIBLE_FOR_GUEST;
        $item = $this->findFirstMenuItem($availableFor);

        if ( $item !== null )
        {
            if ( $item->getRoutePath() )
            {
                $route = OW::getRouter()->getRoute($item->getRoutePath());
                $ddispatchAttrs = $route->getDispatchAttrs();
            }
            else
            {
                $ddispatchAttrs = OW::getRequestHandler()->getStaticPageAttributes();
            }

            $router->addRoute(new OW_Route('base_default_index', '/', $ddispatchAttrs['controller'],
                $ddispatchAttrs['action'],
                array('documentKey' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => $item->getDocumentKey()))));
            $this->indexMenuItem = $item;
            OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'activateMenuItem'));
        }
        else
        {
            $router->addRoute(new OW_Route('base_default_index', '/', 'BASE_CTRL_ComponentPanel', 'index'));
        }

        $isWebservice = false;
        $mobileSupportEvent = OW::getEventManager()->trigger(new OW_Event('check.url.webservice', array()));
        if (isset($mobileSupportEvent->getData()['isWebService']) && $mobileSupportEvent->getData()['isWebService']) {
            $isWebservice = true;
        }

        if ( !OW::getRequest()->isAjax() && !$isWebservice )
        {
            OW::getResponse()->setDocument($this->newDocument());
            OW::getDocument()->setMasterPage($this->getMasterPage());
            OW::getResponse()->setHeader(OW_Response::HD_CNT_TYPE,
                OW::getDocument()->getMime() . '; charset=' . OW::getDocument()->getCharset());
        }
        else
        {
            OW::getResponse()->setDocument(new OW_AjaxDocument());
        }

        /* additional actions */
        if ( OW::getUser()->isAuthenticated() )
        {
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_UPDATE_ACTIVITY_TIMESTAMP));
        }

        // adding global template vars
        $currentThemeImagesDir = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl();
        $currentThemeStaticUrl = OW::getThemeManager()->getCurrentTheme()->getStaticUrl();

        $viewRenderer = OW_ViewRenderer::getInstance();
        $viewRenderer->assignVar('themeImagesUrl', $currentThemeImagesDir);
        $viewRenderer->assignVar('themeStaticUrl', $currentThemeStaticUrl);
        $viewRenderer->assignVar('siteName', OW::getConfig()->getValue('base', 'site_name'));
        $viewRenderer->assignVar('siteTagline', OW::getConfig()->getValue('base', 'site_tagline'));
        $viewRenderer->assignVar('siteUrl', OW_URL_HOME);
        $viewRenderer->assignVar('isAuthenticated', OW::getUser()->isAuthenticated());
        $viewRenderer->assignVar('bottomPoweredByLink', '<a href="https://www./" target="_blank" title="'.OW::getLanguage()->text('base','powered_by_community').'"><img alt="'.OW::getLanguage()->text('base','powered_by_community').'" src="' . $currentThemeImagesDir . 'powered-by-.png" /></a>');
    }

    /**
     * Finds controller and action for current request.
     */
    public function route()
    {
        try
        {
            OW::getRequestHandler()->setHandlerAttributes(OW::getRouter()->route());
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
        }
    }

    /**
     * ---------
     */
    public function handleRequest()
    {
        $baseConfigs = OW::getConfig()->getValues('base');

        //members only
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_CANT_VIEW && !OW::getUser()->isAuthenticated() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_User',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'standardSignIn'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.members_only', $attributes);
            $this->addCatchAllRequestsException('base.members_only_exceptions', 'base.members_only');
        }

        //splash screen
        if ( (bool) OW::getConfig()->getValue('base', 'splash_screen') && !isset($_COOKIE['splashScreen']) )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'splashScreen',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_JS => true,
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ROUTE => 'base_page_splash_screen'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.splash_screen', $attributes);
            $this->addCatchAllRequestsException('base.splash_screen_exceptions', 'base.splash_screen');
        }

        // password protected
        if ( (int) $baseConfigs['guests_can_view'] === BOL_UserService::PERMISSIONS_GUESTS_PASSWORD_VIEW && !OW::getUser()->isAuthenticated() && !isset($_COOKIE['base_password_protection'])
        )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'passwordProtection'
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.password_protected', $attributes);
            $this->addCatchAllRequestsException('base.password_protected_exceptions', 'base.password_protected');
        }

        // maintenance mode
        if ( (bool) $baseConfigs['maintenance'] && !OW::getUser()->isAdmin() )
        {
            $attributes = array(
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_CTRL => 'BASE_CTRL_BaseDocument',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_ACTION => 'maintenance',
                OW_RequestHandler::CATCH_ALL_REQUEST_KEY_REDIRECT => true
            );

            OW::getRequestHandler()->setCatchAllRequestsAttributes('base.maintenance_mode', $attributes);
            $this->addCatchAllRequestsException('base.maintenance_mode_exceptions', 'base.maintenance_mode');
        }

        try
        {
            OW::getRequestHandler()->dispatch();
        }
        catch ( RedirectException $e )
        {
            $this->redirect($e->getUrl(), $e->getRedirectCode());
        }
        catch ( InterceptException $e )
        {
            OW::getRequestHandler()->setHandlerAttributes($e->getHandlerAttrs());
            $this->handleRequest();
        }
    }

    /**
     * Method called just before request responding.
     */
    public function finalize()
    {
        $document = OW::getDocument();

        $meassages = OW::getFeedback()->getFeedback();

        foreach ( $meassages as $messageType => $messageList )
        {
            foreach ( $messageList as $message )
            {
                $document->addOnloadScript("OW.message(" . json_encode($message) . ", '" . $messageType . "');");
            }
        }

        $event = new OW_Event(OW_EventManager::ON_FINALIZE);
        OW::getEventManager()->trigger($event);
    }

    /**
     * System method. Don't call it!!!
     */
    public function onBeforeDocumentRender()
    {
        $document = OW::getDocument();
        $themeManager = OW::getThemeManager();
        $customThemeEvent = OW::getEventManager()->trigger(new OW_Event('frmthememanager.on.before.theme.style.renderer', array()));
        $customTheme = (isset($customThemeEvent->getData()['url']) && $customThemeEvent->getData()['url'] != null);

        $document->addScriptDeclarationBeforeIncludes("var OW_URL_HOME='".OW_URL_HOME."';");
        if (FRMSecurityProvider::themeCoreDetector() || $customTheme ){
            $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'ow.css', 'all', -100);
        }else{
            $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'old_core/ow.css', 'all', -100);
        }

        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if( $customTheme && !$adminEvent ){
            $document->addStyleSheet($customThemeEvent->getData()['url'], 'all', (-90));
        }else{
            $document->addStyleSheet($themeManager->getCssFileUrl(), 'all', (-90));
        }

        // add custom css if page is not admin TODO replace with another condition
        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( $themeManager->getCurrentTheme()->getDto()->getCustomCssFileName() !== null )
            {
                $document->addStyleSheet($themeManager->getThemeService()->getCustomCssFileUrl($themeManager->getCurrentTheme()->getDto()->getKey()));
            }

            if ( $this->getDocumentKey() !== 'base.sign_in' )
            {
                $customHeadCode = OW::getConfig()->getValue('base', 'html_head_code');
                $customAppendCode = OW::getConfig()->getValue('base', 'html_prebody_code');

                if ( !empty($customHeadCode) )
                {
                    $document->addCustomHeadInfo($customHeadCode);
                }

                if ( !empty($customAppendCode) )
                {
                    $document->appendBody($customAppendCode);
                }
            }
        }
        else
        {
            $document->addStyleSheet(OW::getPluginManager()->getPlugin('admin')->getStaticCssUrl() . 'admin.css', 'all', -50);
        }

        $this->check_file_size_js();
        // add current theme name to body class
        $document->addBodyClass($themeManager->getCurrentTheme()->getDto()->getKey());

        $language = OW::getLanguage();
        OW::getLanguage()->addKeyForJs('base', 'are_you_sure');

        if ( $document->getTitle() === null )
        {
            $document->setTitle($language->text('nav', 'page_default_title'));
        }

        if ( $document->getDescription() === null )
        {
            $document->setDescription($language->text('nav', 'page_default_description'));
        }

        if ( $document->getKeywords() === null )
        {
            $words = '';
            if ( $document->getTitle() != null )
            {
                $words = $document->getTitle() .' ';
            }
            if ( $document->getDescription() != null )
            {
                $words .= $document->getDescription() .' ';
            }
            $document->setKeywords( str_replace(' ', ',', $words) . $language->text('nav', 'page_default_keywords'));
        }

        if ( $document->getHeadingIconClass() === null )
        {
            $document->setHeadingIconClass('ow_ic_file');
        }

        if ( !empty($this->documentKey) )
        {
            $document->addBodyClass($this->documentKey);
        }

        if ( $this->getDocumentKey() !== null )
        {
            $masterPagePath = OW::getThemeManager()->getDocumentMasterPage($this->getDocumentKey());

            if ( $masterPagePath !== null )
            {
                $document->getMasterPage()->setTemplate($masterPagePath);
            }
        }
    }

    /***
     *
     */
    public function check_file_size_js(){
        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage ){
            $maxUploadSize = OW::getConfig()->getValue('base', 'attch_file_max_size_mb');
            $validFileExtensions = json_decode(OW::getConfig()->getValue('base', 'attch_ext_list'), true);
            $checkExtension = "
    var validFileExtensions = ".json_encode($validFileExtensions).";
    function jsItemInArrayExists(arr, obj) {
        for(var i=0; i<arr.length; i++) {
            if (arr[i] == obj) return true;
        }
        return false;
    }
    for(var i=0; i<input.files.length; i++) {
        var ext = '';
        if(input.files[i].name.lastIndexOf('.')>0){
            ext = input.files[i].name.substr(input.files[i].name.lastIndexOf('.')+1);
        }
        if(!jsItemInArrayExists(validFileExtensions,ext.toLowerCase())){
            OW.error('".OW::getLanguage()->text('base', 'upload_file_extension_is_not_allowed')."');
            return false;
        }
    }";
        }else{
            $maxUploadSize = BOL_FileService::getInstance()->getUploadMaxFilesize();
            $checkExtension = "";
        }
        $js ="
var checkFileInputSizeAndExtension = function(input) {
    if(input.files.length==0)
    {
        return true;
    }

    ".$checkExtension."
    if(input.files[0].size>".$maxUploadSize."*1024*1024){
        OW.error('".OW::getLanguage()->text('base', 'upload_file_max_upload_filesize_error')."');
        return false;
    }
};

$(function() {
    var inputFileItems;
    var change_function_wrapper = function(e) {
        if($(this).val()) {
            if(checkFileInputSizeAndExtension) {
                var att_element = $(this)[0];
                if(false){
                    var before_id = $(this).attr('id');
                    $(this).attr('id', 'input_uploader_tmp');
                    id = $(this).attr('id');
                    var att_element = document.getElementById(id);
                    if(before_id==undefined)
                        $(this).removeAttr('id');
                    else
                        $(this).attr('id', before_id);
                }
                if(att_element != null){
                    if (checkFileInputSizeAndExtension(att_element)==false) {
                        att_element.value = '';
                        var inputFile = $(this);
                        inputFile.val('');
                        var before_events = jQuery._data(att_element).events;
                        //inputFile.replaceWith(cloned = inputFile.clone(true));
                        e.preventDefault(e);
                        return false;
                    }
                }
            }
            $(this).trigger('changeOthers');
        }
    };
    setInterval(function() {
        var text_input_selector = 'input[type=file]';
        inputFileItems = $(text_input_selector).length;
        $(text_input_selector).each(function(i,elem){
            if($(elem).attr('changeFunctionSet')==undefined){
                $(elem).attr('changeFunctionSet', true);
                if(jQuery._data(elem).events == undefined){
                    $(elem).on('change',change_function_wrapper);
                }else{
                    jQuery._data(elem).events.changeOthers = jQuery._data(elem).events.change;
                    jQuery._data(elem).events.change = jQuery._data(elem).events.changeX;
                    $(elem).on('change',change_function_wrapper);
                }
            }
        });
    }, 1000);
});
            ";
        OW::getDocument()->addScriptDeclarationBeforeIncludes( $js);
    }

    /**
     * Triggers response object to send rendered page.
     */
    public function returnResponse()
    {
        OW::getResponse()->respond();
    }

    /**
     * Makes header redirect to provided URL or URI.
     *
     * @param string $redirectTo
     */
    public function redirect( $redirectTo = null, $switchContextTo = false )
    {
        if ( $switchContextTo !== false && in_array($switchContextTo, array(self::CONTEXT_DESKTOP, self::CONTEXT_MOBILE)) )
        {
            OW::getSession()->set(self::CONTEXT_NAME, $switchContextTo);
        }

        // if empty redirect location -> current URI is used
        if ( $redirectTo === null )
        {
            $redirectTo = OW::getRequest()->getRequestUri();
        }

        // if URI is provided need to add site home URL
        if ( !strstr($redirectTo, 'http://') && !strstr($redirectTo, 'https://') )
        {
            $redirectTo = OW::getRouter()->getBaseUrl() . UTIL_String::removeFirstAndLastSlashes($redirectTo);
        }

        UTIL_Url::redirect($redirectTo);
    }

    public function getContext()
    {
        return $this->context;
    }

    public function isMobile()
    {
        return $this->context == self::CONTEXT_MOBILE;
    }

    public function isDesktop()
    {
        return $this->context == self::CONTEXT_DESKTOP;
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /**
     * Menu item to activate.
     *
     * @var BOL_MenuItem
     */
    protected $indexMenuItem;

    public function activateMenuItem()
    {
        if ( !OW::getDocument()->getMasterPage() instanceof ADMIN_CLASS_MasterPage )
        {
            if ( OW::getRequest()->getRequestUri() === '/' || OW::getRequest()->getRequestUri() === '' )
            {
                OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, $this->indexMenuItem->getPrefix(),
                    $this->indexMenuItem->getKey());
            }
        }
    }
    /* private auxilary methods */

    /***
     * commands for both desktop and mobile version
     */
    protected function newGeneralDocument(){
        $language = BOL_LanguageService::getInstance()->getCurrent();
        $document = new OW_HtmlDocument();
        $document->setCharset('UTF-8');
        $document->setMime('text/html');
        $document->setLanguage($language->getTag());

        if ( $language->getRtl() )
        {
            $document->setDirection('rtl');
        }
        else
        {
            $document->setDirection('ltr');
        }

        if ( (bool) OW::getConfig()->getValue('base', 'favicon') )
        {
            $document->setFavicon(OW::getPluginManager()->getPlugin('base')->getUserFilesUrl() . 'favicon.ico');
        }

        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.min.js',
            'text/javascript', (-100));
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-migrate.min.js',
            'text/javascript', (-100));
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery.form.min.js',
            'text/javascript', (-100));
        $document = $this->includeJConfirm($document);

        if (defined('GRAPHICS_MODE') && GRAPHICS_MODE) {
            $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'graphics_mode.js',
                'text/javascript', (-100));
        }

        return $document;
    }

    protected function newDocument()
    {
        $document = $this->newGeneralDocument();

        // specific to desktop version
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'ow.js', 'text/javascript', (-50));

        $onloadJs = "OW.bindAutoClicks();OW.bindTips($('body'));";
        if ( OW::getUser()->isAuthenticated() )
        {
            $onloadJs .= "OW.getPing().addCommand('user_activity_update').start(600000);";
        }
        $document->addOnloadScript($onloadJs);

        OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($this, 'onBeforeDocumentRender'));
        return $document;
    }

    /***
     * @param $document
     * @return mixed
     */
    protected function includeJConfirm($document){
        $document->addStyleSheet(OW::getPluginManager()->getPlugin('base')->getStaticCssUrl() . 'jquery-confirm.min.css');
        $document->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'jquery-confirm.min.js','text/javascript', (-10));
        $rtl = '';
        if (BOL_LanguageService::getInstance()->getCurrent()->getTag()==='fa-IR')
        {
            $rtl = ' rtl: true,';
        }
        $document->addScriptDeclaration("
jconfirm.defaults = {
    title: '', titleClass: 'warning', type: 'white', smoothContent: true, 
    draggable: true, dragWindowGap: 15, dragWindowBorder: true,
    content: '".OW::getLanguage()->text('base', 'are_you_sure')."',
    icon: 'fa fa-question', theme: 'material', animation: 'scale',$rtl
    backgroundDismiss: true, closeIcon: true,
    defaultButtons: {
        ok: {
            text: '".OW::getLanguage()->text('base', 'ok')."',
            btnClass: 'btn-orange',
        },
        close: {
            text: '".OW::getLanguage()->text('base', 'cancel')."',
        },
    },
};");
        return $document;
    }

    protected function userAutoLogin()
    {
        if ( OW::getSession()->isKeySet('no_autologin') )
        {
            OW::getSession()->delete('no_autologin');
            return;
        }

        if ( !empty($_COOKIE['ow_login']) && !OW::getUser()->isAuthenticated() )
        {
            $id = BOL_UserService::getInstance()->findUserIdByCookie(trim($_COOKIE['ow_login']));

            if ( !empty($id) )
            {
                OW::getLogger()->writeLog(OW_Log::INFO, 'user_auto_login', ['actionType'=>OW_Log::UPDATE, 'enType'=>'user', 'enId'=>$id], false);
                OW_User::getInstance()->login($id);
                BOL_UserService::getInstance()->setLoginCookie(trim($_COOKIE['ow_login']));
            }
        }
    }

    protected function addCatchAllRequestsException( $eventName, $key )
    {
        $event = new BASE_CLASS_EventCollector($eventName);
        OW::getEventManager()->trigger($event);
        $exceptions = $event->getData();

        foreach ( $exceptions as $item )
        {
            if ( is_array($item) && !empty($item['controller']) && !empty($item['action']) )
            {
                OW::getRequestHandler()->addCatchAllRequestsExclude($key, trim($item['controller']),
                    trim($item['action']));
            }
        }
    }

    protected function initRequestHandler()
    {
        OW::getRequestHandler()->setIndexPageAttributes('BASE_CTRL_ComponentPanel');
        OW::getRequestHandler()->setStaticPageAttributes('BASE_CTRL_StaticDocument');
    }

    protected function findAllStaticDocs()
    {
        return BOL_NavigationService::getInstance()->findAllStaticDocuments();
    }

    protected function findFirstMenuItem( $availableFor )
    {
        return BOL_NavigationService::getInstance()->findFirstLocal($availableFor, OW_Navigation::MAIN);
    }

    protected function getSiteRootRoute()
    {
        return new OW_Route('base_default_index', '/', 'BASE_CTRL_ComponentPanel', 'index');
    }

    protected function getMasterPage()
    {
        return new OW_MasterPage();
    }

    protected function detectLanguage()
    {
        $languageId = 0;

        if ( !empty($_GET['language_id']) )
        {
            $languageId = intval($_GET['language_id']);
        }
        else if ( !empty($_COOKIE[BOL_LanguageService::LANG_ID_VAR_NAME]) )
        {
            $languageId = intval($_COOKIE[BOL_LanguageService::LANG_ID_VAR_NAME]);
        }

        if( $languageId > 0 )
        {
            OW::getSession()->set(BOL_LanguageService::LANG_ID_VAR_NAME, $languageId);
        }

        $session_language_id = OW::getSession()->get(BOL_LanguageService::LANG_ID_VAR_NAME);
        $languageService = BOL_LanguageService::getInstance();

        if( $session_language_id  )
        {
            $dto = $languageService->findById($session_language_id);

            if( $dto !== null && $dto->getStatus() == "active" )
            {
                $languageService->setCurrentLanguage($dto);
            }
        }

        $languageService->getCurrent();

        setcookie(BOL_LanguageService::LANG_ID_VAR_NAME, strval($languageService->getCurrent()->getId()), time() + 60 * 60 * 24 * 30, "/");
    }
}
