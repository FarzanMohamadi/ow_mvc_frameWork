<?php
FRMPROFILEMANAGEMENT_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.edit', 'profile/edit', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.edit.changepassword', 'profile/changepassword', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'changePassword'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.changepassword', 'profile/edit/:changepassword', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.old.edit', 'frmprofilemanagement/edit', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.old.edit.changepassword', 'frmprofilemanagement/changepassword', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'changePassword'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.old.changepassword', 'frmprofilemanagement/edit/:changepassword', 'FRMPROFILEMANAGEMENT_MCTRL_Edit', 'index'));

$eventHandler = FRMPROFILEMANAGEMENT_MCLASS_EventHandler::getInstance();
OW::getEventManager()->bind(FRMEventManager::ON_MOBILE_ADD_ITEM, array($eventHandler, 'onMobileAddItem'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.preference_index', 'frmprofilemanagement/preference', 'FRMPROFILEMANAGEMENT_MCTRL_Preference', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmprofilemanagement.delete_user', 'profile/delete', 'FRMPROFILEMANAGEMENT_MCTRL_DeleteUser', 'index'));

OW::getRouter()->addRoute(new OW_Route('base_user_profile_redirection', 'userProfile', 'BASE_CTRL_ComponentPanel', 'redirectToUserprofile'));