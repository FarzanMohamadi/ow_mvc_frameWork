<?php
/**
 * FRM Employee
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */

/* Admin routes */
OW::getRouter()->addRoute(new OW_Route('frmemployee.admin', 'frmemployee/admin', 'FRMEMPLOYEE_CTRL_Admin', 'index'));


$q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
if (empty($q_ee)) {
    return;
}

FRMEMPLOYEE_CLASS_EventHandler::getInstance()->init();

/* Frontend routes */
OW::getRouter()->addRoute(new OW_Route('frmemployee.employees', 'manage-employees', 'FRMEMPLOYEE_CTRL_Employee', 'employees'));
OW::getRouter()->addRoute(new OW_Route('frmemployee.toggle.state', 'toggle-state-employees/:id', 'FRMEMPLOYEE_CTRL_Employee', 'toggleState'));

