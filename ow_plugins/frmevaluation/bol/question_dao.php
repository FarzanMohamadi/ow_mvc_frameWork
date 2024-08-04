<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmevaluation.bol
 * @since 1.0
 */
class FRMEVALUATION_BOL_QuestionDao extends OW_BaseDao
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
        return 'FRMEVALUATION_BOL_Question';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmevaluation_question';
    }

    /***
     * @param $categoryId
     * @param bool $ignoreQuestionWithEmptyValue
     * @return int
     */
    public function getCountOfQuestionsOfCategory($categoryId, $ignoreQuestionWithEmptyValue = true){
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        $questions = $this->findListByExample($ex);
        $count = 0;
        foreach($questions as $question){
            if(sizeof(FRMEVALUATION_BOL_Service::getInstance()->getValuesOfQuestion($question->id))>0 || !$ignoreQuestionWithEmptyValue){
                $count++;
            }
        }
        return $count;
    }

    /***
     * @param $categoryId
     * @return array
     */
    public function getQuestions($categoryId){
        $ex = new OW_Example();
        $ex->andFieldEqual('categoryId', $categoryId);
        $ex->setOrder('`order` ASC');
        return $this->findListByExample($ex);
    }

    public function getMaxOrder(){
        $query = "SELECT MAX(`order`) FROM `{$this->getTableName()}`";
        $maxOrder = $this->dbo->queryForColumn($query);
        if ($maxOrder == null) {
            $maxOrder = 0;
        }
        return $maxOrder;
    }

    /***
     * @param $questionId
     * @return FRMEVALUATION_BOL_Question
     */
    public function getQuestion($questionId){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $questionId);
        return $this->findObjectByExample($ex);
    }

    /***
     * @param $categoryId
     * @param $title
     * @param $description
     * @param $hasDescribe
     * @param $hasFile
     * @param $hasVerification
     * @param $weight
     * @param $level
     * @param $order
     * @return FRMEVALUATION_BOL_Question
     */
    public function saveQuestion($categoryId, $title, $description, $hasDescribe, $hasFile, $hasVerification, $weight, $level, $order){
        $question = new FRMEVALUATION_BOL_Question();
        $question->title = $title;
        $question->description = $description;
        $question->hasDescribe = $hasDescribe;
        $question->hasFile = $hasFile;
        $question->hasVerification = $hasVerification;
        $question->categoryId = $categoryId;
        $question->order = $order;
        $question->weight = $weight;
        $question->level = $level;
        $this->save($question);
        return $question;
    }

    /***
     * @param $questionId
     * @param $catId
     * @param $title
     * @param $description
     * @param $hasDescribe
     * @param $hasFile
     * @param $hasVerification
     * @param $weight
     * @param $level
     * @return mixed
     */
    public function update($questionId, $catId,  $title, $description, $hasDescribe, $hasFile, $hasVerification, $weight, $level){
        $ex = new OW_Example();
        $ex->andFieldEqual('id', $questionId);
        $question = $this->findObjectByExample($ex);
        $question->title = $title;
        $question->description = $description;
        $question->hasDescribe = $hasDescribe;
        $question->hasVerification = $hasVerification;
        $question->categoryId = $catId;
        $question->hasFile = $hasFile;
        $question->weight = $weight;
        $question->level = $level;
        $this->save($question);
        return $question;
    }
}
