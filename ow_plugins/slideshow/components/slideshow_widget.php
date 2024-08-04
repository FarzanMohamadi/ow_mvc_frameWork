<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.components
 * @since 1.4.0
 */
class SLIDESHOW_CMP_SlideshowWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $objParams )
    {
        parent::__construct();
        
        $uniqName = $objParams->widgetDetails->uniqName;
        $this->assign('uniqName', $uniqName);
        
        $service = SLIDESHOW_BOL_Service::getInstance();
        $slides = $service->getSlideList($uniqName);
        $this->assign('slides', $slides);
        
        if ( $slides )
        {
	        $url = OW::getPluginManager()->getPlugin('slideshow')->getStaticJsUrl() . 'slides.min.jquery.js';
	        OW::getDocument()->addScript($url);
	        
	        $settings = $objParams->customParamList;
	        
	        $params = array(
	            'sizes' => $service->getSizes($slides),
	            'pagination' => $settings['navigation'] ? "true" : "false",
	            'interval' => $settings['interval'],
	            'uniqname' => $uniqName,
	            'effect' => $settings['effect'],
                'preloadImage' => OW::getThemeManager()->getThemeImagesUrl() . '/ajax_preloader_content.gif'
	        );
	        
	        $url = OW::getPluginManager()->getPlugin('slideshow')->getStaticJsUrl() . 'slideshow.js';
	        OW::getDocument()->addScript($url);
	        
	        $id = FRMSecurityProvider::generateUniqueId();
	        
	        $script = 'var slideshow' . $id . ' = new slideshow('.json_encode($params).'); slideshow' . $id . '.init();';
	        
	        if ( $objParams->customizeMode )
	        {
		        $script .= 'OW.WidgetPanel.bind("move", function(e) {
		            if ( e.widgetName == "'.$uniqName.'" ) {
		               OW.WidgetPanel.reloadWidget("'.$uniqName.'", function(markup, data){});
		            }
		        });';
	        }
	        
	        OW::getDocument()->addOnloadScript($script);
        }
    }
    
    public static function getSettingList()
    {
        $lang = OW::getLanguage();
        $settingList = array();
        
        $settingList['customSettings'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => array('SLIDESHOW_CMP_SlideshowWidget', 'renderCustomSettingsCmp'),
            'display' => 'block'
        );
        
        $settingList['effect'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $lang->text('slideshow', 'effect'),
            'optionList' => array('fade' => $lang->text('slideshow', 'effect_fade'), 'slide' => $lang->text('slideshow', 'effect_slide')),
            'value' => 'fade'
        );
        
        $settingList['interval'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => $lang->text('slideshow', 'interval'),
            'optionList' => array(
                'long' => $lang->text('slideshow', 'interval_long'), 
                'medium' => $lang->text('slideshow', 'interval_medium'), 
                'short' => $lang->text('slideshow', 'interval_short')
            ),
            'value' => 'medium'
        );
        
        $settingList['navigation'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => $lang->text('slideshow', 'navigation'),
            'value' => '1'
        );

        return $settingList;
    }
    
    public static function renderCustomSettingsCmp( $uniqName )
    {
        $cmp = new SLIDESHOW_CMP_CustomSettings($uniqName);
        return $cmp->render();
    }

    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => false,
        	self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => BASE_CLASS_Widget::ICON_PICTURE,
            self::SETTING_TITLE => OW::getLanguage()->text('slideshow', 'widget_title')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}