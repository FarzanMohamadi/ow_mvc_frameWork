<?php
/**
 * FRM Graph widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMGRAPH_CMP_TopUsersWidget extends BASE_CLASS_Widget
{

    /**
     * FRMGRAPH_CMP_TopUsersWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $topUsersCmp = new FRMGRAPH_CMP_TopUsers(true, 5, false, 5);
        $this->addComponent('topUsers', $topUsersCmp);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmgraph', 'top_users_widget'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}