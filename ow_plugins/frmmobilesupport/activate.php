<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmmobilesupport', 'frmmobilesupport-admin');

OW::getConfig()->saveConfig('frmmobilesupport', 'access_web_service', true);