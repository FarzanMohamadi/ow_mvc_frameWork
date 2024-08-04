<?php
OW::getRouter()->addRoute(new OW_Route('frmterms.index', 'terms', 'FRMTERMS_MCTRL_Terms', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.index.section-id', 'terms/:sectionId', 'FRMTERMS_MCTRL_Terms', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmterms.old.index', 'frmterms', 'FRMTERMS_MCTRL_Terms', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmterms.old.index.section-id', 'frmterms/:sectionId', 'FRMTERMS_MCTRL_Terms', 'index'));

FRMTERMS_MCLASS_EventHandler::getInstance()->genericInit();