<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.birthdays
 * @since 1.0
 */
//add 'birthdays' widget to index page
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_BirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_INDEX
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_FriendBirthdaysWidget', false),
        BOL_ComponentAdminService::PLACE_DASHBOARD
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_RIGHT);

//--
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('BIRTHDAYS_CMP_MyBirthdayWidget', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);
