<?php
/**
 * Data Transfer Object for `base_config` table.  
 * 
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Config extends OW_Entity
{
    /**
     * Constructor.
     */
    public function __construct($x=false)
    {
        if ($x !== false){
            $this->setKey($x->key);
            $this->setName($x->name);
            $this->setValue($x->value);
            $this->setDescription($x->description);
            $this->setId($x->id);
        }
    }

    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $name;
    /**
     * @var mixed
     */
    public $value;
    /**
     * @var string
     */
    public $description;

    /**
     * @return string $key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed $value
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $key
     * @return BOL_Config
     */
    public function setKey( $key )
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @param string $name
     * @return BOL_Config
     */
    public function setName( $name )
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param mixed $value
     * @return BOL_Config
     */
    public function setValue( $value )
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param string $description
     * @return BOL_Config
     */
    public function setDescription( $description )
    {
        $this->description = $description;
        return $this;
    }
}