<?php
/**
 * frmmutual
 */

OW::getRouter()->addRoute(new OW_Route('frmmutual.admin', 'frmmutual/admin', 'FRMMUTUAL_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmmutual.mutual.firends', 'frmmutual/mutuals/:userId', 'FRMMUTUAL_CTRL_Mutuals', 'index'));