<?php
/**
 * frmslideshow
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmslideshow
 * @since 1.0
 */

class FRMSLIDESHOW_MCMP_ExtraWidget extends BASE_CLASS_Widget
{
    public static $uniqNamePrefix = "mobile.index-FRMSLIDESHOW_MCMP_ExtraWidget-";
    /**
     * @param BASE_CLASS_WidgetParameter $paramObj
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        $uniqName = $paramObj->widgetDetails->uniqName;
        if(strpos($uniqName,FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix) !== 0)
            return false;
        $albumId = intval( substr($uniqName,strlen(FRMSLIDESHOW_MCMP_ExtraWidget::$uniqNamePrefix)));

        $service = FRMSLIDESHOW_BOL_Service::getInstance();
        $slides = $service->getSlides($albumId);
        $slidesArray = array();
        foreach ($slides as $item) {
            $slidesArray[] = array(
                'id' => $item->id,
                'description' => $item->description
            );
        }
        $this->assign('slides', $slidesArray);

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmslideshow')->getMobileCmpViewDir() . 'extra_widget.html');

        // import static files
        $jsDir = OW::getPluginManager()->getPlugin("frmslideshow")->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "slick.js");
        OW::getDocument()->addScript($jsDir . "frmslideshow.js");

        $cssFile = OW::getPluginManager()->getPlugin('frmslideshow')->getStaticCssUrl() . 'frmslideshow.css';
        OW::getDocument()->addStyleSheet($cssFile);
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmslideshow', 'untitled'),
            self::SETTING_ICON => self::ICON_USER
        );
    }
}
