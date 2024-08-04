<?php
/**
 * frmhashtag
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
FRMHASHTAG_MCLASS_EventHandler::getInstance()->genericInit();

OW::getRouter()->addRoute(new OW_Route('frmhashtag.load_tags', 'frmhashtag/tags', "FRMHASHTAG_CTRL_Load", 'loadTags'));
OW::getRouter()->addRoute(new OW_Route('frmhashtag.load_tags_filled', 'frmhashtag/tags/:tag', "FRMHASHTAG_CTRL_Load", 'loadTags'));

OW::getRouter()->addRoute(new OW_Route('frmhashtag.page', 'hashtag', "FRMHASHTAG_MCTRL_Load", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmhashtag.tag', 'hashtag/:tag', "FRMHASHTAG_MCTRL_Load", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmhashtag.tag.tab', 'hashtag/:tag/:tab', "FRMHASHTAG_MCTRL_Load", 'index'));