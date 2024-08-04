<?php
$config = OW::getConfig();

if ($config->configExists('newsfeed', 'disableLikeComments')) {
    $value = $config->getValues('newsfeed', 'disableLikeComments');
    $config->deleteConfig('newsfeed', 'disableLikeComments');
    $config->saveConfig('newsfeed', 'disableComments', $value);
}


$config->saveConfig('newsfeed', 'disableLikes', 0);