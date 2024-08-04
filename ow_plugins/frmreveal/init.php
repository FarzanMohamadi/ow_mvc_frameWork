<?php
/**
 * FRM Reveal
 */
OW::getRouter()->addRoute(new OW_Route('frmreveal.reload', 'reveal', 'FRMREVEAL_CTRL_Reveal', 'index'));
FRMREVEAL_CLASS_EventHandler::getInstance()->init();