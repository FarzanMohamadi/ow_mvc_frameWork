<?php
/**
 * frmoghat
 */

FRMOGHAT_BOL_Service::getInstance()->importingDefaultItems();
OW::getRouter()->addRoute(new OW_Route('frmoghat.get.time', 'frmoghat/get-time', 'FRMOGHAT_CTRL_Time', 'getTime'));