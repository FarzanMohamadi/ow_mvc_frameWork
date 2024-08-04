<?php
/**
 * Data Access Object for `base_question_data` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionDataDao extends OW_BaseDao
{
    const QUESTION_NAME = 'questionName';
    const USER_ID = 'userId';
    const TEXT_VALUE = 'textValue';
    const INT_VALUE = 'intValue';
    const DATE_VALUE = 'dateValue';

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
     * @var BOL_QuestionDataDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_QuestionDataDao
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
        return 'BOL_QuestionData';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'base_question_data';
    }

    public function findByQuestionsNameList( array $questionNames, $userId )
    {
        if ( $questionNames === null || count($questionNames) === 0 || empty($userId) )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldInArray('questionName', $questionNames);
        return $this->findListByExample($example);
    }

    public function findQuestionValuesData ($questionName, $userIds) {
        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldInArray('userId', $userIds);
        return $this->findListByExample($example);
    }

    public function deleteByQuestionNamesList( array $questionNames )
    {
        if ( $questionNames === null || count($questionNames) === 0 )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldInArray('questionName', $questionNames);
        $this->deleteByExample($example);
    }

    public function findByQuestionAndAnswers( $questionName, $answers )
    {
        if ( $questionName === null || count($answers) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldInArray(self::INT_VALUE, $answers);
        return $this->findListByExample($example);
    }

    public function findByQuestionAndTextAnswers( $questionName, $answers )
    {
        if ( $questionName === null || count($answers) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldInArray(self::TEXT_VALUE, $answers);
        return $this->findListByExample($example);
    }

    /**
     * @param $questionName
     * @param $userId
     * @return BOL_QuestionData
     */
    public function findByQuestionAndUser( $questionName, $userId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('questionName', $questionName);
        $example->andFieldEqual(self::USER_ID, $userId);
        return $this->findObjectByExample($example);
    }
    /**
     * Returns questions values
     *
     * @return array
     */
    public function findByQuestionsNameListForUserList( array $questionlNameList, $userIdList )
    {
        if ( $questionlNameList === null || count($questionlNameList) === 0 )
        {
            return array();
        }

        if ( $userIdList === null || count($userIdList) === 0 )
        {
            return array();
        }

        $example = new OW_Example();
        $example->andFieldInArray('userId', $userIdList);
        $example->andFieldInArray('questionName', $questionlNameList);

        $data = $this->findListByExample($example);

        $result = array();
        foreach ( $data as $object )
        {
            $result[$object->userId][$object->questionName] = $object;
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
        $example->andFieldEqual('userId', (int) $userId);

        $this->deleteByExample($example);
    }

    public function deleteByQuestionListAndUserId(array $questionNameList, $userId)
    {
        if ( !$questionNameList )
        {
            return;
        }

        $example = new OW_Example();
        $example->andFieldEqual('userId', (int) $userId);
        $example->andFieldInArray('questionName', $questionNameList);

        $this->deleteByExample($example);
    }

    public function findQuestionsNotForAccountType($userId, $oldAccountType, $newAccountType)
    {
        if ( $userId === null || $oldAccountType === null || $newAccountType === null)
        {
            return;
        }

        $oldAccountTypeName = trim($oldAccountType);
        $newAccountTypeName = trim($newAccountType);

        $sql = "SELECT DISTINCT `question_data`.`questionName`  FROM `" . $this->getTableName() . "` AS `question_data`
                INNER JOIN ". BOL_QuestionDao::getInstance()->getTableName()." `question` ON `question`.`name` = `question_data`.`questionName`
                INNER JOIN ". BOL_QuestionToAccountTypeDao::getInstance()->getTableName() ." `question_to_account` ON `question_to_account`.`questionName` = `question`.`name`
                WHERE `question_to_account`.`accountType` = :oldAccountTypeName AND `question_data`.`userId`= :userId
                    AND `question`.`name` NOT IN 
                    (SELECT `".OW_DB_PREFIX."base_question_to_account_type`.`questionName` 
                    FROM `".OW_DB_PREFIX."base_question_to_account_type`
                    WHERE `".OW_DB_PREFIX."base_question_to_account_type`.`accountType` = :newAccountTypeName)";

        return $this->dbo->queryForList($sql, array('userId'=>$userId,
                                                    'oldAccountTypeName' => $oldAccountTypeName,
                                                    'newAccountTypeName' => $newAccountTypeName));
    }
}