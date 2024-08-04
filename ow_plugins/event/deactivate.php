<?php
OW::getNavigation()->deleteMenuItem('event', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('event', 'event_mobile');
BOL_ComponentAdminService::getInstance()->deleteWidget('EVENT_CMP_EventUsers');
BOL_ComponentAdminService::getInstance()->deleteWidget('EVENT_CMP_UpcomingEvents');
BOL_ComponentAdminService::getInstance()->deleteWidget('EVENT_CMP_ProfilePageWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('EVENT_CMP_EventDetails');