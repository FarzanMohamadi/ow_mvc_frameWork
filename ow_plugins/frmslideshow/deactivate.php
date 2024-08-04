<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMSLIDESHOW_MCMP_NewsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMSLIDESHOW_MCMP_ForumWidget');

$service = FRMSLIDESHOW_BOL_Service::getInstance();
$service->deleteAllExtraWidgets();