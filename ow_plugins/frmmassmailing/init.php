<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmmassmailing.admin', 'frmmassmailing/admin', 'FRMMASSMAILING_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmassmailing.admin.section-id', 'frmmassmailing/admin/:sectionId', 'FRMMASSMAILING_CTRL_Admin', 'index'));

FRMMASSMAILING_CLASS_EventHandler::getInstance()->init();