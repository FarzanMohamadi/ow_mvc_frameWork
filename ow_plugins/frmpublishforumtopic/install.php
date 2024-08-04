<?php
$config = OW::getConfig();
if(!$config->configExists('frmpublishforumtopic', 'publish_destination'))
{
    $config->addConfig('frmpublishforumtopic', 'publish_destination','blog');
}