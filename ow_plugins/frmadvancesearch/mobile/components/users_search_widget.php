<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

class FRMADVANCESEARCH_MCMP_UsersSearchWidget extends BASE_CLASS_Widget
{
    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        OW::getLanguage()->addKeyForJs('base', 'more');
        $jsDir = OW::getPluginManager()->getPlugin("frmadvancesearch")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "frmadvancesearch-mobile.js");
        OW::getDocument()->addOnloadScript(';frmadvancesearch_search_users(\''.OW::getRouter()->urlForRoute('frmadvancesearch.search_users', array('type'=>'all', 'key' => '')).'\', "#frmadvancedsearch_search_users",12,false);');

        $toolbar = array('label'=>OW::getLanguage()->text('frmadvancesearch','view_all_users'),
                'href'=>OW::getRouter()->urlForRoute('frmadvancesearch.list.users', array('type'=>'all')));
        $this->assign('toolbar', $toolbar);

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmadvancesearch')->getMobileCmpViewDir() . 'users_search_widget.html');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmadvancesearch', 'search_users'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}
