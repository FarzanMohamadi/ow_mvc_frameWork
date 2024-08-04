<?php
$config = OW::getConfig();
if($config->configExists('frmmainpage', 'disables'))
{
    $disables = json_decode($config->getValue('frmmainpage', 'disables'),true);
    $disables = array_unique($disables);
    $config->saveConfig('frmmainpage', 'disables', json_encode($disables));
}
