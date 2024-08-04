<?php
FRMSECURITYESSENTIALS_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.edit_privacy', 'frmsecurityessentials/edit-privacy', 'FRMSECURITYESSENTIALS_CTRL_Iissecurityessentials', 'editPrivacy'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.delete_activity', 'frmsecurityessentials/delete-activity/:activityId', 'FRMSECURITYESSENTIALS_MCTRL_Iissecurityessentials', 'deleteFeedItem'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.delete_user', 'user/delete/:userId', 'FRMSECURITYESSENTIALS_MCTRL_Iissecurityessentials', 'deleteUser'));