<?php
/**
 * Data Access Object for `base_question_section` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionSection extends OW_Entity
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var integer
     */
    public $sortOrder;
    
    /**
     * @var int
     */
    public $isHidden = false;
    
    /**
     * @var int
     */
    public $isDeletable = true;
}