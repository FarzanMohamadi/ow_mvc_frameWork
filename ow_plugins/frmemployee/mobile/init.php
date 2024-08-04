<?php
/**
 * FRM Employee
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemployee
 * @since 1.0
 */

$q_ee = OW::getConfig()->getValue('frmemployee', 'employee_question');
if (empty($q_ee)) {
    return;
}

FRMEMPLOYEE_CLASS_EventHandler::getInstance()->genericInit();
