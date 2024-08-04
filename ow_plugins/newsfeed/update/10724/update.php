<?php
$config = OW::getConfig();
if (!$config->configExists('newsfeed', 'showDashboardChatForm')) {
    $config->addConfig('newsfeed', 'showDashboardChatForm', 0);
}
if (!$config->configExists('newsfeed', 'showGroupChatForm')) {
    $config->addConfig('newsfeed', 'showGroupChatForm', 0);
}
