<?php
function ensure_session_active()
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function ensure_no_session()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

if (!defined('STDERR')) {
    define('STDERR', fopen('php://stderr', 'w'));
}
function exception_on_error($errno, $errstr, $errfile=null, $errline=null) {
    if (error_reporting() === 0) {
        return false;
    }
    fwrite(STDERR, "errno=$errno, errstr=$errstr, errfile=$errfile, errline=$errline\n");
    $message = strtok($errstr, "\n");
    throw new Exception("errno=$errno, message=$message, errfile=$errfile, errline=$errline");
}

function enable_debug_mode(){
    $filename = OW_DIR_ROOT . 'ow_includes' . DS . 'config.php';
    $data = file($filename); // reads an array of lines
    foreach ($data as $k => $line) {
        if (strpos($line, 'OW_DEBUG_MODE') !== false) {
            $data[$k] = "    define('OW_DEBUG_MODE', true);\r\n";
        }
    }

    $data[] = "\r\n\r\nini_set('display_errors', 1);\nini_set('display_startup_errors', 1);\nerror_reporting(E_ALL);\n";

    file_put_contents($filename, implode('', $data));
    sleep(1);
}

try
{
    set_error_handler('exception_on_error');
	define('_OW_', true);
    define('OW_CRON', true);
	define('DS', DIRECTORY_SEPARATOR);
	define('OW_DIR_ROOT', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);
	define('DEFAULT_TIMEOUT_MILLIS', 15000);

    enable_debug_mode();

	require_once(OW_DIR_ROOT . 'ow_includes' . DS . 'init.php');
	//require_once(OW_DIR_ROOT . 'ow_libraries' . DS . 'vendor' . DS . 'autoload.php');

    // logger
    OW::getLogger()->writeLog(OW_Log::NOTICE, 'phpunit tests started!');

	OW::getSession()->start();

	OW::getRouter()->setBaseUrl(OW_URL_HOME);

	date_default_timezone_set(OW::getConfig()->getValue('base', 'site_timezone'));
	OW_Auth::getInstance()->setAuthenticator(new OW_SessionAuthenticator());

	OW::getPluginManager()->initPlugins();
	$event = new OW_Event(OW_EventManager::ON_PLUGINS_INIT);
	OW::getEventManager()->trigger($event);

	OW::getThemeManager()->initDefaultTheme();

	// setting current theme
	$activeThemeName = OW::getConfig()->getValue('base', 'selectedTheme');
	if ( $activeThemeName !== BOL_ThemeService::DEFAULT_THEME && OW::getThemeManager()->getThemeService()->themeExists($activeThemeName) )
	{
        OW_ThemeManager::getInstance()->setCurrentTheme(BOL_ThemeService::getInstance()->getThemeObjectByKey(trim($activeThemeName)));
	}

    // set theme to the new version
    try {
        BOL_PluginService::getInstance()->install('frmthememanager');
    }catch (LogicException $e){}
    OW::getConfig()->saveConfig('base', 'selectedTheme', 'frmmodernblue');
    OW::getConfig()->saveConfig('frmthememanager', 'themesList', '["techpark"]');
    OW::getConfig()->saveConfig('frmthememanager', 'techpark', '{"form_name":"newTheme","csrf_token":"MTYwNDMyOTAwM3ZJcVlwM0d1cFU1aWRJODFzaVNvbTRySURhV2lSaUg0","csrf_hash":"23d02437a84a715936e44c54f892a96c","themeName":"\u067e\u0648\u0633\u062a\u0647 \u0645\u062f\u0631\u0646 \u067e\u0627\u0631\u06a9\u200c\u0647\u0627\u06cc \u0639\u0644\u0645 \u0648 \u0641\u0646\u0627\u0648\u0631\u06cc","themeKey":"techpark","parentTheme":"frmmodernblue","primaryColor":"#0042ae","secondaryColor":"#4785b8","backgroundAndBorderColor":"#4785b8","backgroundColor":"#f2f5f7","HeaderBackgroundColor":"#002d77","footerBackgroundColor":"#3e3d42","HeaderItemBackgroundColor":"#ffffff","HeaderItemHoverTextColor":"#001b46","HeaderItemTextColor":"#f8fafc","linksColor":"#0042ae","linksColorHover":"#0042ae","verifyColor":"#acfde7","Header":"","background":"","mainLogo":"","mainWhiteLogo":"","headerLogo":"","fileRemoveList":"","themeConfigs":"","themeStyle":".ow_header_pic {\r\n    height: 210px !important;\r\n    border-bottom: none !important;\r\nbackground-size: contain !important;\r\nbackground-position: top center !important;\r\n}\r\nbody {\r\n    background-color: #f5f5f5 !important;\r\n}\r\n.ow_site_panel {\r\n    margin-top: 165px;\r\n}\r\n.ow_menu_wrap {\r\n    background-color: transparent;\r\n    top: -1px;\r\n}\r\n\r\n@media (min-width: 680px) {\r\n\r\n.ow_main_menu li.active a, .ow_main_menu li.active a:hover, .ow_main_menu li a:hover {\r\n    background: transparent !important;\r\n    color: white !important;\r\n    border-bottom: 5px solid white !important;\r\n    padding-bottom: 6px !important;\r\n}\r\n.ow_main_menu li a {\r\n    color: #ffffff8c !important;\r\n}\r\n.ow_main_menu li a span {\r\n    font-size: 14px;\r\n}\r\n.ow_main_menu li a:hover {\r\n    border-bottom-color: transparent !important;\r\n}\r\n\r\n.ow_main_menu li.active a, .ow_main_menu li a:hover {\r\n    padding-bottom: 11px;\r\n    border-radius: 4px 4px 0px 0px;\r\n}\r\n\r\n}\r\n\r\n\r\n.ow_page_padding {\r\n    padding-top: 20px;\r\n}\r\n.ow_group_list .ow_automargin.ow_superwide form {\r\n    max-width: 750px;\r\n}\r\n.ow_console_right {\r\n    top: -20px;\r\n}\r\nbody > .ow_page_wrap .ow_menu_wrap {\r\n    border-bottom: 10px solid white;\r\n    padding-bottom: 0;\r\n}\r\nbody.base_user_dashboard .dashboard-NEWSFEED_CMP_MyFeedWidget div.category_section {\r\n    margin-top: 10px;\r\n}\r\n.ow_box_empty.ow_highbox.ow_stdmargin.index_customize_box.ow_no_cap.ow_break_word.container {\r\n    background-color: transparent;\r\n    position: absolute;\r\n    z-index: 5;\r\n    top: 0;\r\n    left: 0;\r\nbox-shadow: none;\r\n}\r\n\r\n.ow_box_empty.ow_highbox.ow_stdmargin.index_customize_box.ow_no_cap.ow_break_word.container input#goto_customize_btn {\r\n    color: white;\r\n}\r\n\r\na.ow_logo.ow_left {\r\n    top: -115px;\r\n    position: relative;\r\n    width: 360px;\r\n    height: 60px;\r\n}\r\n\r\n\r\n.ow_console_right {\r\n    top: -60px  !important;\r\n}\r\n.ow_header {\r\n    position: absolute;\r\n    top: 0px;\r\n    width: 100%;\r\n    height: 210px;\r\n    background-image: linear-gradient(-90deg, #16348E, #1938B3);\r\n}\r\n.ow_header_pic {\r\n    background-color: transparent !important;\r\n}\r\n.ow_dnd_widget.dashboard-NEWSFEED_CMP_MyFeedWidget {\r\n    box-shadow: 0 0 6px #c1c1c1;\r\n}\r\n.base_index_page .ow_dnd_widget {\r\n    box-shadow: 0 0 6px #c1c1c1;\r\n}\r\n\r\n.ow_site_panel.clearfix .ow_logo {\r\n    z-index: 2;\r\n}\r\n\r\n\r\n.ow_user_list div:not(:last-child) .ow_user_list_item {\r\n    min-height: 205px;\r\n}\r\n.group_users_list_page span.ow_button.ow_ic_add {\r\n    margin-top: -55px;\r\n}\r\nbody.base_users .ow_user_list_item .ow_user_list_data .user_item_profile_questions_item {\r\n    height: initial;\r\n}\r\n.base_users .ow_user_list div:not(:last-child) .ow_user_list_item {\r\n    min-height: 220px;\r\n}\r\n.ow_friends_list .ow_user_list_item.clearfix.ow_item_set3 {\r\n    min-height: initial !important;\r\n}\r\n\r\n#main-channels-list > a {\r\n    display: block;\r\n    padding: 0 26px 0 3px;\r\n    margin-bottom: 1px;\r\n    text-decoration: none;\r\n    white-space: nowrap;\r\n    overflow: hidden;\r\n    text-overflow: ellipsis;\r\n    transition-duration: 0.2s;\r\n    background-position: right 2px;\r\n    background-image: url(https:\/\/tpnet.msrt.ir\/ow_static\/plugins\/base\/css\/old_core\/images\/miniic_li_fa.svg);\r\n    background-repeat: no-repeat;\r\n    color: #7b7b7b;\r\n}\r\n#main-channels-list > a:hover {\r\n    background-color: #0042ae;\r\n    color: white;\r\n    border-radius: 3px;\r\n    margin-right: 3px;\r\n}\r\n\r\n.powered_by_logo {\r\n    background-color: #005eff;\r\n}\r\n.tiser_video_class div#mep_0 {\r\n    display: block;\r\n    margin: auto;\r\n    max-height: 600px;\r\n}\r\n.tiser_video_class .mejs__container {\r\n    background-color: transparent;\r\n}\r\n.mejs__button.mejs__download-button {\r\n    display: none;\r\n}\r\n.tiser_video_class .mejs__overlay-button {\r\n    background-color: #0e0e0e;\r\n    opacity: 0.7;\r\n    border-radius: 50%;\r\n}\r\n.mejs__controls span {\r\n    line-height: inherit;\r\n}\r\n.mejs__container * {\r\n    max-width: 100% !important;\r\n}\r\n\r\n#video_list_widget .ow_other_video_thumb img {\r\n    width: inherit !important;\r\n    height: auto !important;\r\n}\r\n\r\n#video_list_widget .ow_other_video_thumb a{\r\n    height: 45px;\r\n}\r\n\r\n#video_list_widget .ow_other_video_thumb.ow_left {\r\n    height: 45px;\r\n}","footerTags":"","themeColors":{"primaryColor":"#0042ae","secondaryColor":"#4785b8","backgroundAndBorderColor":"#4785b8","backgroundColor":"#f2f5f7","HeaderBackgroundColor":"#002d77","footerBackgroundColor":"#3e3d42","HeaderItemBackgroundColor":"#ffffff","HeaderItemHoverTextColor":"#001b46","HeaderItemTextColor":"#f8fafc","linksColor":"#0042ae","linksColorHover":"#0042ae","verifyColor":"#acfde7"},"urls":{"Header":"https:\/\/tptest.frmcenter.ir\/ow_userfiles\/plugins\/frmthememanager\/frmmodernblue_techpark_Header.png","mainWhiteLogo":"https:\/\/tptest.frmcenter.ir\/ow_userfiles\/plugins\/frmthememanager\/frmmodernblue_techpark_mainWhiteLogo.png","headerLogo":"https:\/\/tptest.frmcenter.ir\/ow_userfiles\/plugins\/frmthememanager\/frmmodernblue_techpark_headerLogo.png","tabIcons":"https:\/\/tptest.frmcenter.ir\/ow_userfiles\/plugins\/frmthememanager\/frmmodernblue_techpark_tab_icons_1.svg"},"fileName":"frmmodernblue_techpark","mobileFileName":"frmmodernblue_techpark_mobile","configs":""}');
    OW::getConfig()->saveConfig('frmthememanager', 'activeTheme', null);


	require_once(OW_DIR_ROOT . 'ow_frm' . DS .'test'. DS . 'FRMTestUtilites.php');
    require_once(OW_DIR_ROOT . 'ow_frm' . DS .'test'. DS . 'FRMUnitTestUtilites.php');
	try{
        FRMSecurityProvider::createBackupTables(new OW_Event(''));
    }catch (Exception $ignored){
    }
    FRMSecurityProvider::updateStaticFiles();
    FRMSecurityProvider::installAllAvailablePlugins();

    require_once(OW_DIR_ROOT . 'ow_unittest' . DS. 'ow_core' . DS . 'baseFunctions.php');
    if(!UnittestBaseFunctions::isSMTPWorking()) {
        $def_smtp_config = array(
            'host' => 'mail.frmcenter.ir',
            'port' => '25',
            'username' => 'notiftest@frmcenter.ir',
            'password' => 'N0Tif3e#',
            'prefix' => '');
        UnittestBaseFunctions::setDefaultSMTPSettings($def_smtp_config);
        sleep(3);
    }
    if(UnittestBaseFunctions::isSMTPWorking()) {
        fwrite(STDERR, "SMTP default is set. \r\n");
    }
    else{
        fwrite(STDERR, "SMTP is not working! \r\n");
    }
}
catch (Exception $ex)
{
    fwrite(STDERR, "Error in PHPUnit bootstrap (init.php):\n".$ex."\nSolve the problem and run again\n");
	throw $ex;
}
