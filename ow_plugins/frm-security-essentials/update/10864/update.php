<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
if ( !OW::getConfig()->configExists('frmsecurityessentials', 'ie_message_enabled')){
    OW::getConfig()->saveConfig('frmsecurityessentials', 'ie_message_enabled', true);
}
