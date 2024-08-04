<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmquestionroles
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmquestionroles.index', 'admin/users/moderators-questions', "FRMQUESTIONROLES_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmquestionroles.delete', 'delete_question_roles/:id', "FRMQUESTIONROLES_CTRL_Admin", 'delete'));
OW::getRouter()->addRoute(new OW_Route('frmquestionroles.user_disapproved', 'disapproved/users', "FRMQUESTIONROLES_CTRL_QuestionRoles", 'disapprovedUsers'));
FRMQUESTIONROLES_CLASS_EventHandler::getInstance()->init();

