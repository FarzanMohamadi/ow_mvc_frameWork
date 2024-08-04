<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:22 AM
 */

$config = OW::getConfig();
if (!$config->configExists('frmcontactus', 'adminComment')) {
    $config->addConfig('frmcontactus', 'adminComment', '');
}