<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.classes
 * @since 1.4.0
 */
class SLIDESHOW_CLASS_AddSlideForm extends Form
{
    /**
     * Class constructor
     */
    public function __construct( $uniqName )
    {
        parent::__construct('add-slide-form');
        
        $lang = OW::getLanguage();
        $this->setAjax(true);
        
        $this->setAction(OW::getRouter()->urlForRoute('slideshow.ajax-add-slide'));
        
        $IdField = new HiddenField('uniqName');
        $IdField->setValue($uniqName);
        $this->addElement($IdField);
        
        $slideId = new HiddenField('slideId');
        $this->addElement($slideId);

        $urlField = new TextField('url');
        $urlField->setLabel($lang->text('slideshow', 'url'));
        $this->addElement($urlField);
        
        $file = new SLIDESHOW_CLASS_UploadSlideField('slide', $uniqName);
        $this->addElement($file);
        
        $titleField = new TextField('title');
        $titleField->setLabel($lang->text('slideshow', 'label'));
        $this->addElement($titleField);
                
        $submit = new Submit('add');
        $submit->setValue($lang->text('slideshow', 'add_slide'));
        $this->addElement($submit);
    }
}