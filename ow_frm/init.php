<?php
/**
 * User: Hamed Tahmooresi
 * Date: 1/5/2016
 * Time: 3:31 PM
 */
require_once 'event' . DS . 'manager.php';

$frmSecurityProvider = FRMSecurityProvider::getInstance();


OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_INSTALL, array($frmSecurityProvider, 'createBackupTables'));
OW::getEventManager()->bind(FRMEventManager::ON_DATA_BACKUP_DELETE, array($frmSecurityProvider, 'deleteBackupData'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_SQL_IMPORT_IN_INSTALLING, array($frmSecurityProvider, 'createBackupTables'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_SQL_IMPORT_IN_INSTALLING, array($frmSecurityProvider, 'FixRobots'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_INSTALLATION_COMPLETED, array($frmSecurityProvider, 'installComplete'));
OW::getEventManager()->bind('plugin.privacy.on_change_action_privacy', array($frmSecurityProvider, 'onAfterPrivacyChanged'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_INSTALL_EXTENSIONS_CHECK, array($frmSecurityProvider, 'checkDiff'));
OW::getEventManager()->bind('base.mail_service.send.check_mail_state', array($frmSecurityProvider, 'onBeforeEmailSend'));
OW::getEventManager()->bind(FRMEventManager::ON_ALBUM_DEFAULT_COVER_SET, array($frmSecurityProvider, 'setAlbumCoverDefault'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_ERROR_RENDER, array($frmSecurityProvider, 'onBeforeErrorRender'));
OW::getEventManager()->bind(FRMEventManager::BEFORE_ALTER_QUERY_EXECUTED, array($frmSecurityProvider, 'onBeforeAlterQueryExecuted'));
OW::getEventManager()->bind(FRMEventManager::AFTER_QUERY_EXECUTED, array($frmSecurityProvider, 'onAfterQueryExecuted'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_ACTIONS_LIST_RETURN, array($frmSecurityProvider, 'onBeforeActionsListReturn'));
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_PLUGIN_UNINSTALL, array($frmSecurityProvider, 'onAfterPluginUnistall'));
OW::getEventManager()->bind(FRMEventManager::CHECK_MASTER_PAGE_BLANK_HTML_FOR_UPLOAD_IMAGE_FORM, array($frmSecurityProvider, 'checkMasterPageBlankHtml'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_CURRENCY_FIELD_APPEAR, array($frmSecurityProvider, 'decideToShowCurrencySetting'));
OW::getEventManager()->bind(FRMEventManager::CORRECT_MULTIPLE_LANGUAGE_SENTENCE_ALIGNMENT, array($frmSecurityProvider, 'multipleLanguageSentenceAlignmentCorrection'));
OW::getEventManager()->bind(FRMEventManager::ON_VALIDATE_HTML_CONTENT, array($frmSecurityProvider, 'validateHtmlContent'));
OW::getEventManager()->bind(FRMEventManager::BEFORE_ALLOW_CUSTOMIZATION_CHANGED, array($frmSecurityProvider, 'beforeAllowCustomizationChanged'));
OW::getEventManager()->bind(FRMEventManager::BEFORE_CUSTOMIZATION_PAGE_RENDERER, array($frmSecurityProvider, 'beforeCustomizationPageRenderer'));
OW::getEventManager()->bind(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array($frmSecurityProvider, 'partialHalfSpaceCodeCorrection'));
OW::getEventManager()->bind(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array($frmSecurityProvider, 'partialSpaceCodeCorrection'));
OW::getEventManager()->bind(FRMEventManager::HTML_ENTITY_CORRECTION, array($frmSecurityProvider, 'htmlEntityCorrection'));
OW::getEventManager()->bind(FRMEventManager::DISTINGUISH_REQUIRED_FIELD, array($frmSecurityProvider, 'setDistinguishForRequiredField'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array($frmSecurityProvider, 'onAfterNewsFeedStatusStringRead'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_NEWSFEED_STATUS_STRING_WRITE, array($frmSecurityProvider, 'onBeforeNewsFeedStatusStringWrite'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_NOTIFICATION_STRING_READ, array($frmSecurityProvider, 'onAfterNotificationDataRead'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_NOTIFICATION_STRING_WRITE, array($frmSecurityProvider, 'onBeforeNotificationDataWrite'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_GET_TPL_DATA , array($frmSecurityProvider, 'onAfterGetTplData'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_FORUM_SECTIONS_RETURN , array($frmSecurityProvider, 'onBeforeForumSectionsReturn'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_FORUM_ADVANCE_SEARCH_QUERY_EXECUTE , array($frmSecurityProvider, 'onBeforeForumAdvanceSearchQueryExecute'));
OW::getEventManager()->bind(FRMEventManager::IS_MOBILE_VERSION , array($frmSecurityProvider, 'isMobileVersion'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN , array($frmSecurityProvider, 'checkPhotoExtension'));
OW::getEventManager()->bind(FRMEventManager::VALIDATE_UPLOADED_FILE_NAME, array($frmSecurityProvider, 'validateUploadedFileName'));
OW::getEventManager()->bind(OW_EventManager::ON_USER_REGISTER, array($frmSecurityProvider, 'setDefaultTimeZoneForUser'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_URL_IMAGE_ADD_ON_CHECK_LINK, array($frmSecurityProvider, 'checkImageExtenstionForAddAsImagesOfUrl'));
OW::getEventManager()->bind(FRMEventManager::ENABLE_DESKTOP_OFFLINE_CHAT, array($frmSecurityProvider, 'enableDesktopOfflineChat'));
OW::getEventManager()->bind(FRMEventManager::USER_LIST_FRIENDSHIP_STATUS, array($frmSecurityProvider, 'userListFriendshipStatus'));
OW::getEventManager()->bind(FRMEventManager::BEFORE_CHECK_URI_REQUEST, array($frmSecurityProvider, 'beforeCheckUriRequest'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_AUTOLOGIN_COOKIE_UPDATE, array($frmSecurityProvider, 'autoLoginCookieUpdate'));
OW::getEventManager()->bind(OW_EventManager::ON_AFTER_REQUEST_HANDLE, array($frmSecurityProvider, 'onAfterRouteCheckRequest'));
OW::getEventManager()->bind(FRMEventManager::CHECK_OWNER_OF_ACTION_ID, array($frmSecurityProvider, 'checkOwnerOfActionId'));
OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($frmSecurityProvider, 'addMediaElementPlayerAfterRender'));
OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_CONSOLE_ITEM_RENDER, array($frmSecurityProvider, 'editConsoleItemContent'));
OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($frmSecurityProvider, "onRabbitMQLogRelease"));
OW::getEventManager()->bind('base.mail_service.send.check_mail_state', array($frmSecurityProvider, 'checkRecipientsSuspended'));
OW::getEventManager()->bind('base.on_socket_message_received', array($frmSecurityProvider, 'checkReceivedMessage'));
OW::getEventManager()->bind('base.send_data_using_socket', array($frmSecurityProvider, 'sendDataUsingSocket'));
OW::getEventManager()->bind('socket.all_user_socket_closed', array($frmSecurityProvider, 'allUserSocketClosed'));
OW::getEventManager()->bind('socket.user_socket_created', array($frmSecurityProvider, 'firstUserSocketOpen'));
OW::getEventManager()->bind('base.after_save_online_user', array($frmSecurityProvider, 'firstUserSocketOpen'));


FRMSecurityProvider::updateDefaultTheme();

//require_once OW_DIR_ROOT.'ow_system_plugins'.DS.'base'.DS.'classes'.DS.'cache_backend_mysql.php';
//OW::getCacheManager()->setCacheEnabled(true);
//OW::getCacheManager()->setCacheBackend(new BASE_CLASS_CacheBackendMysql(OW::getDbo()));
