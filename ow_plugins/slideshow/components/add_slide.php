<?php
/**
 * Slideshow add slide component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.components
 * @since 1.4.0
 */
class SLIDESHOW_CMP_AddSlide extends OW_Component
{
    public function __construct( $uniqName )
    {
        parent::__construct();
        
        $form = new SLIDESHOW_CLASS_AddSlideForm($uniqName);
        $this->addForm($form);
        
        $script = '$("#btn-add-slide").click(function(){
        	var file = $("#file_'.$uniqName.'");
        	
        	if ( file.val() != "" ) {
        		OW.inProgressNode($(this));
            	window.uploadSlideFields["'.$uniqName.'"].startUpload();
    		}
        });
        
        document.addSlideFloatbox.bind("close", function(){
            OW.unbind("slideshow.upload_file");
            OW.unbind("slideshow.upload_file_complete");
        });
        
        window.owForms["add-slide-form"].bind("success", function(data){
            $.ajax({
                type: "post",
                url: '.json_encode(OW::getRouter()->urlForRoute('slideshow.ajax-redraw-list', array('uniqName' => $uniqName))).',
                data: {},
                dataType: "json",
                success: function(data){
                    markup = data.markup;
                    document.addSlideFloatbox.close();
                    $("#slides-tbl tbody").html(data.markup);
                }
            });
        });';
        
        OW::getDocument()->addOnloadScript($script);
    }
}