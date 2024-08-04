<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus
 * @since 1.0
 */
class FRMGROUPSPLUS_CMP_PendingInvitation extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $groupId = null;
        if(isset($params->additionalParamList) && isset($params->additionalParamList['entityId'])){
            $groupId = $params->additionalParamList['entityId'];
        }

        $usersInvitedComponent = new FRMGROUPSPLUS_CMP_PendingUsers($groupId);
        $this->addComponent('usersInvitedComponent', $usersInvitedComponent);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmgroupsplus', 'pending_invitation'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}