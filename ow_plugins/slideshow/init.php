<?php 



OW::getRouter()->addRoute(new OW_Route('slideshow.ajax-add-slide', 'slideshow/ajax/add-slide', 'SLIDESHOW_CTRL_Ajax', 'addSlide'));
OW::getRouter()->addRoute(new OW_Route('slideshow.ajax-edit-slide', 'slideshow/ajax/edit-slide', 'SLIDESHOW_CTRL_Ajax', 'editSlide'));
OW::getRouter()->addRoute(new OW_Route('slideshow.ajax-redraw-list', 'slideshow/ajax/redraw-list/:uniqName', 'SLIDESHOW_CTRL_Ajax', 'redrawList'));
OW::getRouter()->addRoute(new OW_Route('slideshow.ajax-delete-slide', 'slideshow/ajax/delete-slide', 'SLIDESHOW_CTRL_Ajax', 'deleteSlide'));
OW::getRouter()->addRoute(new OW_Route('slideshow.ajax-reorder-list', 'slideshow/ajax/reorder-list', 'SLIDESHOW_CTRL_Ajax', 'reorderList'));
OW::getRouter()->addRoute(new OW_Route('slideshow.upload-file', 'slideshow/upload-file/:uniqName', 'SLIDESHOW_CTRL_Slide', 'uploadFile'));
OW::getRouter()->addRoute(new OW_Route('slideshow.update-file', 'slideshow/update-file/:slideId', 'SLIDESHOW_CTRL_Slide', 'updateFile'));
OW::getRouter()->addRoute(new OW_Route('slideshow.uninstall', 'admin/plugins/slideshow/uninstall', 'SLIDESHOW_CTRL_Admin', 'uninstall'));

SLIDESHOW_CLASS_EventHandler::getInstance()->init();