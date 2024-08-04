<?php
/**
 * FRM Suggest Friend widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMSUGGESTFRIEND_CMP_UserIisSuggestFriendWidget extends BASE_CLASS_Widget
{

    /**
     * FRMSUGGESTFRIEND_CMP_UserIisSuggestFriendWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $currentUserId = OW::getUser()->getId();
        $secondLevelFriendsOfFriendsId = FRMSUGGESTFRIEND_CLASS_Suggest::getInstance()->getSuggestedFriends($currentUserId);

        if (sizeof($secondLevelFriendsOfFriendsId) == 0) {
            $this->assign('empty_list', true);
        } else {
            $this->addComponent('userList', new BASE_CMP_AvatarUserList($secondLevelFriendsOfFriendsId));
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmsuggestfriend', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}