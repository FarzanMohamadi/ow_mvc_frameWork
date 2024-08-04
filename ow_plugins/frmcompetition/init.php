<?php
/**
 * frmcompetition
 */

OW::getRouter()->addRoute(new OW_Route('frmcompetition.admin', 'frmcompetition/admin', 'FRMCOMPETITION_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.admin.users', 'frmcompetition/admin/users/:competitionId', 'FRMCOMPETITION_CTRL_Admin', 'users'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.admin.groups', 'frmcompetition/admin/groups/:competitionId', 'FRMCOMPETITION_CTRL_Admin', 'groups'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.admin.edit.competition', 'frmcompetition/admin/edit/competition/:competitionId', 'FRMCOMPETITION_CTRL_Admin', 'editCompetition'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.admin.delete.competition', 'frmcompetition/admin/delete/competition/:competitionId', 'FRMCOMPETITION_CTRL_Admin', 'deleteCompetition'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.index', 'competitions', 'FRMCOMPETITION_CTRL_Competition', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmcompetition.competition', 'competition/:id', 'FRMCOMPETITION_CTRL_Competition', 'viewCompetition'));
FRMCOMPETITION_CLASS_EventHandler::getInstance()->init();