<?php
OW::getNavigation()->deleteMenuItem('video', 'video');
OW::getNavigation()->deleteMenuItem('video', 'video_mobile');

BOL_ComponentAdminService::getInstance()->deleteWidget('VIDEO_CMP_VideoListWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('VIDEO_CMP_UserVideoListWidget');
