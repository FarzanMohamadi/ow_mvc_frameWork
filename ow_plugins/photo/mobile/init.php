<?php
/**
 * Mobile init
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.mobile
 * @since 1.6.0
 */
OW::getRouter()->addRoute(new OW_Route('photo_user_albums', 'photo/useralbums/:user', 'PHOTO_MCTRL_Photo', 'albums'));
OW::getRouter()->addRoute(new OW_Route('photo_user_album', 'photo/useralbum/:user/:album', 'PHOTO_MCTRL_Photo', 'album'));
OW::getRouter()->addRoute(new OW_Route('photo_album_edit', 'photo/edit/album/:album', 'PHOTO_MCTRL_Photo', 'editAlbum'));
OW::getRouter()->addRoute(new OW_Route('photo.update_album', 'photo/update-album', 'PHOTO_MCTRL_Photo', 'updateAlbum'));
OW::getRouter()->addRoute(new OW_Route('photo_list_index', 'photo', 'PHOTO_MCTRL_Photo', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('view_photo_list', 'photo/viewlist/:listType', 'PHOTO_MCTRL_Photo', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('photo_upload', 'photo/upload', 'PHOTO_MCTRL_Upload', 'photos'));
OW::getRouter()->addRoute(new OW_Route('photo_upload_album', 'photo/upload/:album', 'PHOTO_MCTRL_Upload', 'photos'));
OW::getRouter()->addRoute(new OW_Route('view_photo', 'photo/view/:id', 'PHOTO_MCTRL_Photo', 'view'));
OW::getRouter()->addRoute(new OW_Route('delete_photo', 'photo/delete/:entityId', 'PHOTO_MCTRL_Photo', 'deletePhoto'));
OW::getRouter()->addRoute(new OW_Route('view_photo_type', 'photo/view/:id/:listType', 'PHOTO_MCTRL_Photo', 'view', array('listType' => array('default' => 'latest'))));
PHOTO_MCLASS_EventHandler::getInstance()->init();
