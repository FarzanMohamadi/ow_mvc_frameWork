<?php
/**
 * Admin user statistics widget component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.6
 */
class ADMIN_CMP_UserStatisticWidget extends ADMIN_CMP_AbstractStatisticWidget
{
    /**
     * Class constructor
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->defaultPeriod = 'last_7_days';
        if (isset($paramObj->customParamList['defaultPeriod']))
            $this->defaultPeriod = $paramObj->customParamList['defaultPeriod'];
        else if(isset($paramObj->additionalParamList['defaultPeriod']))
            $this->defaultPeriod = $paramObj->additionalParamList['defaultPeriod'];
    }

    /**
     * On before render
     *
     * @return void
     */
    public function onBeforeRender()
    {
        // register components
        $this->addComponent('statistics', new ADMIN_CMP_UserStatistic(array(
            'defaultPeriod' => $this->defaultPeriod
        )));

        $this->addMenu('user');

        // assign view variables
        $this->assign('defaultPeriod', $this->defaultPeriod);
    }

    /**
     * Get standart setting values list
     *
     * @return array
     */
    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('admin', 'widget_user_statistics'),
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }
}