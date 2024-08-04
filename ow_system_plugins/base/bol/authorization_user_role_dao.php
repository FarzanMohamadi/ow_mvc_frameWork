<?php
/**
 * Data Access Object for `base_authorization_user_role` table.
 *
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_AuthorizationUserRoleDao extends OW_BaseDao
{

    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var BOL_AuthorizationUserRoleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_AuthorizationUserRoleDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_AuthorizationUserRole';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_authorization_user_role';
    }

    public function getRoleIdList( $userId )
    {
        $sql = 'SELECT `roleId` FROM ' . $this->getTableName() . ' WHERE `userId`=?';

        return $this->dbo->queryForColumnList($sql, array($userId));
    }

    public function countByRoleId( $id )
    {
        /*
        $ex = new OW_Example();

        $ex->andFieldEqual('roleId', $id);

        return $this->countByExample($ex);
        */
        $usersTable = OW_DB_PREFIX . 'base_user';
        $query = "SELECT roles.* FROM {$this->getTableName()} roles
            INNER JOIN {$usersTable} users  ON roles.userId=users.id
            WHERE  `roleId`=:roleId";

        return count($this->dbo->queryForList($query,array('roleId'=>$id)));
    }

    public function findUsersByRoleId( $roleId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('roleId', $roleId);
        return $this->findListByExample($ex);
    }

    public function findUsersByRoleIds( $roleIds )
    {
        if($roleIds == null || sizeof($roleIds) == 0){
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('roleId', $roleIds);
        return $this->findListByExample($ex);
    }

    public function onDeleteRole( $roleId, $defaulRoleId )
    {
        $query = "
			SELECT `ur`.`id`,  IF(`ur2`.`id` IS NULL, 'update', 'delete') AS `case`
			FROM `{$this->getTableName()}` AS `ur`
			LEFT JOIN `{$this->getTableName()}` AS `ur2`
				ON( `ur`.`userId` = `ur2`.`userId` and `ur2`.`roleId` = :default)

			WHERE `ur`.`roleId` = :toDelete
    	";

        $list = $this->dbo->queryForList($query, array(':toDelete' => $roleId, ':default' => $defaulRoleId));

        if ( $list === false )
        {
            return false;
        }

        $idList = array(
            'toDelete' => array(),
            'toUpdate' => array(),
        );

        foreach ( $list as $row )
        {
            switch ( $row['case'] )
            {
                case 'delete':
                    $idList['toDelete'][] = (int) $row['id'];

                    break;

                case 'update':
                    $idList['toUpdate'][] = (int) $row['id'];

                    break;
            }
        }

        if ( !empty($idList['toDelete']) )
        {
            $query = "DELETE FROM {$this->getTableName()} WHERE `id` IN({$this->dbo->mergeInClause($idList['toDelete'])})";

            $this->dbo->query($query);
        }

        if ( !empty($idList['toUpdate']) )
        {
            $query = "UPDATE {$this->getTableName()} SET `roleId`=? WHERE `id` IN({$this->dbo->mergeInClause($idList['toUpdate'])})";

            $this->dbo->query($query, array($defaulRoleId));
        }
    }

    public function deleteByUserId( $userId )
    {
        $userId = (int) $userId;
        if ( $userId > 0 )
        {
            $ex = new OW_Example();
            $ex->andFieldEqual('userId', $userId);
            $this->deleteByExample($ex);
        }
    }

    public function deleteUserRole( $userId, $roleId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('userId', $userId);
        $ex->andFieldEqual('roleId', $roleId);
        $this->deleteByExample($ex);
    }
    private $cachedItems = array();

    public function getLastDisplayLabelRoleOfIdList(array $idList )
    {
        $displayLabel = true;
        if ( count($idList) < 1 )
        {
            return array();
        }

        $idList = array_map('intval', $idList);

        $idsToRequire = array();
        $result = array();
        $var = null;

        foreach ( $idList as $id )
        {
            if ( array_key_exists($id, $this->cachedItems) )
            {
                if ( $this->cachedItems[$id] !== null )
                {
                    $result[$id] = $this->cachedItems[$id];
                }
            }
            else
            {
                $idsToRequire[] = $id;
            }
        }

        $items = array();
        
        if ( !empty($idsToRequire) )
        {
            $roleTable = BOL_AuthorizationRoleDao::getInstance()->getTableName();
            $labelCond = $displayLabel ? ' AND r.displayLabel=1 ' : '';
            $query = "SELECT `userId`,`name`,`custom` FROM {$this->getTableName()} ur 
                  INNER JOIN {$roleTable} r ON ur.roleId=r.id
                  WHERE ur.userId IN({$this->dbo->mergeInClause($idsToRequire)}) " . $labelCond . "
                  ORDER BY sortOrder DESC";

            $items = $this->dbo->queryForList($query);
        }

        foreach ( $items as $key => $item )
        {
        	if ( key_exists($item['userId'], $result) )
        	{
        		continue;
        	}
        	
            $result[(int) $item['userId']] = $item;
            $this->cachedItems[(int) $item['userId']] = $item;
        }
        
        return $result;
    }

    public function clearCachedItems($userId){
        unset($this->cachedItems[$userId]);
    }
}