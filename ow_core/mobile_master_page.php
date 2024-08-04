<?php
/**
 * Master page is a common markup "border" for controller's output.
 * It includes menus, sidebar, header, etc.
 *
 * @package ow_core
 * @since 1.0
 */
class OW_MobileMasterPage extends OW_MasterPage
{
    /*
     * List of default master page templates.
     */
    const TEMPLATE_GENERAL = "mobile_general";
    const TEMPLATE_BLANK = "mobile_blank";

    /**
     * List of button params
     */
    const BTN_DATA_ID = "id";
    const BTN_DATA_CLASS = "class";
    const BTN_DATA_HREF = "href";
    const BTN_DATA_EXTRA = "extraString";

    private $buttonData;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->buttonData = array(
            "left" => array(self::BTN_DATA_ID => "owm_header_left_btn", self::BTN_DATA_CLASS => null, self::BTN_DATA_HREF => "javascript://",
                self::BTN_DATA_EXTRA => ""),
            "right" => array(self::BTN_DATA_ID => "owm_header_right_btn", self::BTN_DATA_CLASS => null, self::BTN_DATA_HREF => "javascript://",
                self::BTN_DATA_EXTRA => "")
        );
    }

    /**
     * Master page init actions. Template assigning, registering standard cmps, etc.
     * Default version works for `general` master page. 
     */
    protected function init()
    {
        $themeKey = OW::getThemeManager()->getCurrentTheme()->getDto()->key;
        $customThemeEvent = OW::getEventManager()->trigger(new OW_Event('frmthememanager.on.before.theme.style.renderer', array()));
        $customTheme = (isset($customThemeEvent) && !empty($customThemeEvent->getData()['url']));
        if (FRMSecurityProvider::themeCoreDetector() && $customTheme ){
            $themeObject = FRMTHEMEMANAGER_BOL_Service::getInstance()->getThemeArrayByKey($customThemeEvent->getData()['CurrentActiveTheme']);
            if (isset($themeObject['urls']['mainLogo'])){
                $iconUrl = $themeObject['urls']['mainLogo'];
            }else{
                $iconUrl = BOL_ThemeService::getInstance()->getStaticUrl($themeKey)."mobile/images/logo.png";
            }
        }else{
            $iconUrl = BOL_ThemeService::getInstance()->getStaticUrl($themeKey)."mobile/images/logo.png";
        }
        $this->assign('site_name', OW::getConfig()->getValue('base', 'site_name'));
        $this->assign('currentThemeStaticsUrl', $iconUrl);
    }

    /**
     * @param array $data
     */
    public function setLButtonData( array $data )
    {
        $this->buttonData["left"] = array_merge($this->buttonData["left"], $data);
    }

    /**
     * @param array $data
     */
    public function setRButtonData( array $data )
    {
        $this->buttonData["right"] = array_merge($this->buttonData["right"], $data);
    }

    public function onBeforeRender()
    {
        if ( $this->getTemplate() === null )
        {
            $this->setTemplate(OW::getThemeManager()->getMasterPageTemplate(self::TEMPLATE_GENERAL));
        }

        $this->addComponent("signIn", new BASE_MCMP_SignIn());
        $this->addComponent("topMenu", new BASE_MCMP_TopMenu());
        $this->addComponent("bottomMenu", new BASE_MCMP_BottomMenu());
        $this->assign("buttonData", $this->buttonData);

        parent::onBeforeRender();
    }

    public function setTemplate( $template )
    {
        //TODO remove dirty hack for backcompat
        if ( substr(basename($template), 0, strlen(self::TEMPLATE_BLANK)) == self::TEMPLATE_BLANK )
        {
            $this->buttonData = array("left" => array(), "right" => array());
        }

        parent::setTemplate($template);
    }
}
