<?php
/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */

class EVENT_CMP_InviteUserListSelect extends BASE_CMP_AvatarUserListSelect
{
    public function __construct( $eventId )
    {
        $idList = EVENT_BOL_EventService::getInstance()->findUserListForInvite((int)$eventId);
        $this->setTemplate( OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'avatar_user_list_select.html' );
        
        parent::__construct($idList);
    }
}
