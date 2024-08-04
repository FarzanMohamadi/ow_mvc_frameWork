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
class FRMCHALLENGE_BOL_AnswerDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Answer';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_answer';
    }

    public function findQuestionAnswer($questionId){
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        return $this->findListByExample($example);
    }

    public function findByTitleAndQuestion($title,$questionId){
        $example = new OW_Example();
        $example->andFieldEqual('title', $title);
        $example->andFieldEqual('questionId', $questionId);
        return $this->findListByExample($example);
    }

    public function findQuestionCorrectAnswer($questionId){
        $example = new OW_Example();
        $example->andFieldEqual('questionId', $questionId);
        $example->andFieldEqual('correct', true);
        return $this->findListByExample($example);
    }
}
