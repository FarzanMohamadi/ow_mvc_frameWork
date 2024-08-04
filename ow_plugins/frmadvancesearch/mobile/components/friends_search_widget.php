<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

class FRMADVANCESEARCH_MCMP_FriendsSearchWidget extends BASE_CLASS_Widget
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
        OW::getDocument()->addOnloadScript(';frmadvancesearch_search_users(\''.OW::getRouter()->urlForRoute('frmadvancesearch.search_friends', array('key' => '')).'\', "#frmadvancedsearch_search_friends",12,false);');

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmadvancesearch')->getMobileCmpViewDir() . 'friends_search_widget.html');
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmadvancesearch', 'search_friends'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}
