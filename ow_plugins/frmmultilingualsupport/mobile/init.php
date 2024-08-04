<?php
FRMMULTILINGUALSUPPORT_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmmultilingualsupport.select.language', 'frmmultilingualsupport/selectLanguage', "FRMMULTILINGUALSUPPORT_MCTRL_Multilingualsupport", 'index'));