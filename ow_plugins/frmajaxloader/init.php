<?php
/**
 * frmajaxloader
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */
FRMAJAXLOADER_CLASS_EventHandler::getInstance()->genericInit();

OW::getRouter()->addRoute(new OW_Route('frmajaxloader.myfeed.newly', 'frmajaxloader/myfeed/newly/:lastTS',"FRMAJAXLOADER_CTRL_Load", 'load_myfeed_newly'));
OW::getRouter()->addRoute(new OW_Route('frmajaxloader.sitefeed.newly', 'frmajaxloader/sitefeed/newly/:lastTS',"FRMAJAXLOADER_CTRL_Load", 'load_sitefeed_newly'));
OW::getRouter()->addRoute(new OW_Route('frmajaxloader.userfeed.newly', 'frmajaxloader/userfeed/:userId/newly/:lastTS',"FRMAJAXLOADER_CTRL_Load", 'load_userfeed_newly'));
OW::getRouter()->addRoute(new OW_Route('frmajaxloader.groupsfeed.newly', 'frmajaxloader/groupsfeed/:groupId/newly/:lastTS',"FRMAJAXLOADER_CTRL_Load", 'load_groupsfeed_newly'));
