<?php
/**
 * FRM Rules
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmtelegram
 */


OW::getRouter()->addRoute(new OW_Route('frmrules.index', 'rules', 'FRMRULES_MCTRL_Rules', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmrules.index.section-id', 'rules/:sectionId', 'FRMRULES_MCTRL_Rules', 'index'));
