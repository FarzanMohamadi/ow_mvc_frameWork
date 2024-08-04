<?php
/**
 * FRM Graph widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMGRAPH_CMP_CountupWidget extends BASE_CLASS_Widget
{

    /**
     * FRMGRAPH_CMP_CountupWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'countUp.js', 'text/javascript', (-100));
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl() . 'countup.css');

        $infoItems = array();

        // users
        $numberOfAllUsers =  BOL_UserService::getInstance()->count(true);
        $infoItems[] = array(
            'class' => 'users',
            'count' => $numberOfAllUsers,
            'title' => OW::getLanguage()->text('frmgraph', 'number_of_users'),
        );
        OW::getDocument()->addOnloadScript('countUpProcess("statistical_info_item_count_users", ' . $numberOfAllUsers . ');');

        // groups
        if (FRMSecurityProvider::getInstance()->checkPluginActive('groups', true)) {
            $numberOfAllGroups = GROUPS_BOL_Service::getInstance()->findAllGroupCount();
            $infoItems[] = array(
                'class' => 'groups',
                'count' => $numberOfAllGroups,
                'title' => OW::getLanguage()->text('frmgraph', 'number_of_groups'),
            );
            OW::getDocument()->addOnloadScript('countUpProcess("statistical_info_item_count_groups", ' . $numberOfAllGroups . ');');
        }

        // topics
        if (FRMSecurityProvider::getInstance()->checkPluginActive('forum', true)) {
            $numberOfAllTopics = FORUM_BOL_ForumService::getInstance()->countAllTopics();
            $infoItems[] = array(
                'class' => 'topics',
                'count' => $numberOfAllTopics,
                'title' => OW::getLanguage()->text('frmgraph', 'number_of_topics'),
            );
            OW::getDocument()->addOnloadScript('countUpProcess("statistical_info_item_count_topics", ' . $numberOfAllTopics . ');');
        }

        // online users
        $numberOfAllOnlineUsers = BOL_UserService::getInstance()->countOnline();
        $infoItems[] = array(
            'class' => 'online_users',
            'count' => $numberOfAllOnlineUsers,
            'title' => OW::getLanguage()->text('frmgraph', 'number_of_online_users'),
        );
        OW::getDocument()->addOnloadScript('countUpProcess("statistical_info_item_count_online_users", ' . $numberOfAllOnlineUsers . ');');

        $this->assign('items', $infoItems);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmgraph', 'countup_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}