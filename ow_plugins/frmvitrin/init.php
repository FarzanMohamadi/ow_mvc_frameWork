<?php
/**
 * frmvitrin
 */

OW::getRouter()->addRoute(new OW_Route('frmvitrin.admin', 'frmvitrin/admin', 'FRMVITRIN_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmvitrin.admin.edit-item', 'frmvitrin/admin/edit-item/:id', 'FRMVITRIN_CTRL_Admin', 'editItem'));
OW::getRouter()->addRoute(new OW_Route('frmvitrin.admin.delete-item', 'frmvitrin/admin/delete-item/:id', 'FRMVITRIN_CTRL_Admin', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('frmvitrin.index', 'vitrin', 'FRMVITRIN_CTRL_Vitrin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmvitrin.item', 'vitrin/:id', 'FRMVITRIN_CTRL_Vitrin', 'item'));