<?php
FRMNEWSFEEDPLUS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmnewsfeedplus.edit.post', 'frmnewsfeedplus/edit_post', 'FRMNEWSFEEDPLUS_CTRL_Edit', 'edit'));
OW::getRouter()->addRoute(new OW_Route('frmnewsfeedplus.admin_config', 'frmnewsfeedplus/admin', 'FRMNEWSFEEDPLUS_CTRL_Admin', 'index'));