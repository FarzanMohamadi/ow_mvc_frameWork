<?php
/**
 * Abstract statistics widget component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
abstract class ADMIN_CMP_AbstractStatisticWidget extends BASE_CLASS_Widget
{
    /**
     * Default period
     * @var string
     */
    protected $defaultPeriod;

    /**
     * Add menu
     *
     * @param string $prefix
     * @return void
     */
    protected function addMenu($prefix)
    {
        $this->addComponent('menu', new BASE_CMP_WidgetMenu(array(
            'today' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                'id' => $prefix . '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_TODAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_TODAY
            ),
            'yesterday' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY
            ),
            'last_7_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS
            ),
            'last_30_days' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS
            ),
            'last_year' => array(
                'label' => OW::getLanguage()->text('admin', 'site_statistics_last_year_period'),
                'id' => $prefix. '_menu_statistics_' . BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR,
                'active' => $this->defaultPeriod == BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR
            )
        )));
    }

    /**
     * Get widget access
     *
     * @return string
     */
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    /**
     * Get custom settings list
     *
     * @return array
     */
    public static function getSettingList()
    {
        $settingList = array();
        $settingList['defaultPeriod'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('admin', 'site_statistics_default_period'),
            'value' => BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS,
            'optionList' => array(
                BOL_SiteStatisticService::PERIOD_TYPE_TODAY => OW::getLanguage()->text('admin', 'site_statistics_today_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_YESTERDAY => OW::getLanguage()->text('admin', 'site_statistics_yesterday_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_7_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_7_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_30_DAYS => OW::getLanguage()->text('admin', 'site_statistics_last_30_days_period'),
                BOL_SiteStatisticService::PERIOD_TYPE_LAST_YEAR => OW::getLanguage()->text('admin', 'site_statistics_last_year_period')
            )
        );

        return $settingList;
    }
}