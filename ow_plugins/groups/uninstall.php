<?php
$widgetService = BOL_ComponentAdminService::getInstance();

try
{
    $widgets = $widgetService->findPlaceComponentList(GROUPS_BOL_Service::WIDGET_PANEL_NAME);
    foreach ( $widgets as $widget )
    {
	$widgetService->deleteWidgetPlace($widget['uniqName']);
    }
}
catch ( Exception $e ) {}

BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_BriefInfoWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_UserListWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_WallWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('GROUPS_CMP_LeaveButtonWidget');

if ( OW::getConfig()->getValue('groups', 'is_forum_connected') )
{
    $event = new OW_Event('forum.delete_section', array('entity' => 'groups'));
    OW::getEventManager()->trigger($event);

    $event = new OW_Event('forum.delete_widget');
    OW::getEventManager()->trigger($event);
}

if ( OW::getConfig()->getValue('groups', 'is_frmgroupsplus_connected') )
{
    $event = new OW_Event('frmgroupsplus.delete_widget');
    OW::getEventManager()->trigger($event);
    OW::getConfig()->deleteConfig('groups', 'is_frmgroupsplus_connected');
}

if(OW::getConfig()->configExists('groups', 'is_telegram_connected'))
{
    $event = new OW_Event('frmtelegram.delete_widget');
    OW::getEventManager()->trigger($event);
    OW::getConfig()->deleteConfig('groups', 'is_telegram_connected');
}

if ( OW::getConfig()->getValue('groups', 'is_instagram_connected') )
{
    $event = new OW_Event('frminstagram.delete_widget');
    OW::getEventManager()->trigger($event);
    OW::getConfig()->deleteConfig('groups', 'is_instagram_connected');
}

$dbPrefix = OW_DB_PREFIX;

$sql =
    <<<EOT
DELETE FROM `{$dbPrefix}base_place` WHERE `name`='group';
EOT;

OW::getDbo()->query($sql);