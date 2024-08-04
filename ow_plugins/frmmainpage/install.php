<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */

$config = OW::getConfig();
if(!$config->configExists('frmmainpage', 'orders'))
{
    $config->addConfig('frmmainpage', 'orders', '');
}
