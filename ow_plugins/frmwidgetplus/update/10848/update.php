<?php
$config = OW::getConfig();

if ( !$config->configExists('frmwidgetplus', 'add_select2') )
{
    $config->addConfig('frmwidgetplus', 'add_select2', 0, 'Make select boxes searchable');
}
