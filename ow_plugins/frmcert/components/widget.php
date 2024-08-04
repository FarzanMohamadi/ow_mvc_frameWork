<?php
/**
 * FRM Cert
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcert
 * @since 1.0
 */
class FRMCERT_CMP_Widget extends BASE_CLASS_Widget
{
    /***
     * FRMCERT_CMP_Widget constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $result = FRMCERT_BOL_Service::getInstance()->getResults('cert_report');
        $lastVulnDay = 1002;
        $lastVulnWeek = 1084;
        $lastVulnMonth = 10279;

        $lastBotDay = 1002;
        $lastBotWeek = 1084;
        $lastBotMonth = 10279;

        $time = time();

        if ($result != null) {
            $result = (array) json_decode($result);
            if (isset($result['vuln'])) {
                $lastVulnDay = $result['vuln']->last_day;
                $lastVulnWeek = $result['vuln']->last_week;
                $lastVulnMonth = $result['vuln']->last_month;
            }
            if (isset($result['bot'])) {
                $lastBotDay = $result['bot']->last_day;
                $lastBotWeek = $result['bot']->last_week;
                $lastBotMonth = $result['bot']->last_month;
            }
            if (isset($result['time'])){
                $time = $result['time'];
            }
        }

        //Vulnerabilities
        $this->assign('vulDayStatistics',$lastVulnDay);
        $this->assign('vulWeekStatistics',$lastVulnWeek);
        $this->assign('vulMonthStatistics',$lastVulnMonth);
        $this->assign('time',UTIL_DateTime::formatSimpleDate($time));

        //Bots
        $this->assign('botDayStatistics',$lastBotDay);
        $this->assign('botWeekStatistics',$lastBotWeek);
        $this->assign('botMonthStatistics',$lastBotMonth);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('frmcert', 'widget_title'),
            self::SETTING_WRAP_IN_BOX => true,
        );
    }


}
