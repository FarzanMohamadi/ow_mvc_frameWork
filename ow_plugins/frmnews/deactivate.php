<?php
OW::getNavigation()->deleteMenuItem('frmnews', 'main_menu_item');
OW::getNavigation()->deleteMenuItem('frmnews', 'frmnews_mobile');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMNEWS_CMP_NewsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('FRMNEWS_CMP_TagsWidget');
