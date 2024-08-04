<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 8/27/2017
 * Time: 9:15 AM
 */

$config = OW::getConfig();
if ( !$config->configExists('frmftpmanager', 'host') ) {
    $config->addConfig('frmftpmanager', 'host', "localhost");
}
if ( !$config->configExists('frmftpmanager', 'username') ) {
    $config->addConfig('frmftpmanager', 'username', "");
}
if ( !$config->configExists('frmftpmanager', 'password') ) {
    $config->addConfig('frmftpmanager', 'password', "");
}
if ( !$config->configExists('frmftpmanager', 'port') ) {
    $config->addConfig('frmftpmanager', 'port', "21");
}
if ( !$config->configExists('frmftpmanager', 'ftp_enabled') ) {
    $config->addConfig('frmftpmanager', 'ftp_enabled', false);
}
