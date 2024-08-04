<?php
/**
 * @package ow_system_plugins.base.bol
 * @since 1.0
 */
class BOL_Theme extends BOL_StoreItem
{
    /**
     * @var string
     */
    public $customCss;

    /**
     * @var string
     */
    public $mobileCustomCss;

    /**
     * @var string
     */
    public $customCssFileName;

    /**
     * @var string
     */
    public $sidebarPosition;

    /**
     * @return string
     */
    public function getCustomCss()
    {
        return $this->customCss;
    }

    /**
     * @param string $css
     * @return BOL_Theme
     */
    public function setCustomCss( $css )
    {
        $this->customCss = trim($css);

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomCssFileName()
    {
        return $this->customCssFileName;
    }

    /**
     * @param string $customCssFileName
     * @return BOL_Theme
     */
    public function setCustomCssFileName( $customCssFileName )
    {
        $this->customCssFileName = $customCssFileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSidebarPosition()
    {
        return $this->sidebarPosition;
    }

    /**
     * @param string $sidebarPosition
     * @return BOL_Theme
     */
    public function setSidebarPosition( $sidebarPosition )
    {
        $this->sidebarPosition = $sidebarPosition;

        return $this;
    }

    /**
     * @return string
     */
    public function getMobileCustomCss()
    {
        return $this->mobileCustomCss;
    }

    /**
     * @param string $mobileCustomCss
     * @return BOL_Theme
     */
    public function setMobileCustomCss( $mobileCustomCss )
    {
        $this->mobileCustomCss = $mobileCustomCss;

        return $this;
    }

    /**
     * @deprecated since version 1.8.1
     * @return string
     */
    public function getName()
    {
        return $this->key;
    }

    /**
     * @deprecated since version 1.8.1
     * 
     * @param string $name
     * @return BOL_Theme
     */
    public function setName( $name )
    {
        $this->key = trim($name);
        return $this;
    }
}
