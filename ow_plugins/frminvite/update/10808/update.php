<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:28 AM
 */

$config = OW::getConfig();
if (!$config->configExists('frminvite', 'invitation_view_count')) {
    $config->addConfig('frminvite', 'invitation_view_count', 15);
}