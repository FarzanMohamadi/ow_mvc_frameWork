<?php
/**
 * @package ow_system_plugins.base.bol
 * @since 1.8.1
 */
abstract class BOL_StoreItem extends OW_Entity
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $developerKey;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $licenseKey;

    /**
     * @var int
     */
    public $licenseCheckTimestamp;

    /**
     * @var integer
     */
    public $build = 0;

    /**
     * @var boolean
     */
    public $update = 0;

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $description
     * @return BOL_StoreItem
     */
    public function setDescription( $description )
    {
        $this->description = trim($description);

        return $this;
    }

    /**
     * @param string $key
     * @return BOL_StoreItem
     */
    public function setKey( $key )
    {
        $this->key = trim($key);

        return $this;
    }

    /**
     * @param string $title
     * @return BOL_StoreItem
     */
    public function setTitle( $title )
    {
        $this->title = trim($title);

        return $this;
    }

    /**
     * @return int
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param int $build
     * @return BOL_StoreItem
     */
    public function setBuild( $build )
    {
        $this->build = (int) $build;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * @param int $update
     * @return BOL_StoreItem
     */
    public function setUpdate( $update )
    {
        $this->update = (int) $update;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     * @return BOL_StoreItem
     */
    public function setLicenseKey( $licenseKey )
    {
        $this->licenseKey = $licenseKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getDeveloperKey()
    {
        return $this->developerKey;
    }

    /**
     * @param string $developerKey
     * @return BOL_StoreItem
     */
    public function setDeveloperKey( $developerKey )
    {
        $this->developerKey = $developerKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getLicenseCheckTimestamp()
    {
        return $this->licenseCheckTimestamp;
    }

    /**
     * @param int $licenseCheckTimestamp
     * @return BOL_StoreItem
     */
    public function setLicenseCheckTimestamp( $licenseCheckTimestamp )
    {
        $this->licenseCheckTimestamp = $licenseCheckTimestamp;

        return $this;
    }
}
