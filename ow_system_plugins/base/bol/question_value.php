<?php
/**
 * Data Transfer Object for `base_question_value` table.  
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionValue extends OW_Entity
{
    /**
     * @var int
     */
    public $questionName;
    /**
     * @var string
     */
    public $value;
    /**
     * @var integer
     */
    public $sortOrder;
}
