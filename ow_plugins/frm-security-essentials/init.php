<?php
/**
 * User: Hamed Tahmooresi
 * Date: 12/23/2015
 * Time: 11:00 AM
 */
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.admin', 'frmsecurityessentials/admin', 'FRMSECURITYESSENTIALS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.admin.currentSection', 'frmsecurityessentials/admin/:currentSection', 'FRMSECURITYESSENTIALS_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.edit_privacy', 'frmsecurityessentials/edit-privacy', 'FRMSECURITYESSENTIALS_CTRL_Iissecurityessentials', 'editPrivacy'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.delete_activity', 'frmsecurityessentials/delete-activity/:activityId', 'FRMSECURITYESSENTIALS_CTRL_Iissecurityessentials', 'deleteFeedItem'));
OW::getRouter()->addRoute(new OW_Route('frmsecurityessentials.delete_user', 'user/delete/:userId', 'FRMSECURITYESSENTIALS_CTRL_Iissecurityessentials', 'deleteUser'));

FRMSECURITYESSENTIALS_CLASS_EventHandler::getInstance()->init();