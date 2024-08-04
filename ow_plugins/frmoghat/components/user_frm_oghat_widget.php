<?php
/**
 * FRM Oghat widget
 *
 * @since 1.0
 */
class FRMOGHAT_CMP_UserIisOghatWidget extends BASE_CLASS_Widget
{

    /**
     * FRMOGHAT_CMP_UserIisOghatWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {

        $sevices = FRMOGHAT_BOL_Service::getInstance();
        $cities = $sevices->getAllCity();

        $defaultCity = array();

        $citiesList = array();
        foreach($cities as $city){
            $cityInformation['name'] = $city->name;
            $cityInformation['longitude'] = $city->longitude;
            $cityInformation['latitude'] = $city->latitude;
            $cityInformation['default'] = false;

            if($city->default == 1){
                $cityInformation['default'] = true;
                $defaultCity['name'] = $city->name;
                $defaultCity['longitude'] = $city->longitude;
                $defaultCity['latitude'] = $city->latitude;
            }
            $citiesList[] = $cityInformation;
        }

        $this->assign('cities',$citiesList);
        $lang = OW::getLanguage();

        $lang->addKeyForJs('frmoghat', 'Azan_am');
        $lang->addKeyForJs('frmoghat', 'Azan_pm');
        $lang->addKeyForJs('frmoghat', 'Sunrise');
        $lang->addKeyForJs('frmoghat', 'Sunset');
        $lang->addKeyForJs('frmoghat', 'azan_maghreb');
        $lang->addKeyForJs('frmoghat', 'Azan_am_time_horizon');
        $lang->addKeyForJs('frmoghat', 'Azan_pm_time_horizon');
        $lang->addKeyForJs('frmoghat', 'azan_maghreb_time_horizon');
        $lang->addKeyForJs('frmoghat', 'until');

        $timeUrl = OW::getRouter()->urlForRoute('frmoghat.get.time');
        $this->assign('timeUrl',$timeUrl);

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmoghat')->getStaticJsUrl() . 'frmoghat.js', 'text/javascript', (-100));
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmoghat')->getStaticCssUrl() . 'frmoghat.css' , 'all', -100);
        $callMainFunction = 'main_oghat(\''.$defaultCity['name'].'\',\''.$timeUrl.'\');';
        OW::getDocument()->addOnloadScript('coord('.$defaultCity['longitude'].', '.$defaultCity['latitude'].');'.$callMainFunction.'setInterval("'.$callMainFunction.'",30000);');
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmoghat', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}