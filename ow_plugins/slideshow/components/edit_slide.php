<?php
/**
 * Slideshow edit slide component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.components
 * @since 1.4.0
 */
class SLIDESHOW_CMP_EditSlide extends OW_Component
{
    public function __construct( $slideId )
    {
        parent::__construct();
        
        $slide = SLIDESHOW_BOL_Service::getInstance()->findSlideById($slideId);
        $form = new SLIDESHOW_CLASS_EditSlideForm($slide);
        $this->addForm($form);
        
        $script = '$("#btn-edit-slide").click(function(){
            var file = $("#file_'.$slide->widgetId.'");
            
            if ( file.val() != "" ) {
                OW.inProgressNode($(this));
                window.uploadSlideFields["'.$slide->widgetId.'"].startUpload();
            }
            else {
                window.owForms["edit-slide-form"].submitForm();
            }
        });
        
        document.editSlideFloatbox.bind("close", function(){
            OW.unbind("slideshow.upload_file");
            OW.unbind("slideshow.upload_file_complete");
        });
        
        window.owForms["edit-slide-form"].bind("success", function(data){
            $.ajax({
                type: "post",
                url: '.json_encode(OW::getRouter()->urlForRoute('slideshow.ajax-redraw-list', array('uniqName' => $slide->widgetId))).',
                data: {},
                dataType: "json",
                success: function(data){
                    markup = data.markup;
                    document.editSlideFloatbox.close();
                    $("#slides-tbl tbody").html(data.markup);
                }
            });
        });';
        
        OW::getDocument()->addOnloadScript($script);
    }
}