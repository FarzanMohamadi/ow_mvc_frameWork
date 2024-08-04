<?php
/***
 * Author: Farzan Mohammadi
 * Class ADMIN_CMP_OnlineUsersStatisticWidget
 */
class ADMIN_CMP_OnlineUsersStatisticWidget extends ADMIN_CMP_AbstractStatisticWidget
{
    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj ) {
        parent::__construct();
    }

    /**
     * @throws Redirect404Exception
     */
    public function onBeforeRender()
    {
        $socketEnabled = FRMSecurityProvider::isSocketEnable();
        $this->assign('socketEnabled', $socketEnabled);
        OW::getDocument()->addScriptDeclarationBeforeIncludes("var streamOnlineUserChart;");
        $this->assign('chartId', 'online-users-statistics-container');
        $data[] = array(
            'data' => array(0)
        );
        $this->assign('data', json_encode($data, JSON_NUMERIC_CHECK));
        $this->assign('categories', json_encode(array(''), JSON_NUMERIC_CHECK));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'chart.js');

        if ($socketEnabled) {
            OW::getDocument()->addScriptDeclaration('streamOnlineUser()', "text/javascript", 10000);
        }
    }

    /**
     * Get chart color
     *
     * @param integer $num
     * @return array
     */
    protected function getChartColor($num)
    {
        $hash = md5('chart' . $num);

        $r = hexdec(substr($hash, 0, 2));
        $g = hexdec(substr($hash, 2, 2));
        $b = hexdec(substr($hash, 4, 2));

        return array(
            'fillColor' => 'rgba(' . $r . ',' . $g . ',' . $b . ',0.2)',
            'strokeColor' => 'rgba(' . $r . ',' . $g . ',' .$b . ',1)',
            'pointColor' => 'rgba(' . $r . ',' .$g .',' . $b . ',1)',
            'pointStrokeColor' => '#fff',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(' .$r . ',' . $g .','. $b . ',1)'
        );
    }

    /**
     * Get standart setting values list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('admin', 'online_user_statistics'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}