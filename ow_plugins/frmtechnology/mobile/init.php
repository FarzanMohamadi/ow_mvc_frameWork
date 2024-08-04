<?php
FRMTECHNOLOGY_CLASS_EventHandler::getInstance()->init();

OW::getRouter()->addRoute(new OW_Route('frmtechnology.index', 'frmtechnology/technologies', 'FRMTECHNOLOGY_CTRL_Technologies', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.view-list', 'frmtechnology/technologies/:listType', 'FRMTECHNOLOGY_CTRL_Technologies', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.add', 'frmtechnology/add', "FRMTECHNOLOGY_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.edit', 'frmtechnology/edit/:technologyId', "FRMTECHNOLOGY_CTRL_Save", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.view', 'frmtechnology/:technologyId', 'FRMTECHNOLOGY_CTRL_Technologies', 'view'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.submit', 'frmtechnology/order/:technologyId', "FRMTECHNOLOGY_CTRL_Order", 'orderSubmit'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.orderIndex', 'frmtechnology/orders', "FRMTECHNOLOGY_CTRL_Order", 'orderIndex'));
OW::getRouter()->addRoute(new OW_Route('frmtechnology.orderView', 'frmtechnology/orders/:orderId', "FRMTECHNOLOGY_CTRL_Order", 'orderView'));
