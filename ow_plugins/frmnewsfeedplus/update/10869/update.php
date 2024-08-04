<?php
OW::getPluginManager()->addPluginSettingsRouteName('frmnewsfeedplus','frmnewsfeedplus.admin_config');

OW::getConfig()->saveConfig('frmnewsfeedplus', 'newsfeed_list_order','activity');

