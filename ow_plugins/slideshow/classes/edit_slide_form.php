<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.classes
 * @since 1.4.0
 */
class SLIDESHOW_CLASS_EditSlideForm extends Form
{
    /**
     * Class constructor
     */
    public function __construct( $slide )
    {
        parent::__construct('edit-slide-form');
        
        $lang = OW::getLanguage();
        $this->setAjax(true);
        
        $this->setAction(OW::getRouter()->urlForRoute('slideshow.ajax-edit-slide'));
        
        $IdField = new HiddenField('slideId');
        $IdField->setValue($slide->id);
        $this->addElement($IdField);
        
        $urlField = new TextField('url');
        $urlField->setValue($slide->url);
        $urlField->setLabel($lang->text('slideshow', 'url'));
        $this->addElement($urlField);
        
        $file = new SLIDESHOW_CLASS_UploadSlideField('slide', $slide->widgetId, $slide->id);
        $this->addElement($file);
        
        $titleField = new TextField('title');
        $titleField->setLabel($lang->text('slideshow', 'label'));
        $titleField->setValue($slide->label);
        $this->addElement($titleField);
                
        $submit = new Submit('update');
        $submit->setValue($lang->text('slideshow', 'update_slide'));
        $this->addElement($submit);
    }
}