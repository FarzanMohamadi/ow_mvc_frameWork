<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcertedu
 * @since 1.0
 */
class FRMCERTEDU_CMP_Faq extends BASE_CLASS_Widget
{
    /***
     * FRMCERTEDU_CMP_News constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('frmcertedu', 'faq_widget_title'),
            self::SETTING_WRAP_IN_BOX => false,
        );
    }
}

