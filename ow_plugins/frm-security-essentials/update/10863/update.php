<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
if ( OW::getConfig()->configExists('frmsecurityessentials', 'disabled_home_page_action_types')){
    OW::getConfig()->deleteConfig('frmsecurityessentials', 'disabled_home_page_action_types');
}
