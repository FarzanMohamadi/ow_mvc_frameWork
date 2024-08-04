<?php
/**
 * frmgroupsinvitationlink
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsinvitationlink.controllers
 * @since 1.0
 */
class FRMGROUPSINVITATIONLINK_MCTRL_Link extends OW_MobileActionController
{
    /**
     *
     * @var FRMGROUPSINVITATIONLINK_BOL_Service
     */
    private $service;
    private $linkDao;

    public function __construct()
    {
        $this->service = FRMGROUPSINVITATIONLINK_BOL_Service::getInstance();
        $this->linkDao = FRMGROUPSINVITATIONLINK_BOL_LinkDao::getInstance();
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgroupsinvitationlink')->getStaticJsUrl().'groups_invitation_link.js');

    }

    public function joinLink($params){
        if(!isset($params) || !isset($params['code'])){
            throw new Redirect404Exception();
        }
        $group = $this->service->findGroupByInvitationLink($params['code']);

        if(!isset($group)) {
            throw new Redirect404Exception();
        }
        $groupId = $group->getId();
        $this->service->registerUserInGroupLink($params['code']);
        $this->redirect(OW::getRouter()->urlForRoute('groups-view',array('groupId' => $groupId)));
    }

}
