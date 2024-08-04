<?php
OW::getRouter()->addRoute(new OW_Route('frmcompetition.index', 'competitions', 'FRMCOMPETITION_MCTRL_Competition', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.competition', 'competition/:id', 'FRMCOMPETITION_MCTRL_Competition', 'viewCompetition'));
FRMCOMPETITION_MCLASS_EventHandler::getInstance()->init();