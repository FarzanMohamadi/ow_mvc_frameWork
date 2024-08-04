<?php

OW::getConfig()->saveConfig('notifications', 'delete_days_for_viewed', 7);
OW::getConfig()->saveConfig('notifications', 'delete_days_for_not_viewed', 60);

OW::getPluginManager()->addPluginSettingsRouteName('notifications', 'notifications.admin');