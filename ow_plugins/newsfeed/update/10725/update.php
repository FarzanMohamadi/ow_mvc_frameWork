<?php
$config = OW::getConfig();
if (!$config->configExists('newsfeed', 'removeDashboardStatusForm')) {
    $config->addConfig('newsfeed', 'removeDashboardStatusForm', 0);
}
