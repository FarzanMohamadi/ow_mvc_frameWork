<?php
$eventHandler = new FRMPUBLISHFORUMTOPIC_CLASS_EventHandler();
$eventHandler->init();

OW::getRouter()->addRoute(new OW_Route('frmpublishforumtopic.admin', 'frmpublishforumtopic/admin', 'FRMPUBLISHFORUMTOPIC_CTRL_Admin', 'index'));