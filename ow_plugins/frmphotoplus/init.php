<?php
/**
 * frmphotoplus
 */

FRMPHOTOPLUS_CLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('frmphotoplus.ajax_upload_submit', 'frmphotoplus/ajax-upload-submit', 'FRMPHOTOPLUS_CTRL_AjaxUpload', 'ajaxSubmitPhotos'));