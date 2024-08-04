<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgoupsplus.classes
 * @since 1.0
 */
class FRMSUBGROUPS_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function init()
    {
        $service= FRMSUBGROUPS_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmsubgroup.check.access.create.subgroups', array($service, 'checkAccessCreateSubgroups'));
        $eventManager->bind('frmsubgroup.check.access.view.subgroups', array($service, 'checkAccessViewSubgroups'));
        $eventManager->bind('frmsubgroup.check.access.view.subgroup.details', array($service, 'checkAccessViewSubgroupDetails'));
        $eventManager->bind('add.group.setting.elements', array($service, 'addParentGroupField'));
        $eventManager->bind('groups_group_create_complete', array($service, 'createSubgroup'));
        $eventManager->bind('groups.list.add.where.clause', array($service, 'addFindSubGroupsWhereClause'));
        $eventManager->bind('frmgroupsplus.check.can.invite.all', array($service, 'onInviteParentUsers'));
        $eventManager->bind('frmsubgroups.replace.query.group.list', array($service, 'replaceQueryGroupList'));
        $eventManager->bind('frmsubgroups.replace.query.group.list.without.order', array($service, 'replaceQueryGroupListWithoutOrder'));
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($service, 'loadStaticFiles'));
        $eventManager->bind('on.prepare.group.data', array($service, 'getParentGroupData'));
        $eventManager->bind('on.render.group.edit.buttons', array($service, 'getSubGroupDeleteConfirm'));
        $eventManager->bind('groups_on_group_delete', array($service, 'onDeleteGroup'));
        $eventManager->bind('groups_find_subgroups', array($service, 'onFindSubGroups'));
        $eventManager->bind('frmsubgroups.groups_find_parent', array($service, 'onFindParentGroup'));
    }


}