<?php
/**
 * Group Brief Info Widget
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_BriefInfoWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $service = GROUPS_BOL_Service::getInstance();
        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $additionalInfo = array();
        if (isset($paramObj->additionalParamList)) {
            $additionalInfo = $paramObj->additionalParamList;
        }

        $this->addComponent('briefInfo', new GROUPS_CMP_BriefInfoContent($groupId, $additionalInfo));
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('groups', 'widget_brief_info_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function userAllowedAccess()
    {
        $cmpAdminService = BOL_ComponentAdminService::getInstance();
        $cmpUniqueName = 'group-'.GROUPS_CMP_BriefInfoWidget::class;
        $restrictView = $cmpAdminService->findSettingByComponentPlaceUniqNameAndName($cmpUniqueName,'restrict_view');

        if( isset($restrictView) && $restrictView->getValue() == 1 ){
            $accessRestriction = $cmpAdminService->findSettingByComponentPlaceUniqNameAndName($cmpUniqueName,'access_restrictions');

            if(isset($accessRestriction) && isset($accessRestriction->value) ){
                $accessRestrictionValue = $accessRestriction->getValue();

                if ( OW::getUser()->isAuthenticated() ){
                    $userRoles = BOL_AuthorizationService::getInstance()->findUserRoleList(OW::getUser()->getId());
                    foreach ( $userRoles as $role ) {
                        if (in_array($role->id, $accessRestrictionValue)) {
                            return true;
                        }
                    }
                }
                else{
                    $guestRole = BOL_AuthorizationService::getInstance()->getGuestRoleId();
                    if (in_array($guestRole, $accessRestrictionValue)) {
                        return true;
                    }
                }
            }

            return false;
        }
        return true;
    }
}