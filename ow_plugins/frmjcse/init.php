<?php
FRMJCSE_CLASS_EventHandler::getInstance()->genericInit();

OW::getRouter()->addRoute(new OW_Route('frmjcse.admin', 'admin/frmjcse/issues', "FRMJCSE_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.admin.issue', 'admin/frmjcse/issue/:issueid', "FRMJCSE_CTRL_Admin", 'issue'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.admin.issue.edit', 'admin/frmjcse/issue/edit/:issueid', "FRMJCSE_CTRL_Admin", 'editIssue'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.admin.article.edit', 'admin/frmjcse/article/edit/:articleid', "FRMJCSE_CTRL_Admin", 'editArticle'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.admin.citation', 'admin/frmjcse/citation', "FRMJCSE_CTRL_Admin", 'citationSetting'));

OW::getRouter()->addRoute(new OW_Route('frmjcse.index', 'issues', "FRMJCSE_CTRL_Journal", 'issues'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.issue', 'issue/:issueid', "FRMJCSE_CTRL_Journal", 'issue'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.article', 'article/:articleid', "FRMJCSE_CTRL_Journal", 'article'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.search', 'search/:search_text', "FRMJCSE_CTRL_Journal", 'search'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.searchEmpty', 'search', "FRMJCSE_CTRL_Journal", 'search'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.article.xml', 'article/xml/:id', "FRMJCSE_CTRL_Journal", 'xml'));
OW::getRouter()->addRoute(new OW_Route('frmjcse.issue.xml', 'issue/xml/:id', "FRMJCSE_CTRL_Journal", 'xmlIssue'));
