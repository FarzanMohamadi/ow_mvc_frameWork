<?php
/**
 * frmvitrin
 */

OW::getRouter()->addRoute(new OW_Route('frmvitrin.index', 'vitrin', 'FRMVITRIN_MCTRL_Vitrin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmvitrin.item', 'vitrin/:id', 'FRMVITRIN_MCTRL_Vitrin', 'item'));