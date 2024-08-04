<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.mailbox
 * @since 1.6.1
 */

//@mkdir(OW_DIR_PLUGINFILES . 'mailbox' . DS . 'attachments' . DS);
//@chmod(OW_DIR_PLUGINFILES . 'mailbox' . DS . 'attachments' . DS, 0777);

OW::getStorage()->mkdir(OW_DIR_USERFILES . 'plugins' . DS . 'mailbox' . DS . 'attachments' . DS, true);
//OW::getStorage()->chmod(OW_DIR_USERFILES . 'plugins' . DS . 'mailbox' . DS . 'attachments' . DS, 0777, true);