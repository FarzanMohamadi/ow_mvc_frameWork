<?php
/**
 * frmmainpage
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmainpage
 * @since 1.0
 */

OW::getRouter()->addRoute(new OW_Route('frmmainpage.admin', 'frmmainpage/admin', 'FRMMAINPAGE_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmainpage.admin.ajax-save-order', 'frmmainpage/admin/ajax-save-order', 'FRMMAINPAGE_CTRL_Admin', 'ajaxSaveOrder'));