<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmtpnetsms', 'frmtpnetsms.admin');

if (!OW::getConfig()->configExists('frmtpnetsms', 'user_id')){
    OW::getConfig()->saveConfig('frmtpnetsms', 'user_id', '0');
}

if (!OW::getConfig()->configExists('frmtpnetsms', 'password')){
    OW::getConfig()->saveConfig('frmtpnetsms', 'password', '0');
}

if (!OW::getConfig()->configExists('frmtpnetsms', 'originator')){
    OW::getConfig()->saveConfig('frmtpnetsms', 'originator', '0');
}

if (!OW::getConfig()->configExists('frmtpnetsms', 'url')){
    OW::getConfig()->saveConfig('frmtpnetsms', 'url', 'http://127.0.0.1/');
}
