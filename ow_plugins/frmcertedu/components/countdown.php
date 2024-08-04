<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcertedu
 * @since 1.0
 */
class FRMCERTEDU_CMP_Countdown extends BASE_CLASS_Widget
{
    /***
     * FRMCERTEDU_CMP_Courses constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('frmcertedu', 'courses_countdown_title'),
            self::SETTING_WRAP_IN_BOX => true,
        );
    }


}
