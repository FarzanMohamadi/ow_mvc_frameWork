<?php
/**
 * frmgroupsplus
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgroupsplus
 * @since 1.0
 */

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMGROUPSPLUS_CMP_FileListWidget');
try
{
    BOL_ComponentAdminService::getInstance()->deleteWidget('FRMGROUPSPLUS_CMP_PendingInvitation');
}
catch(Exception $e)
{

}