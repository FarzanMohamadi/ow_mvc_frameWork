<?php
/**
 * @package ow_core
 * @method static OW_Authorization getInstance()
 * @since 1.0
 */
class OW_Authorization
{
    use OW_Singleton;
    
    /**
     * @var BOL_AuthorizationService
     */
    private $service;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->service = BOL_AuthorizationService::getInstance();
    }

    /**
     * Adds new group.
     *
     * @param string $name
     * @param boolean $moderated
     */
    public function addGroup( $name, $moderated = true )
    {
        if ( $this->service->findGroupByName($name) !== null )
        {
            trigger_error('Cant add group `' . $name . '`! Duplicate entry!');
            return;
        }

        $group = new BOL_AuthorizationGroup();
        $group->name = $name;
        $group->moderated = $moderated;

        $this->service->saveGroup($group);
    }

    /**
     * @param $groupName
     * @param $actionName
     * @param bool $availableForGuest
     * @param bool $addToExistedRole
     */
    public function addAction( $groupName, $actionName, $availableForGuest = false,$addToExistedRole = true )
    {
        $group = $this->service->findGroupByName($groupName);

        if ( $group === null )
        {
            trigger_error('Cant add action `' . $actionName . '`! Empty group `' . $groupName . '`!');
            return;
        }

        if ( $this->service->findAction($groupName, $actionName) !== null )
        {
            trigger_error('Cant add action `' . $actionName . '` to group `' . $groupName . '`! Duplicate entry!');
            return;
        }

        $action = new BOL_AuthorizationAction();
        $action->groupId = $group->id;
        $action->name = $actionName;
        $action->availableForGuest = $availableForGuest;

        $this->service->saveAction($action);
        if($addToExistedRole) {
            $roles = $this->service->getRoleList();
            foreach ($roles as $role) {
                $this->service->grantActionListToRole($role, array($action));
            }
        }
    }

    /**
     * Deletes group and all included actions.
     *
     * @param string $groupName
     */
    public function deleteGroup( $groupName )
    {
        $this->service->deleteGroup($groupName);
    }

    /**
     * Deletes action by group and action names.
     *
     * @param string $groupName
     * @param string $actionName
     */
    public function deleteAction( $groupName, $actionName )
    {
        $action = $this->service->findAction($groupName, $actionName);

        if ( $action !== null )
        {
            $this->service->deleteAction($action->id);
        }
    }

    /**
     * Checks if user authorized for group/action.
     *
     * @param integer $userId
     * @param string $groupName
     * @param string $actionName
     * @param array $extra
     * @return boolean
     */
    public function isUserAuthorized( $userId, $groupName, $actionName = null, $extra = null )
    {
        if ( $extra !== null && !is_array($extra) )
        {
            trigger_error("`ownerId` parameter has been deprecated, pass `extra` parameter instead");
        }

        return $this->service->isActionAuthorizedForUser($userId, $groupName, $actionName, $extra);
    }
}