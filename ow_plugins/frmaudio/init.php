<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmaudio.add_audio', 'audio/add', 'FRMAUDIO_CTRL_Audio', 'addAudio'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio', 'audio', 'FRMAUDIO_CTRL_Audio', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-delete-item', 'audio/delete/:id', 'FRMAUDIO_CTRL_Audio', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-save-temp-item', 'audio/save/temp', 'FRMAUDIO_CTRL_Audio', 'saveTempItem'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-save-blob-item', 'audio/save/blob', 'FRMAUDIO_CTRL_Audio', 'saveTempBlobItem'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-admin', 'admin/frmaudio/settings', "FRMAUDIO_CTRL_Admin", 'settings'));

FRMAUDIO_CLASS_EventHandler::getInstance()->init();
