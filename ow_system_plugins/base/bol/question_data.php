<?php
/**
 * Data Transfer Object for `base_question_data` table.
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionData extends OW_Entity
{
    /**
     * @var int
     */
    public $questionName;
    /**
     * @var integer
     */
    public $userId;
    /**
     * @var string
     */
    public $textValue = '';
    /**
     * @var integer
     */
    public $intValue = 0;
    /**
     * @var integer
     */
    public $dateValue;
}