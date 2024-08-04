<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:37 AM
 */

$config = OW::getConfig();
if (!$config->configExists('frmrules', 'frmrules_guidline')) {
    $config->addConfig('frmrules', 'frmrules_guidline', '');
}