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

FRMGUIDEDTOUR_CLASS_EventHandler::getInstance()->init();
$router = OW::getRouter();
$router->addRoute(new OW_Route('frmguidedtour.setGuideSeenStatus', 'frmguidedtour/updateSeenStatus', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'updateGuideSeenStatus'));
$router->addRoute(new OW_Route('frmguidedtour.openGuide', 'frmguidedtour/openGuide', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'openGuide'));

$router->addRoute(new OW_Route('frmguidedtour.setSeen', 'frmguidedtour/setSeen', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'setGuideSeen'));
$router->addRoute(new OW_Route('frmguidedtour.setUnseen', 'frmguidedtour/setUnseen', "FRMGUIDEDTOUR_CTRL_GuidedTour", 'setGuideUnseen'));