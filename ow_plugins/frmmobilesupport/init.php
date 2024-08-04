<?php
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin', 'admin/frmmobilesupport/settings', "FRMMOBILESUPPORT_CTRL_Admin", 'settings'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-versions', 'admin/frmmobilesupport/versions', "FRMMOBILESUPPORT_CTRL_Admin", 'versions'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-android-versions', 'admin/frmmobilesupport/android-versions', "FRMMOBILESUPPORT_CTRL_Admin", 'androidVersions'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-ios-versions', 'admin/frmmobilesupport/ios-versions', "FRMMOBILESUPPORT_CTRL_Admin", 'iosVersions'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-android-native-versions', 'admin/frmmobilesupport/android-native-versions', "FRMMOBILESUPPORT_CTRL_Admin", 'androidNativeVersions'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-download-show', 'admin/frmmobilesupport/download-show', "FRMMOBILESUPPORT_CTRL_Admin", 'downloadShow'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-delete-value', 'admin/frmmobilesupport/delete-version/:id', "FRMMOBILESUPPORT_CTRL_Admin", 'deleteVersion'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-web-settings', 'admin/frmmobilesupport/web-settings', "FRMMOBILESUPPORT_CTRL_Admin", 'webSettings'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-deprecate-value', 'admin/frmmobilesupport/deprecate-version/:id', "FRMMOBILESUPPORT_CTRL_Admin", 'deprecateVersion'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-admin-approve-value', 'admin/frmmobilesupport/approve-version/:id', "FRMMOBILESUPPORT_CTRL_Admin", 'approveVersion'));

OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-index', 'mobile/service/:key', "FRMMOBILESUPPORT_MCTRL_Service", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-use-mobile', 'mobile/use_mobile_only', "FRMMOBILESUPPORT_CTRL_Service", 'useMobile'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-get-information', 'mobile/services/information/:type', "FRMMOBILESUPPORT_MCTRL_Service", 'getInformation'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-get-information-without-type', 'mobile/services/information', "FRMMOBILESUPPORT_MCTRL_Service", 'getInformation'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-action', 'mobile/services/action/:type', "FRMMOBILESUPPORT_MCTRL_Service", 'action'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-action-without-type', 'mobile/services/action', "FRMMOBILESUPPORT_MCTRL_Service", 'action'));

OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-set-token', 'setWebToken', 'FRMMOBILESUPPORT_CTRL_Service', 'setWebToken'));

OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-latest-version', 'mobile-app/latest/:type', "FRMMOBILESUPPORT_CTRL_Service", 'downloadLatestVersion'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-latest-version-short', 'app/:type', "FRMMOBILESUPPORT_CTRL_Service", 'downloadLatestVersion'));

FRMMOBILESUPPORT_CLASS_EventHandler::getInstance()->init();