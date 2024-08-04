<?php
/**
 * frmevaluation
 */

OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin', 'frmevaluation/admin', 'FRMEVALUATION_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.ajax-save-categories-order', 'frmevaluation/admin/ajax-save-categories-order', 'FRMEVALUATION_CTRL_Admin', 'ajaxSaveCategoriesOrder'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.ajax-save-questions-order', 'frmevaluation/admin/ajax-save-questions-order', 'FRMEVALUATION_CTRL_Admin', 'ajaxSaveQuestionsOrder'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.edit-category', 'frmevaluation/admin/edit-category/:id', 'FRMEVALUATION_CTRL_Admin', 'editCategory'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.edit-question', 'frmevaluation/admin/edit-question/:id', 'FRMEVALUATION_CTRL_Admin', 'editQuestion'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.edit-value', 'frmevaluation/admin/edit-value/:id', 'FRMEVALUATION_CTRL_Admin', 'editValue'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.delete-category', 'frmevaluation/admin/delete-category/:id', 'FRMEVALUATION_CTRL_Admin', 'deleteCategory'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.delete-question', 'frmevaluation/admin/delete-question/:id', 'FRMEVALUATION_CTRL_Admin', 'deleteQuestion'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.delete-value', 'frmevaluation/admin/delete-value/:id', 'FRMEVALUATION_CTRL_Admin', 'deleteValue'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.questions', 'frmevaluation/admin/questions/:catId', 'FRMEVALUATION_CTRL_Admin', 'questions'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.users', 'frmevaluation/admin/users', 'FRMEVALUATION_CTRL_Admin', 'users'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.unassigned-user', 'evaluation/unassigned/:username', 'FRMEVALUATION_CTRL_Admin', 'unassignedUser'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.assign-user', 'evaluation/assign-user/:id', 'FRMEVALUATION_CTRL_Admin', 'assignUser'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.admin.lock-user', 'evaluation/lock-user/:username', 'FRMEVALUATION_CTRL_Admin', 'lockUser'));


OW::getRouter()->addRoute(new OW_Route('frmevaluation.index', 'evaluation', 'FRMEVALUATION_CTRL_Evaluation', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.results', 'evaluation/results', 'FRMEVALUATION_CTRL_Evaluation', 'results'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.results.user', 'evaluation/results/:userId', 'FRMEVALUATION_CTRL_Evaluation', 'results'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.index.user', 'evaluation/:userId', 'FRMEVALUATION_CTRL_Evaluation', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.questions', 'evaluation/questions/:catId', 'FRMEVALUATION_CTRL_Evaluation', 'questions'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.questions.user', 'evaluation/questions/:catId/:userId', 'FRMEVALUATION_CTRL_Evaluation', 'questions'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.question', 'evaluation/question/:id', 'FRMEVALUATION_CTRL_Evaluation', 'question'));
OW::getRouter()->addRoute(new OW_Route('frmevaluation.question.user', 'evaluation/question/:id/:userId', 'FRMEVALUATION_CTRL_Evaluation', 'question'));




FRMEVALUATION_CLASS_EventHandler::getInstance()->init();