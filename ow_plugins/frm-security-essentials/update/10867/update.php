<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */
if ( !OW::getConfig()->configExists('frmsecurityessentials', 'disable_verify_peer')){
    OW::getConfig()->saveConfig('frmsecurityessentials', 'disable_verify_peer', false);
}
