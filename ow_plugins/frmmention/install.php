<?php
/**
 * frmmention
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */

if ( !OW::getConfig()->configExists('frmmention', 'max_count') )
{
    OW::getConfig()->saveConfig('frmmention', 'max_count', 5, 'Mention Max Count');
}
