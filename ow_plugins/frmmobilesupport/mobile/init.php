<?php
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-index', 'mobile/service/:key', "FRMMOBILESUPPORT_MCTRL_Service", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-use-mobile', 'mobile/use_mobile_only', "FRMMOBILESUPPORT_MCTRL_Service", 'useMobile'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-get-information', 'mobile/services/information/:type', "FRMMOBILESUPPORT_MCTRL_Service", 'getInformation'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-get-information-without-type', 'mobile/services/information', "FRMMOBILESUPPORT_MCTRL_Service", 'getInformation'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-action', 'mobile/services/action/:type', "FRMMOBILESUPPORT_MCTRL_Service", 'action'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-web-service-action-without-type', 'mobile/services/action', "FRMMOBILESUPPORT_MCTRL_Service", 'action'));

OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-latest-version', 'mobile-app/latest/:type', "FRMMOBILESUPPORT_CTRL_Service", 'downloadLatestVersion'));
OW::getRouter()->addRoute(new OW_Route('frmmobilesupport-latest-version-short', 'app/:type', "FRMMOBILESUPPORT_CTRL_Service", 'downloadLatestVersion'));
FRMMOBILESUPPORT_MCLASS_EventHandler::getInstance()->init();