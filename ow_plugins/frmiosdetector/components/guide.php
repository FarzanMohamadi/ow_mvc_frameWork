<?php
/**
 * frmiosdetector
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmiosdetector
 * @since 1.0
 */
class FRMIOSDETECTOR_CMP_Guide extends OW_Component
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $siteName = OW::getConfig()->getValue('base', 'site_name');
        $headerColor = FRMSecurityProvider::themeAttributeExtractor('emailHeaderColor');
        $logo = BOL_ThemeService::getInstance()->getStaticImagesUrl(OW::getConfig()->getValue('base', 'selectedTheme'))."logo.png";
        OW::getDocument()->addStyleDeclaration(".ios_detector_icon{background-image: url(".$logo.")}");
        $this->assign("siteName",$siteName);
        $this->assign("headerColor",$headerColor);
    }
}
