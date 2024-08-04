<?php
/* 
 * Ajax Upload Slide Field Class
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.classes
 * @since 1.4.0
 */
class SLIDESHOW_CLASS_UploadSlideField extends FormElement
{
	private $uniqName;
	
	private $slideId; 
	
    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name, $uniqName, $slideId = null )
    {
        parent::__construct($name);

        $this->uniqName = $uniqName;
        
        $this->slideId = $slideId;
    }

    /**
     * @see FormElement::renderInput()
     *
     * @param array $params
     * @return string
     */
    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        $elementId = 'file_' . $this->uniqName;
        
        $router = OW::getRouter();

        $respUrl = $this->slideId ? 
            $router->urlForRoute('slideshow.update-file', array('slideId' => $this->slideId)) :
            $router->urlForRoute('slideshow.upload-file', array('uniqName' => $this->uniqName));
        
        $params = array('elementId' => $elementId, 'fileResponderUrl' => $respUrl);

        $script = "window.uploadSlideFields = {};
        	window.uploadSlideFields['" . $this->uniqName . "'] = new uploadSlideField(" . json_encode($params) . ");
			window.uploadSlideFields['" . $this->uniqName . "'].init();";

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin("slideshow")->getStaticJsUrl() . 'upload_slide_field.js');
        OW::getDocument()->addOnloadScript($script);

        $fileAttr = array('type' => 'file', 'id' => $elementId);
        $fileField = UTIL_HtmlTag::generateTag('input', $fileAttr);

        $hiddenAttr = array('type' => 'hidden', 'name' => $this->getName(), 'id' => 'hidden_' . $this->uniqName);
        $hiddenField = UTIL_HtmlTag::generateTag('input', $hiddenAttr);

        return '<span class="'. $elementId .'_cont">' . $fileField . '</span>' . $hiddenField;
    }
}