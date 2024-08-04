<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @since 1.0
 */

$old_role = BOL_AuthorizationRoleDao::getInstance()->findRoleByName("seller");
if(empty($old_role)){
    $role = new BOL_AuthorizationRole();
    $role->setName("seller");
    $role->setSortOrder(BOL_AuthorizationRoleDao::getInstance()->findMaxOrder() + 1);
    BOL_AuthorizationRoleDao::getInstance()->save($role);
}
