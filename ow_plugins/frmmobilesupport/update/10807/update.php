<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:35 AM
 */

$config = OW::getConfig();
if (!$config->configExists('frmmobilesupport', 'disable_notification_content')) {
    $config->addConfig('frmmobilesupport', 'disable_notification_content', false);
}