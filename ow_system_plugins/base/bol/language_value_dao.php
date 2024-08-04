<?php
/**
 * Singleton. 'Language Value' Data Access Object
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageValueDao extends OW_BaseDao
{

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var BOL_LanguageValueDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return BOL_LanguageValueDao
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_LanguageValue';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_language_value';
    }

    public function findLastKeyList( $first, $count, $prefix = null )
    {
        if ( $prefix !== null )
        {
            $prefixId = BOL_LanguagePrefixDao::getInstance()->findPrefixId($prefix);

            if ( !$prefixId )
            {
                throw new Exception('There is no such prefix..');
            }
        }

        $query_part = array();

        $query_part['optional-prefix_criteria'] = ( $prefix !== null && $prefixId > 0 ) ? "`p`.`id` = {$prefixId}" : '1';

        $query_part['dev-mode-order'] = !$this->isDevMode() && $prefix == null ? "IF(`p`.`prefix` = 'ow_custom', 1, 0) DESC, " : '';

        $keyTable = BOL_LanguageKeyDao::getInstance()->getTableName();
        $prefixTable = BOL_LanguagePrefixDao::getInstance()->getTableName();

        $query = "
		SELECT `key`,
		       `p`.`label`, `p`.`prefix`
		FROM `" . $keyTable . "` as `k`
		INNER JOIN `" . $prefixTable . "` AS `p`
		     ON ( `k`.`prefixId` = `p`.`id` )
	    WHERE {$query_part['optional-prefix_criteria']} /*optional-prefix_criteria*/ 
		ORDER BY {$query_part['dev-mode-order']} `p`.`label`,
		         `k`.`id` desc
		LIMIT ?, ?
		";

        return $this->dbo->queryForList($query, array($first, $count));
    }

    // This function retrieves dictionary key by 'language id' and 'key value'
    public function findSearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ?
			 ORDER BY `p`.`label`,
			        `k`.`id` desc
			 LIMIT ?, ?
			";

        return $this->dbo->queryForList($_query, array("%{$search}%", $languageId, $first, $count));
    }

    // This function retrieves dictionary key by 'language id', 'plugin prefix' and 'key value'
    public function findSearchResultKeyListWithPrefix( $languageId, $first, $count, $search, $prefix )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND p.`prefix` = ? AND `v`.`languageId` = ?
			 ORDER BY `p`.`label`,
			        `k`.`id` desc
			 LIMIT ?, ?
			";

        return $this->dbo->queryForList($_query, array("%{$search}%", $prefix, $languageId, $first, $count));
    }

    // This function retrieves dictionary key by 'language id' and 'key name'
    public function findKeySearchResultKeyList( $languageId, $first, $count, $search )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` ) 
			 WHERE `k`.`key` LIKE :keySearch
			 LIMIT :first, :count
			";

        return $this->dbo->queryForList($_query, array('keySearch'=>"%{$search}%", 'first'=>$first, 'count'=>$count));
    }

    // This function retrieves dictionary key by 'language id', 'plugin prefix' and 'key name'
    public function findKeySearchResultKeyListWithPrefix( $languageId, $first, $count, $search ,$prefix )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT `k`.`key`,
			        `p`.`label`, `p`.`prefix`
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` ) 
			 WHERE `k`.`key` LIKE :keySearch AND p.`prefix` = :prefix
			 LIMIT :first, :count
			";

        return $this->dbo->queryForList($_query, array('keySearch'=>"%{$search}%", 'prefix'=>$prefix, 'first'=>$first, 'count'=>$count));
    }

    // This function finds and counts dictionary key by 'language id' and 'key value'
    public function countSearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND `v`.`languageId` = ? 
			";

        return $this->dbo->queryForColumn($_query, array("%{$search}%", $languageId));
    }

    // This function finds and counts dictionary key by 'language id', 'plugin prefix' and 'key value'
    public function countSearchResultKeysWithPrefix( $languageId, $search ,$prefix)
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageValueDao::getInstance()->getTableName() . "` as `v`
			 INNER JOIN `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			      ON( `v`.`keyId` = `k`.`id` )
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			      ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `v`.`value` LIKE ? AND p.`prefix` = ? AND `v`.`languageId` = ? 
			";

        return $this->dbo->queryForColumn($_query, array("%{$search}%", $prefix ,$languageId));
    }

    // This function finds and counts dictionary key by 'language id' and 'key name'
    public function countKeySearchResultKeys( $languageId, $search )
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `k`.`key` LIKE :keySearch
			";

        return $this->dbo->queryForColumn($_query, array('keySearch'=>"%{$search}%"));
    }

    // This function finds and counts dictionary key by 'language id', 'plugin prefix' and 'key name'
    public function countKeySearchResultKeysWithPrefix( $languageId, $search, $prefix)
    {
        $search = $this->dbo->escapeValue($search);

        $_query =
            "
			 SELECT COUNT(*)
			 FROM `" . BOL_LanguageKeyDao::getInstance()->getTableName() . "` as `k`
			 INNER JOIN `" . BOL_LanguagePrefixDao::getInstance()->getTableName() . "` as `p`
			    ON( `k`.`prefixId` = `p`.`id` )
			 WHERE `k`.`key` LIKE :keySearch AND p.`prefix` = :prefix
			";

        return $this->dbo->queryForColumn($_query, array('keySearch'=>"%{$search}%", 'prefix'=>$prefix));
    }

    public function findValue( $languageId, $keyId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('languageId', $languageId)->andFieldEqual('keyId', $keyId);

        return $this->findObjectByExample($ex);
    }

    public function findValues( $languageIds, $keyIds )
    {
        if (empty($languageIds) || empty($keyIds)) {
            return array();
        }
        $ex = new OW_Example();
        $ex->andFieldInArray('languageId', $languageIds);
        $ex->andFieldInArray('keyId', $keyIds);

        return $this->findListByExample($ex);
    }

    public function deleteValues( $languageId )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('languageId', $languageId);

        $this->deleteByExample($ex);
    }

    private function isDevMode()
    {
        if ( !empty($_GET) )
        {
            $arr = explode('?', OW::getRequest()->getRequestUri());

            return $arr[0] == OW::getRouter()->uriForRoute('admin_developer_tools_language');
        }

        return OW::getRequest()->getRequestUri() == OW::getRouter()->uriForRoute('admin_developer_tools_language');
    }

    public function deleteByKeyId( $id )
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('keyId', $id);

        $this->deleteByExample($ex);
    }
}