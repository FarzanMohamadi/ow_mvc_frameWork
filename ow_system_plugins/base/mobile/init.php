<?php
$autoloader = OW::getAutoloader();
$autoloader->addPackagePointer('MBOL', OW_PluginManager::getInstance()->getPlugin('base')->getMobileBolDir());

$router = OW::getRouter();

$router->addRoute(new OW_Route('base.not_available_in_this_context', 'not-available', 'BASE_MCTRL_BaseDocument', 'notAvailable'));
$router->addRoute(new OW_Route('base.desktop_version', 'desktop-version', 'BASE_MCTRL_BaseDocument', 'redirectToDesktop'));
$router->addRoute(new OW_Route('static_sign_in', 'sign-in', 'BASE_MCTRL_User', 'standardSignIn'));
$router->addRoute(new OW_Route('base_sign_out', 'sign-out', 'BASE_CTRL_User', 'signOut'));
$router->addRoute(new OW_Route('base_user_profile_picture', 'user/picture-url/:username', 'BASE_MCTRL_User', 'profilePicture'));
$router->addRoute(new OW_Route('base_user_profile', 'user/:username', 'BASE_MCTRL_User', 'profile'));
$router->addRoute(new OW_Route('base_about_profile', 'about/:username', 'BASE_MCTRL_User', 'about'));
$router->addRoute(new OW_Route('back_step', 'back_step', 'BASE_MCTRL_Join', 'backStep'));
$router->addRoute(new OW_Route('base_page_404', '404', 'BASE_MCTRL_BaseDocument', 'page404'));
$router->addRoute(new OW_Route('base_page_403', '403', 'BASE_MCTRL_BaseDocument', 'page403'));
$router->addRoute(new OW_Route('base_page_auth_failed', 'authorization-failed', 'BASE_MCTRL_BaseDocument', 'authorizationFailed'));
$router->addRoute(new OW_Route('base_page_splash_screen', 'splash-screen', 'BASE_MCTRL_BaseDocument', 'splashScreen'));
$router->addRoute(new OW_Route('base_email_verify', 'email-verify', 'BASE_MCTRL_EmailVerify', 'index'));
$router->addRoute(new OW_Route('base_email_verify_code_form', 'email-verify-form', 'BASE_MCTRL_EmailVerify', 'verifyForm'));
$router->addRoute(new OW_Route('base_email_verify_code_check', 'email-verify-check/:code', 'BASE_MCTRL_EmailVerify', 'verify'));
$router->addRoute(new OW_Route('base_forgot_password', 'forgot-password', 'BASE_MCTRL_User', 'forgotPassword'));
$router->addRoute(new OW_Route('base.reset_user_password', 'reset-password/:code', 'BASE_MCTRL_User', 'resetPassword'));
$router->addRoute(new OW_Route('base.reset_user_password_request', 'reset-password-request', 'BASE_MCTRL_User', 'resetPasswordRequest'));
$router->addRoute(new OW_Route('base.reset_user_password_expired_code', 'reset-password-code-expired', 'BASE_MCTRL_User', 'resetPasswordCodeExpired'));
$router->addRoute(new OW_Route('base_page_confirm', 'confirm-page', 'BASE_MCTRL_BaseDocument', 'confirmPage'));
$router->addRoute(new OW_Route('base_page_alert', 'alert-page', 'BASE_MCTRL_BaseDocument', 'alertPage'));
$router->addRoute(new OW_Route('users-blocked', 'users/blocked', 'BASE_MCTRL_UserList', 'blocked'));

$router->addRoute(new OW_Route('base_user_privacy_no_permission', 'profile/:username/no-permission', 'BASE_MCTRL_User', 'privacyMyProfileNoPermission'));

// Drag And Drop panels
$router->addRoute(new OW_Route('base_member_dashboard', 'dashboard', 'BASE_MCTRL_WidgetPanel', 'dashboard'));
$router->addRoute(new OW_Route('base_index', 'index', 'BASE_MCTRL_WidgetPanel', 'index'));
//$router->addRoute(new OW_Route('base_user_profile', 'user/:username', 'BASE_MCTRL_WidgetPanel', 'profile'));

$router->addRoute(new OW_Route('users', 'users', 'BASE_MCTRL_UserList', 'index', array('list' => array(OW_Route::PARAM_OPTION_HIDDEN_VAR => 'latest'))));
$router->addRoute(new OW_Route('base_user_lists', 'users/:list', 'BASE_MCTRL_UserList', 'index'));
$router->addRoute(new OW_Route('base_user_lists_responder', 'responder', 'BASE_MCTRL_UserList', 'responder'));

$router->addRoute(new OW_Route('base_join', 'join', 'BASE_MCTRL_Join', 'index'));

$router->addRoute(new OW_Route('base.complete_account_type', 'fill/account_type', 'BASE_MCTRL_CompleteProfile', 'fillAccountType'));
$router->addRoute(new OW_Route('base.complete_required_questions', 'fill/profile_questions', 'BASE_CTRL_MCompleteProfile', 'fillRequiredQuestions'));

$router->addRoute(new OW_Route('base.attachment.get_photo', 'getPhoto/:place/:plugin/:dir/:filepath', 'BASE_CTRL_Attachment', 'getPhoto'));

$router->addRoute(new OW_Route('users-search-by-rq-result', 'users/search-by-rq-result', 'BASE_CTRL_UserSearch', 'searchUserByRQResponder'));
$router->addRoute(new OW_Route('users-search-load-more', 'users/users-search-load-more', 'BASE_CTRL_UserSearch', 'loadMoreUsersByRQ'));

$owBasePlugin = OW::getPluginManager()->getPlugin('base');

$themeManager = OW::getThemeManager();
$baseDecorators = array('box_cap', 'box', 'button', 'paging', 'avatar_item', 'tooltip', 'box_toolbar', "floatbox", "ic" , "ipc" , "user_list_item");

foreach ( $baseDecorators as $name )
{
    $themeManager->addDecoratorPath($name, $owBasePlugin->getMobileDecoratorDir() . $name . '.html');
}

$classesToAutoload = array(
    'BASE_Members' => $owBasePlugin->getCtrlDir() . 'user_list.php',
    'BASE_MenuItem' => $owBasePlugin->getCmpDir() . 'menu.php',
    'BASE_CommentsParams' => $owBasePlugin->getCmpDir() . 'comments.php',
    'BASE_ContextAction' => $owBasePlugin->getCmpDir() . 'context_action.php'
);

OW::getAutoloader()->addClassArray($classesToAutoload);

BASE_MCLASS_ConsoleEventHandler::getInstance()->init();

$baseEventHandler = new BASE_MCLASS_EventHandler();
$baseEventHandler->init();

BASE_CLASS_InvitationEventHandler::getInstance()->genericInit();

