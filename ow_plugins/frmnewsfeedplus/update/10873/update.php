<?php
$config = OW::getConfig();

if ($config->configExists('frmnewsfeedplus', 'allow_sort'))
{
    $config->saveConfig('frmnewsfeedplus', 'allow_sort','1');
}
else{
    $config->addConfig('frmnewsfeedplus', 'allow_sort', '1','Allow sort feeds');
}
