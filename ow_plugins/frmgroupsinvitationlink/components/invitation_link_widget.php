<?php
/**
 * Invitation Link Widget
 *
 * @package ow_plugins.frmgroupsinvitationlink.components
 * @since 1.0
 */

class FRMGROUPSINVITATIONLINK_CMP_InvitationLinkWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        if(!FRMSecurityProvider::checkPluginActive('groups', true)){
            return null;
        }
        parent::__construct();
        $groupId = (int) $params->additionalParamList['entityId'];
        $service = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance();
        $link = $service->findGroupLatestLink($groupId);

        if($link != null){
            $hasActiveLink = true;
            $hashLink = $link->getHashLink();
            $hashLink = OW::getRouter()->urlForRoute('frmgroupsinvitationlink.join-group',array('code'=>$hashLink));
        } else{
            $hasActiveLink = false;
            $hashLink = '';
        }
        $groupDto = GROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        $canAddLink = $service->isCurrentUserCanAddLink($groupId,$groupDto);
        if($groupDto->whoCanView == GROUPS_BOL_Service::WCV_INVITE){
            $canSeeLink = $canAddLink;
        } else{
            $canSeeLink = $service->isCurrentUserCanSeeLink($groupId,$groupDto);
        }

        $generateText = OW::getLanguage()->text('frmgroupsinvitationlink', 'add_link');
        $confirmText = OW::getLanguage()->text('frmgroupsinvitationlink', 'add_link_confirm');

        $addLinkUrl = $canAddLink ? OW::getRouter()->urlForRoute('frmgroupsinvitationlink.add-link', array('id' => $groupId )) : '';
        $viewAllLinksUrl = $canAddLink ? OW::getRouter()->urlForRoute('frmgroupsinvitationlink.group-links', array('id' => $groupId )) : '';

        $this->assign("hasActiveLink", $hasActiveLink);
        $this->assign("groupId", $groupId);
        $this->assign("link", $hashLink);
        $this->assign("linkLabel", $service->linkBriefer($hashLink));
        $this->assign('view_all_links', $viewAllLinksUrl);
        $this->assign('canAddLink', $canAddLink);
        $this->assign('canSeeLink', $canSeeLink);
        $this->assign('addLinkUrl', $addLinkUrl);
        $this->assign('confirmText', $confirmText);
        $this->assign('generateText', $generateText);

        OW::getLanguage()->addKeyForJs('frmgroupsinvitationlink', 'link_generation_success_message');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgroupsinvitationlink')->getStaticJsUrl().'groups_invitation_link.js');

    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('frmgroupsinvitationlink', 'widget_link_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}
