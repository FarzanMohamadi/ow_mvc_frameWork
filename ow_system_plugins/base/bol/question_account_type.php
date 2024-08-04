<?php
/**
 * Data Transfer Object for `base_question_account_type` table.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_QuestionAccountType extends OW_Entity
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $roleId;
    
    /**
     * @var int
     */
    public $sortOrder = 0;

    /**
     * Class instance generator.
     *
     * @param string $name
     * @return BOL_QuestionAccountType
     */
    public static function newInstance( $name = null, $sortOrder = null )
    {
        $dto = new self();

        if ( $name !== null )
        {
            $dto->name = trim($name);
        }

        return $dto;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return BOL_QuestionAccountType
     */
    public function setName( $name )
    {
        $this->name = $name;
        return $this;
    }
}
