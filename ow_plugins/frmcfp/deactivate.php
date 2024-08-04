<?php
OW::getNavigation()->deleteMenuItem('frmcfp', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('frmcfp', 'event_mobile');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMCFP_CMP_UpcomingEvents');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMCFP_CMP_ProfilePageWidget');
