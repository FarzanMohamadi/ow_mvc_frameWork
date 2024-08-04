<?php

$config = OW::getConfig();
if (!$config->configExists('frmcontrolkids', 'kidsAge') )
{
    $config->saveConfig('frmcontrolkids', 'kidsAge', 13);
}