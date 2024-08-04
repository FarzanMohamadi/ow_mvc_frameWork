<?php

OW::getPluginManager()->addPluginSettingsRouteName('frmlike', 'frmlike.admin');
OW::getConfig()->saveConfig('frmlike', 'dislikeActivate', 0);
OW::getConfig()->saveConfig('frmlike', 'dislikePostActivate', 0);
