<?php
/**
 * Cover Photo
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.coverphoto
 * @since 1.0
 */

try {
 BOL_ComponentAdminService::getInstance()->deleteWidget('COVERPHOTO_CMP_CoverPhotoWidget');
} catch (Exception $ex){}