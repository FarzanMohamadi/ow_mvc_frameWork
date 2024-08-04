<?php
/**
 * Data Transfer Object for `base_question_config` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionConfig extends OW_Entity
{
    /**
     * @var string
     */
    public $questionPresentation;
    /**
     * @var string
     */
    public $name;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $presentationClass;
}

