<?php
/**
 * FRM CERTEDU
 */

FRMCERT_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmcert.index', 'certStatus', "FRMCERT_CTRL_Information", 'index'));