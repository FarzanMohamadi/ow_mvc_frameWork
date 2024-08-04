<?php
/**
 * FRM Challenge
 */

FRMCHALLENGE_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmchallenge.index', 'challenges', 'FRMCHALLENGE_CTRL_Challenge', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin', 'frmchallenge/admin', 'FRMCHALLENGE_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin.section', 'frmchallenge/admin/:section', 'FRMCHALLENGE_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin.category.edit', 'frmchallenge/admin/category/edit/:catId', 'FRMCHALLENGE_CTRL_Admin', 'editCategory'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin.question.edit', 'frmchallenge/admin/question/edit/:id', 'FRMCHALLENGE_CTRL_Admin', 'editQuestion'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin.question.search', 'frmchallenge/admin/question/search', 'FRMCHALLENGE_CTRL_Admin', 'search'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.admin.answer.edit', 'frmchallenge/admin/answer/edit/:id', 'FRMCHALLENGE_CTRL_Admin', 'editAnswer'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.challenge.answer', 'challenge/action/answer/:typeId/:entityId/:questionId', 'FRMCHALLENGE_CTRL_Challenge', 'challengeAnswer'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.challenge.join', 'challenge/action/join/:typeId/:entityId', 'FRMCHALLENGE_CTRL_Challenge', 'joinChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.solitary.challenge', 'challenge/solitary/index/:solitaryId', 'FRMCHALLENGE_CTRL_Challenge', 'solitaryChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.solitary.challenge.notify', 'challenge/solitary/index/:solitaryId/:correctNotif', 'FRMCHALLENGE_CTRL_Challenge', 'solitaryChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.solitary.cancel', 'challenge/solitary/cancel/:solitaryId', 'FRMCHALLENGE_CTRL_Challenge', 'cancelSolitaryChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.add.challenge', 'challenge/add/challenge', 'FRMCHALLENGE_CTRL_Challenge', 'add'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.universal.challenge', 'challenge/universal/index/:universalId', 'FRMCHALLENGE_CTRL_Challenge', 'universalChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.universal.challenge.notify', 'challenge/universal/index/:universalId/:correctNotif', 'FRMCHALLENGE_CTRL_Challenge', 'universalChallenge'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.load_usernames', 'frmchallenge/usernames/:username', "FRMCHALLENGE_CTRL_Challenge", 'loadUsernames'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.challenge.wrong.answer', 'challenge/action/wrong_answer/:typeId/:entityId/:questionId', 'FRMCHALLENGE_CTRL_Challenge', 'finishAnswerTime'));
OW::getRouter()->addRoute(new OW_Route('frmchallenge.challenge.remove.universal', 'challenge/action/remove_universal/:id', 'FRMCHALLENGE_CTRL_Challenge', 'removeUniversal'));
