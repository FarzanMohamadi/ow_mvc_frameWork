<?php
FRMNEWSFEEDPLUS_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmnewsfeedplus.edit.post', 'frmnewsfeedplus/edit_post', 'FRMNEWSFEEDPLUS_CTRL_Edit', 'edit'));