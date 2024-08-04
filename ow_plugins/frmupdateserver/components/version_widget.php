<?php
/**
 * Version widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMUPDATESERVER_CMP_VersionWidget extends BASE_CLASS_Widget
{

    /**
     * FRMUPDATESERVER_CMP_VersionWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $service = FRMUPDATESERVER_BOL_Service::getInstance();

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
        $this->assign('download_last_core_update_build_version', OW::getLanguage()->text('frmupdateserver', 'download_last_core_update_build_version', array('value' => $buildNumber)));
        $this->assign('urlOfCoreMainLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/main'));
        $this->assign('urlOfSha256CoreMainLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/main').'.sha256');
        $this->assign('urlOfCoreUpdateLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/updates'));
        $this->assign('urlOfSha256CoreUpdateLatestVersions', $service->getUrlOfLastVersionsOfItem('core', $allVersions, 'core/updates').'.sha256');
        $this->assign('date_core_released', $time);
        $this->assign('urlOfAllCoreVersions', $service->getPathOfFTP() . 'core');
        $cssDir = OW::getPluginManager()->getPlugin("frmupdateserver")->getStaticCssUrl();
        OW::getDocument()->addStyleSheet($cssDir . "frmupdateserver.css");
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmupdateserver', 'title_widget'),
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}