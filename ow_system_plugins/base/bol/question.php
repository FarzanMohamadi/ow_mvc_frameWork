<?php
/**
 * Data Transfer Object for `base_question` table.  
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Question extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $sectionName;
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $presentation;
    /**
     * @var integer
     */
    public $required = 0;
    /**
     * @var integer
     */
    public $onJoin = 0;
    /**
     * @var integer
     */
    public $onEdit = 0;
    /**
     * @var integer
     */
    public $onSearch = 0;
    /**
     * @var integer
     */
    public $onView = 0;
    /**
     * @var integer
     */
    public $base = 0;
    /**
     * @var integer
     */
    public $removable = 1;
    /**
     * @var integer
     */
    public $editable = 1;
    /**
     * @var integer
     */
    public $sortOrder;
    /**
     * @var integer
     */
    public $columnCount = 1;
    /**
     * @var string
     */
    public $parent;
    /**
     * @var string
     */
    public $custom;

    /**
     * @var string
     */
    public $condition;

    /**
     * @return string
     */
    public function getConditionQuestionName()
    {
        if(empty($this->condition))
            return '';
        return json_decode($this->condition, true)['question'];
    }

    /**
     * @return string
     */
    public function getConditionValue()
    {
        if(empty($this->condition))
            return '';
        return json_decode($this->condition, true)['value'];
    }

    /**
     * @param $questionName
     * @param $value
     */
    public function setCondition($questionName, $value)
    {
        $this->condition = json_encode(['question'=>$questionName, 'value'=>$value]);
    }

}

