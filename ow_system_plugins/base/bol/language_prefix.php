<?php
/**
 * Data Transfer Object for `language_prefix` table
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_LanguagePrefix extends OW_Entity
{
    /**
     * @var string
     */
    public $prefix;
    /**
     *
     * @var string
     */
    public $label;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $label
     * @return BOL_LanguagePrefix
     */
    public function setLabel( $label )
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param string $prefix
     * @return BOL_LanguagePrefix
     */
    public function setPrefix( $prefix )
    {
        $this->prefix = $prefix;

        return $this;
    }
}

