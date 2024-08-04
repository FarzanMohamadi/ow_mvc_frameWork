<?php

$widget = BOL_ComponentAdminService::getInstance()->addWidget('FRMJCSE_CMP_InfoWidget', false);
$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace($widget, BOL_ComponentAdminService::PLACE_INDEX);
BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_BOTTOM );
