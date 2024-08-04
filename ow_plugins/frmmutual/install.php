<?php
/**
 * frmmutual
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmutual
 * @since 1.0
 */

$config = OW::getConfig();

if ( !$config->configExists('frmmutual', 'numberOfMutualFriends') )
{
    $config->addConfig('frmmutual', 'numberOfMutualFriends', 6);
}
