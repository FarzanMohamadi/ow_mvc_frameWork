<?php
/**
 * Data Access Object for `forum_section` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.bol
 * @since 1.0
 */
class FORUM_BOL_SectionDao extends OW_BaseDao
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
     * @var FORUM_BOL_SectionDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return FORUM_BOL_SectionDao
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
        return 'FORUM_BOL_Section';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'forum_section';
    }

    /**
     * Returns new forum section order
     * @return int
     */
    public function getNewSectionOrder()
    {
        $query = "SELECT MAX( `order` )	FROM `" . $this->getTableName() . "`";
        $order = (int) $this->dbo->queryForColumn($query);

        return $order + 1;
    }

    /**
     * Returns section groups list
     *
     * @param boolean $includeHidden
     * @param int $sectionId
     * @return array
     */
    public function getSectionGroupList( $includeHidden = false, $sectionId = null )
    {
        $groupDao = FORUM_BOL_GroupDao::getInstance();

        $query = "
    	SELECT `s`.`id` AS `sectionId`, `s`.`name` AS `sectionName`, `s`.`order` AS `sectionOrder`, `g`.*    	
    	FROM `" . $this->getTableName() . "` AS `s`
    	INNER JOIN `" . $groupDao->getTableName() . "` AS `g` ON ( `s`.`id` = `g`.`sectionId` )
    	WHERE 1 ".
        ( ((bool)$includeHidden == true) ? "" :  " AND `s`.`isHidden` = 0 " ).
        ( $sectionId ? " AND `s`.`id` = :sId " :  "" ).
        " ORDER BY `s`.`isHidden`, `s`.`order`, `g`.`order` ";

        $params = $sectionId ? array('sId' => $sectionId) : array();

        return $this->dbo->queryForList($query, $params);
    }

    /**
     * Returns section groups list
     * 
     * @param int $isHidden
     * @return array
     */
    public function getCustomSectionGroupList( $isHidden = 0 )
    {
        $groupDao = FORUM_BOL_GroupDao::getInstance();

        $query = "
    	SELECT `g`.*,  `s`.`id` AS `sectionId`, `s`.`name` AS `sectionName`, `s`.`order` AS `sectionOrder`  	
    	FROM `" . $this->getTableName() . "` AS `s`
    	LEFT JOIN `" . $groupDao->getTableName() . "` AS `g` ON ( `s`.`id` = `g`.`sectionId` )
        WHERE `s`.`isHidden` = " . $isHidden . "
        ORDER BY `s`.`order`, `g`.`order`
    	";

        return $this->dbo->queryForList($query);
    }

    /**
     * Returns section list
     * 
     * @param string $sectionName
     * @return array of FORUM_BOL_Section
     */
    public function suggestSection( $sectionName )
    {
        $example = new OW_Example();
        $example->andFieldEqual('isHidden', '0');
        $example->andFieldLike('name', "$sectionName%");

        return $this->findListByExample($example);
    }

    /**
     * Returns section
     * 
     * @param string $sectionName
     * @param int $sectionId
     * @return FORUM_BOL_Section
     */
    public function findSection( $sectionName, $sectionId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('name', $sectionName);

        if ( $sectionId )
            $example->andFieldEqual('id', $sectionId);

        return $this->findObjectByExample($example);
    }

    /**
     * Returns public section
     *
     * @param $sectionName
     * @param $sectionId
     * @return FORUM_BOL_Section
     */
    public function findPublicSection( $sectionName, $sectionId )
    {
        $example = new OW_Example();

        $example->andFieldEqual('name', $sectionName);
        $example->andFieldEqual('isHidden', 0);

        if ( $sectionId )
            $example->andFieldEqual('id', $sectionId);

        return $this->findObjectByExample($example);
    }

    /**
     * Find latest public sections ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicSectionsIds($first, $count)
    {
        $example = new OW_Example();
        $example->andFieldEqual('isHidden', 0);
        $example->setLimitClause($first, $count);
        $example->setOrder('id DESC');

        return $this->findIdListByExample($example);
    }

    /**
     * Returns list of not hidden sections
     *
     * @return array of FORUM_BOL_Section
     */
    public function findGeneralSectionList()
    {
        $example = new OW_Example();

        $example->andFieldEqual('isHidden', "0");

        return $this->findListByExample($example);
    }

    /**
     * Returns section of specified entity
     *
     * @param string $entity
     * @return FORUM_BOL_Section
     */
    public function findByEntity( $entity )
    {
        $example = new OW_Example();
        $example->andFieldEqual('entity', $entity);

        return $this->findObjectByExample($example);
    }

    /**
     * Find forum first not hidden section
     *
     * @return FORUM_BOL_Section
     */
    public function findFirstSection()
    {
        $example = new OW_Example();

        $example->setOrder('`order` ASC');
        $example->andFieldEqual('isHidden', 0);
        $example->setLimitClause(0, 1);

        return $this->findObjectByExample($example);
    }
}