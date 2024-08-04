<?php
/**
 * Data Access Object for `base_question_data` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_PreferenceDataDao extends OW_BaseDao
{
    const PREFERENCE_NAME = 'key';
    const USER_ID = 'userId';
    const VALUE = 'value';

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
     * @var BOL_PreferenceDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_PreferenceDataDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
            self::$classInstance = new self();

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'BOL_PreferenceData';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_preference_data';
    }

    public function findByPreferenceNameList( array $preferenceNameList, $userId )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, $userId);
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);
        return $this->findListByExample($example);
    }

    public function deleteByPreferenceNamesList( array $preferenceNameList )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);
        $this->deleteByExample($example);
    }

    /**
     * Returns preference values
     *
     * @return array
     */
    public function findByPreferenceListForUserList( array $preferenceNameList, $userIdList )
    {
        if ( $preferenceNameList === null || count($preferenceNameList) === 0 )
        {
            return array();
        }

        if ( $userIdList === null || count($userIdList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray(self::USER_ID, $userIdList);
        $example->andFieldInArray(self::PREFERENCE_NAME, $preferenceNameList);

        $data = $this->findListByExample($example);

        $result = array();
        foreach ( $data as $object )
        {
            $result[$object->userId][$object->key] = $object;
        }

        return $result;
    }

    public function batchReplace( array $objects )
    {
        $this->dbo->batchInsertOrUpdateObjectList($this->getTableName(), $objects);
        return $this->dbo->getAffectedRows();
    }

    public function deleteByUserId( $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual(self::USER_ID, (int) $userId);

        $this->deleteByExample($example);
    }
}