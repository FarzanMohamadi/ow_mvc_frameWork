<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmwidgetplus', 'frmwidgetplus_admin_setting');
OW::getConfig()->saveConfig("frmwidgetplus", "displayRateWidget", 1);