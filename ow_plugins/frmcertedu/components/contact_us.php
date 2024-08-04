<?php
/**
 * Created by PhpStorm.
 * User: MHeshmati
 * Date: 2/3/2019
 * Time: 6:20 PM
 */

class FRMCERTEDU_CMP_ContactUs extends BASE_CLASS_Widget
{
    /***
     * FRMCERTEDU_CMP_ContactUs constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('frmcertedu', 'contact_us_widget_title'),
            self::SETTING_WRAP_IN_BOX => false,
        );
    }
}