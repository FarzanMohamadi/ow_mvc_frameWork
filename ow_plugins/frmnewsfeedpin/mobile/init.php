<?php
FRMNEWSFEEDPIN_MCLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmnewsfeedpin.add_pin_by_entity', 'frmnewsfeedpin/add_pin_entity', 'FRMNEWSFEEDPIN_MCTRL_Pin', 'addPinByEntity'));
OW::getRouter()->addRoute(new OW_Route('frmnewsfeedpin.pin_delete', 'frmnewsfeedpin/delete', 'FRMNEWSFEEDPIN_MCTRL_Pin', 'deletePin'));
