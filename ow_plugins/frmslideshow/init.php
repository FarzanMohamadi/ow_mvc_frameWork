<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin', 'frmslideshow/admin', 'FRMSLIDESHOW_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.section-id', 'frmslideshow/admin/:sectionId', 'FRMSLIDESHOW_CTRL_Admin', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.edit-album', 'frmslideshow/admin/edit-album/:id', 'FRMSLIDESHOW_CTRL_Admin', 'editAlbum'));
OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.delete-album', 'frmslideshow/admin/delete-album/:id', 'FRMSLIDESHOW_CTRL_Admin', 'deleteAlbum'));

OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.slides', 'frmslideshow/admin/slides/:albumId', 'FRMSLIDESHOW_CTRL_Admin', 'slides'));
OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.edit-slide', 'frmslideshow/admin/edit-slide/:id', 'FRMSLIDESHOW_CTRL_Admin', 'editSlide'));
OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.delete-slide', 'frmslideshow/admin/delete-slide/:id', 'FRMSLIDESHOW_CTRL_Admin', 'deleteSlide'));


OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.ajax-save-albums-order', 'frmslideshow/admin/ajax-save-albums-order', 'FRMSLIDESHOW_CTRL_Admin', 'ajaxSaveAlbumsOrder'));
OW::getRouter()->addRoute(new OW_Route('frmslideshow.admin.ajax-save-slides-order', 'frmslideshow/admin/ajax-save-slides-order', 'FRMSLIDESHOW_CTRL_Admin', 'ajaxSaveSlidesOrder'));


FRMSLIDESHOW_CLASS_EventHandler::getInstance()->init();
