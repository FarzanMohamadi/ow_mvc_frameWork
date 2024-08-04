<?php
$config = OW::getConfig();
if ( !$config->configExists('frmsecurityessentials', 'privacySet') )
{
    $config->addConfig('frmsecurityessentials', 'privacySet', false);
}
