<?php
try
{
    $widgetService = Updater::getWidgetService();
    $widgetService->deleteWidget('GROUPS_CMP_InviteWidget');
    $widgetService->deleteWidget('GROUPS_CMP_LeaveButtonWidget');
}
catch ( Exception $e )
{}
