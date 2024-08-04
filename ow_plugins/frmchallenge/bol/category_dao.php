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
class FRMCHALLENGE_BOL_CategoryDao extends OW_BaseDao
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
        return 'FRMCHALLENGE_BOL_Category';
    }
    
    public function getTableName()
    {
        return OW_DB_PREFIX . 'frmchallenge_challenge_category';
    }

    /***
     * @return array
     */
    public function getAllCategories(){
        $example = new OW_Example();
        return $this->findListByExample($example);
    }

    public function findByTitle($title){
        $example = new OW_Example();
        $example->andFieldEqual('title',$title);
        return $this->findObjectByExample($example);
    }
}
