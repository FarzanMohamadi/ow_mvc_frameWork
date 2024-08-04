<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:25 AM
 */

$config = OW::getConfig();
if (!$config->configExists('frmforumplus', 'mobile_forum_group_visibile')) {
    $config->addConfig('frmforumplus', 'mobile_forum_group_visibile', false);
}