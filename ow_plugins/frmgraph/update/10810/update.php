<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */

try {
    $authorization = OW::getAuthorization();
    $groupName = 'frmgraph';
    $authorization->addGroup($groupName);
    $authorization->addAction($groupName, 'graphshow');
}catch (Exception $ex){}