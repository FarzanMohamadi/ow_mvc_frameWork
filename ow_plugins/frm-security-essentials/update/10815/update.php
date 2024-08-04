<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:39 AM
 */

$config = OW::getConfig();
if ( !$config->configExists('frmsecurityessentials', 'disabled_home_page_action_types') )
{
    $config->addConfig('frmsecurityessentials', 'disabled_home_page_action_types', '');
}