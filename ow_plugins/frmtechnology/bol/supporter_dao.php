<?php
class FRMTECHNOLOGY_BOL_SupporterDao extends OW_BaseDao
{

    private static $classInstance;


    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    public function getDtoClassName()
    {
        return 'FRMTECHNOLOGY_BOL_Supporter';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmtechnology_supporter';
    }

    public function findByTechnologyId( $technologyId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('technologyId', $technologyId);
        return $this->findListByExample($example);
    }
    public function findByTechnologyIdLimited( $technologyId, $first, $count )
    {
        $query = "SELECT * FROM " . $this->getTableName() . "   WHERE technologyId=:t LIMIT :lf, :lc";
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName(), array(
            "t" => $technologyId,
            "lf" => $first,
            "lc" => $count
        ));
    }
    public function deleteByUserIdAndTechnologyId( $technologyId, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('technologyId', (int) $technologyId);
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }
}