<?php
/**
 * Data Transfer Object for `language_value` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguageValue extends OW_Entity
{
    /**
     *
     * @var int
     */
    public $languageId;
    /**
     *
     * @var int
     */
    public $keyId;
    /**
     * 
     * @var string
     */
    public $value;

    /**
     * @return int
     */
    public function getKeyId()
    {
        return $this->keyId;
    }

    /**
     * @return int
     */

    public $original_value;

    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param int $keyId
     * @return BOL_LanguageValue
     */
    public function setKeyId( $keyId )
    {
        $this->keyId = $keyId;

        return $this;
    }

    /**
     * @param int $languageId
     * @return BOL_LanguageValue
     */
    public function setLanguageId( $languageId )
    {
        $this->languageId = $languageId;

        return $this;
    }

    /**
     * @param string $value
     * @return BOL_LanguageValue
     */
    public function setValue( $value )
    {
        $this->value = trim($value);

        return $this;
    }
    public function setOriginalValue( $original_value )
    {
        $this->original_value = trim($original_value);

        return $this;
    }
}
?>