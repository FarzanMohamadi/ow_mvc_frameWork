<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmdemo.bol
 * @since 1.0
 */
class FRMDEMO_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        $eventManager->bind('base.members_only_exceptions', array($this, 'catchAllRequestsExceptions'));
    }

    public function catchAllRequestsExceptions(BASE_CLASS_EventCollector $event)
    {
        $event->add(array(
            OW_RequestHandler::ATTRS_KEY_CTRL => 'FRMDEMO_CTRL_Demo',
            OW_RequestHandler::ATTRS_KEY_ACTION => 'changeTheme'
        ));

    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
        if (strpos($_SERVER['REQUEST_URI'], '/admin') === false && strpos($_SERVER['REQUEST_URI'], '/lock') === false) {
            $themes = BOL_ThemeService::getInstance()->findAllThemes();
            foreach ($themes as $key => $row) {
                $titles[$key]  = $row->getTitle();
            }
            array_multisort($titles, SORT_ASC, $themes);

            $currentTheme = OW::getConfig()->getValue('base', 'selectedTheme');
            $themeOptions = '';
            $ignoreThemesKeyList = array();
            if(FRMSecurityProvider::checkPluginActive('frmupdateserver', true)) {
                $ignoreThemesKeyList = FRMUPDATESERVER_BOL_Service::getInstance()->getIgnoreThemesKeyList();
            }else{
                $response = UTIL_HttpClient::get(BOL_StorageService::UPDATE_SERVER. "get-ignore-themes");
                if ( $response && $response->getStatusCode() == UTIL_HttpClient::HTTP_STATUS_OK && $response->getBody() )
                {
                    $ignoreThemesKeyList = json_decode($response->getBody());
                }
            }

            foreach ($themes as $theme) {
                if(!in_array($theme->getKey(), $ignoreThemesKeyList)) {
                    $selected = '';
                    if ($currentTheme == $theme->getName()) {
                        $selected = 'selected';
                    }
                    $themeOptions .= '<option value="' . $theme->getName() . '" ' . $selected . '>' . $theme->getTitle() . '</option>';
                }
            }
            $remainingMinutes = 61 - date("i");
            $remainingSeconds = $remainingMinutes * 60;
            $countDownElement = ' <span id="countdown_demo_timer">' . $remainingMinutes . '</span> ';
            $countDownJs = 'startTimer(' . $remainingSeconds . ', document.getElementById(\'countdown_demo_timer\'));';
            $chooseThemeLink = OW::getLanguage()->text('frmdemo', 'theme') . ' <select id="demo_themes_items" onchange="changeDemoTheme(\'' . OW::getRouter()->urlForRoute('frmdemo.change-theme') . '\')" style="background:white">' . $themeOptions . '</select>';
            $adminPanelLink = '( <a href="' . OW::getRouter()->urlForRoute("admin_default") . '">' . OW::getLanguage()->text('frmdemo', 'admin_panel') . '</a> )';
            $rightOrLeftClass = 'demo-nav-span-right';
            if (BOL_LanguageService::getInstance()->getCurrent()->getRtl()) {
                $rightOrLeftClass = 'demo-nav-span-left';
            }
            $demoDiv = '<div id="div_demo" class="demo-nav">';
            $demoDiv .= '<span class="' . $rightOrLeftClass . '">' . $chooseThemeLink . '</span>';
            $demoDiv .= '<span class="' . $rightOrLeftClass . ' timer">' . OW::getLanguage()->text('frmdemo', 'reset_data') . $countDownElement . '</span>';
            $demoDiv .= '<span class="' . $rightOrLeftClass . ' link">' . $adminPanelLink . '</span></div>';
            $DemoDivJS = '$(\'body\').append(\'' . str_replace("'", "\\'", $demoDiv) . '\')';
            OW::getDocument()->addScriptDeclaration($DemoDivJS);
            OW::getDocument()->addOnloadScript($countDownJs);
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmdemo')->getStaticJsUrl() . 'frmdemo.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmdemo')->getStaticCssUrl() . 'frmdemo.css');
        }
    }
}