<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 10:03 AM
 */
class FRMQUESTIONS_BOL_OptionDao extends OW_BaseDao
{

    const QUESTION_ID = 'questionId';
    const USER_ID = 'userId';
    const TEXT = 'text';
    const TIME_STAMP = 'timeStamp';

    private static $INSTANCE;

    public static function getInstance(){
        if(!isset(self::$INSTANCE))
            self::$INSTANCE = new self();
        return self::$INSTANCE;
    }

    public function findOptionList($questionId){
        $example = new OW_Example();
        $example->andFieldEqual(self::QUESTION_ID,$questionId);
        return $this->findIdListByExample($example);
    }

    public function findOptionListByQuestionIds($questionIds){
        if (empty($questionIds)) {
            return array();
        }
        $example = new OW_Example();
        $example->andFieldInArray(self::QUESTION_ID, $questionIds);
        return $this->findListByExample($example);
    }

    public function findOptionsAnswersListByQuestionIds($questionIds){
        if (!is_array($questionIds) || empty($questionIds)) {
            return array();
        }

        $query = 'SELECT a.* FROM ' . FRMQUESTIONS_BOL_QuestionDao::getInstance()->getTableName() . ' q, ' . FRMQUESTIONS_BOL_AnswerDao::getInstance()->getTableName() . ' a, ' . FRMQUESTIONS_BOL_OptionDao::getInstance()->getTableName() . ' o WHERE o.questionId = q.id AND q.id = a.questionId AND a.optionId = o.id and q.id in (' . $this->dbo->mergeInClause($questionIds) . ')';
        return $this->dbo->queryForList($query);
    }

    public function deleteByQuestion($questionId){
        $example = new OW_Example();
        $example->andFieldEqual(self::QUESTION_ID,$questionId);
        return $this->deleteByExample($example);
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmquestions_option';
    }

    public function getDtoClassName()
    {
        return 'FRMQUESTIONS_BOL_Option';
    }
}