<?php
OW::getRouter()->addRoute(new OW_Route('rss_without_parameter', 'news/rss', 'FRMRSS_CTRL_Rss', 'index'));
OW::getRouter()->addRoute(new OW_Route('rss_with_parameter', 'news/rss/:tag', 'FRMRSS_CTRL_Rss', 'index'));
OW::getRouter()->addRoute(new OW_Route('create_rss_with_tag', 'news/rss/tag', 'FRMRSS_CTRL_Rss', 'createWithTag'));
$eventHandler = FRMRSS_MCLASS_EventHandler::getInstance();
$eventHandler->init();