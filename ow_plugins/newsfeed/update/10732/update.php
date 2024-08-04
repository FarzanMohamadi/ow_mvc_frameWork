<?php
$config = OW::getConfig();
if (!$config->configExists('newsfeed', 'disableNewsfeedFromUserProfile')) {
    $config->addConfig('newsfeed', 'disableNewsfeedFromUserProfile', 0);
}
