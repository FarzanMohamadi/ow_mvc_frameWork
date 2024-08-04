<?php
/**
 * FRM Advance Search
 */

FRMADVANCESEARCH_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.search', 'search/all', 'FRMADVANCESEARCH_CTRL_Search', 'searchAll'));

OW::getRouter()->addRoute(new OW_Route('frmadvancesearch.admin', 'frmadvancesearch/admin', "FRMADVANCESEARCH_CTRL_Admin", 'index'));
