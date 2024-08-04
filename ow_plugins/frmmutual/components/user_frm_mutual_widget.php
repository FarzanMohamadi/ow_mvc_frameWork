<?php
/**
 * FRM Mutual widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
class FRMMUTUAL_CMP_UserIisMutualWidget extends BASE_CLASS_Widget
{

    /**
     * FRMMUTUAL_CMP_UserIisMutualWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
    }

    private function assignList($params)
    {
        $profileOwnerId = (int) $params->additionalParamList['entityId'];
        if(!OW::getUser()->isAuthenticated() || $profileOwnerId == OW::getUser()->getId()){
            OW::getDocument()->addStyleDeclaration('.ow_dnd_widget.profile-FRMMUTUAL_CMP_UserIisMutualWidget {display: none;}');
        }else {
                $result = FRMMUTUAL_CLASS_Mutual::getInstance()->getMutualFriends($profileOwnerId, OW::getUser()->getId());
                $friendSize = sizeof($result['mutualFriensdId']);
            if ($friendSize >= OW::getConfig()->getValue('frmmutual', 'numberOfMutualFriends')) {
                $toolbar = array(array('label' => OW::getLanguage()->text('frmmutual', 'view_all', array('number' => $friendSize)), 'href' => OW::getRouter()->urlForRoute('frmmutual.mutual.firends', array('userId' => $profileOwnerId))));
                $this->assign('toolbar', $toolbar);
            }

            if (sizeof($result['FilteredMutualFriensdId']) == 0) {
                $this->assign('empty_list', true);
            } else {
                $this->addComponent('userList', new BASE_CMP_AvatarUserList($result['FilteredMutualFriensdId']));
            }
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmmutual', 'main_menu_item'),
            self::SETTING_ICON => self::ICON_PICTURE
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}