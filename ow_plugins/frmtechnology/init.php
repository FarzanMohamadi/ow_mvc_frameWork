<?php
FRMTECHNOLOGY_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmtechnology.index', 'technology/technologies', 'FRMTECHNOLOGY_CTRL_Technologies', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.view-list', 'technology/technologies/:listType', 'FRMTECHNOLOGY_CTRL_Technologies', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.add', 'technology/add', "FRMTECHNOLOGY_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.edit', 'technology/edit/:technologyId', "FRMTECHNOLOGY_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.view', 'technology/:technologyId', 'FRMTECHNOLOGY_CTRL_Technologies', 'view'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.submit', 'technology/order/:technologyId', "FRMTECHNOLOGY_CTRL_Order", 'orderSubmit'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.orderIndex', 'technology/orders', "FRMTECHNOLOGY_CTRL_Order", 'orderIndex'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.orderView', 'technology/orders/:orderId', "FRMTECHNOLOGY_CTRL_Order", 'orderView'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.admin-config', 'technology/admin', 'FRMTECHNOLOGY_CTRL_Admin', 'index'));
