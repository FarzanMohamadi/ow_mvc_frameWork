<?php
OW::getNavigation()->deleteMenuItem('photo', 'photo');
OW::getNavigation()->deleteMenuItem('photo', 'mobile_photo');

BOL_ComponentAdminService::getInstance()->deleteWidget('PHOTO_CMP_PhotoListWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('PHOTO_CMP_UserPhotoAlbumsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('PHOTO_MCMP_PhotoListWidget');
