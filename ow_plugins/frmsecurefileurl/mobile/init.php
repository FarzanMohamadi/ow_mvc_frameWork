<?php
OW::getRouter()->addRoute(new OW_Route('frmsecurefileurl.process_file', 'secure/files/:hash', 'FRMSECUREFILEURL_CTRL_Url', 'index'));
FRMSECUREFILEURL_CLASS_EventHandler::getInstance()->init();