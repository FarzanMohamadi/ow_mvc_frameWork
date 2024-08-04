<?php

OW::getPluginManager()->addPluginSettingsRouteName('frmlike', 'frmlike.admin');
OW::getConfig()->saveConfig('frmlike', 'dislikeActivate', 0);