<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:30 AM
 */

$config = OW::getConfig();
if ( !$config->configExists('frmmassmailing', 'mail_view_count') )
{
    $config->addConfig('frmmassmailing', 'mail_view_count',15);
}