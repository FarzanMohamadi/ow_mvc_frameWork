<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmaudio.add_audio', 'audio/add', 'FRMAUDIO_MCTRL_Audio', 'addAudio'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio', 'audio', 'FRMAUDIO_MCTRL_Audio', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-delete-item', 'audio/delete/:id', 'FRMAUDIO_MCTRL_Audio', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-save-temp-item', 'audio/save/temp', 'FRMAUDIO_MCTRL_Audio', 'saveTempItem'));
OW::getRouter()->addRoute(new OW_Route('frmaudio-audio-save-blob-item', 'audio/save/blob', 'FRMAUDIO_MCTRL_Audio', 'saveTempBlobItem'));

FRMAUDIO_MCLASS_EventHandler::getInstance()->init();
