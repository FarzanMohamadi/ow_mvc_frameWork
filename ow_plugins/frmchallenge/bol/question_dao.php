<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.bol
 * @since 1.0
 */
class FRMCHALLENGE_BOL_QuestionDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Question';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_question';
    }

    public function getRandomQuestionsByCategory($count = 5, $categoryIds = null){
        $whereCondition = "";
        if($categoryIds != null && !empty($categoryIds)){
            $whereCondition = " and q.categoryId = " . $categoryIds . " ";
        }
        $query = "SELECT DISTINCT q.* FROM " . $this->getTableName() . " q, " . FRMCHALLENGE_BOL_AnswerDao::getInstance()->getTableName() . " a where q.id = a.questionId and a.correct = 1 " . $whereCondition ." ORDER BY RAND() LIMIT ".$count;
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }

    public function countQuestionByCategory($categoryId){
        $example = new OW_Example();
        $example->andFieldEqual('categoryId',$categoryId);
        return $this->countByExample($example);
    }

    public function findByTitleAndCategory($title,$categoryId){
        $example = new OW_Example();
        $example->andFieldEqual('title',$title);
        $example->andFieldEqual('categoryId',$categoryId);
        return $this->findObjectByExample($example);
    }

    public function findByTitleLike($title){
        $example = new OW_Example();
        $example->andFieldLike('title','%'.$title.'%');
        return $this->findListByExample($example);
    }
}
