<?php
/**
 * FRM Advance Search
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmadvancesearch
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.search_users_empty', 'ajax-search-users/:type', 'FRMADVANCESEARCH_CTRL_Search', 'searchUsers'));
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.search_users', 'ajax-search-users/:type/:key', 'FRMADVANCESEARCH_CTRL_Search', 'searchUsers'));
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.search_friends', 'search/friends/:key', 'FRMADVANCESEARCH_CTRL_Search', 'searchFriends'));

OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.search_users.ctrl', 'search-users', 'FRMADVANCESEARCH_MCTRL_Container', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.list.users', 'search/users/:type', 'FRMADVANCESEARCH_MCTRL_AllUsersList', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.all_users.search', 'search/users/all/:search', 'FRMADVANCESEARCH_MCTRL_AllUsersList', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.all_users.responder', 'search/users/all-responder', 'FRMADVANCESEARCH_MCTRL_AllUsersList', 'responder'));

FRMADVANCESEARCH_CLASS_EventHandler::getInstance()->genericInit();