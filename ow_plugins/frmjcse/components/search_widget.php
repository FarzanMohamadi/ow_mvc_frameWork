<?php
/**
 * Created by PhpStorm.
 * User: Ali Khatami
 * Date: 11/26/2018
 * Time: 12:38 PM
 */

class FRMJCSE_CMP_SearchWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $searchUrl = OW::getRouter()->urlForRoute('frmjcse.searchEmpty');
        $this->assign('searchUrl',$searchUrl);
    }
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        $language = OW::getLanguage();
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_TITLE => $language->text('frmjcse', 'search_widget_title')
        );
    }
}