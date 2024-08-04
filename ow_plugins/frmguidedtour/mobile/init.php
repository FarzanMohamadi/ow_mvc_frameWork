<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmguidedtour
 * @since 1.0
 */

$eventHandler = FRMGUIDEDTOUR_MCLASS_EventHandler::getInstance();
$eventHandler->init();
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmguidedtour.setSeen', 'frmguidedtour/setSeen', "FRMGUIDEDTOUR_MCTRL_GuidedTour", 'setGuideSeen'));
$router->addRoute(new OW_Route('frmguidedtour.setUnseen', 'frmguidedtour/setUnseen', "FRMGUIDEDTOUR_MCTRL_GuidedTour", 'setGuideUnseen'));

$router->addRoute(new OW_Route('frmguidedtour.showGuideMobile', 'frmguidedtour/showGuideMobile', "FRMGUIDEDTOUR_MCTRL_GuidedTour", 'showGuide'));

$router->addRoute(new OW_Route('frmguidedtour.setGuideSeenStatus', 'frmguidedtour/updateSeenStatus', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'updateGuideSeenStatus'));
$router->addRoute(new OW_Route('frmguidedtour.openGuide', 'frmguidedtour/openGuide', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'openGuide'));