<?php
$moduleName = OW::getPluginManager()->getPlugin('mailbox')->getModuleName();

OW::getStorage()->removeFile( OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'mailbox.js', true );
OW::getStorage()->copyFile( OW_DIR_PLUGIN . $moduleName . DS . 'static' .DS . 'js' . DS . 'mailbox.js' , OW_DIR_STATIC_PLUGIN . $moduleName . DS . 'js' . DS . 'mailbox.js', true);

