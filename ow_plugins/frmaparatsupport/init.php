<?php
FRMAPARATSUPPORT_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmaparatsupport.load', 'frmaparatsupport/load/:vid',"FRMAPARATSUPPORT_CTRL_Load", 'get_aparat_info'));
