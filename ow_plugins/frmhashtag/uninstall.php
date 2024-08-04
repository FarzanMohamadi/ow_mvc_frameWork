<?php
/**
 * frmhashtag
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmhashtag';
    $authorization->deleteAction($groupName, 'view_newsfeed');
}catch (Exception $e){}