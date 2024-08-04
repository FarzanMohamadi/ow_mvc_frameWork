<?php
/**
 * Slideshow custom settings component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.components
 * @since 1.4.0
 */
class SLIDESHOW_CMP_CustomSettings extends OW_Component
{
    public function __construct( $uniqName )
    {
        parent::__construct();

        $service = SLIDESHOW_BOL_Service::getInstance();
        
        $slides = $service->getSlideList($uniqName);

        $markup = '';
        if ( $slides )
        {
            foreach ( $slides as $slide )
            {
            	$cmp = new SLIDESHOW_CMP_Slide($slide);
                $markup .= $cmp->render();
            }
        }
        
        $this->assign('markup', $markup);

        $baseJsDir = OW::getPluginManager()->getPlugin("base")->getStaticJsUrl();
        OW::getDocument()->addScript($baseJsDir . "jquery-ui.min.js");
        
        $script = '$("#btn-add-image").click(function(){
            document.addSlideFloatbox = OW.ajaxFloatBox(
        		"SLIDESHOW_CMP_AddSlide", 
        		{uniqName: '.json_encode($uniqName).'}, 
        		{width:422, iconClass: "ow_ic_add", title: '.json_encode(OW::getLanguage()->text('slideshow', 'add_image')).'}
        	);
        });';
        
        $script .= '$("#slides-tbl").on("mouseover", "tr", function(){ 
            $(this).find(".ow_slider_actions a").show(); 
        });
        
        $("#slides-tbl").on("mouseout", "tr", function(){
            $(this).find(".ow_slider_actions a").hide(); 
        });
        
        $("#slides-tbl").on("click", "a", function(){
            if ( $(this).hasClass("action_delete_slide") )
            {
                var url = '.json_encode(OW::getRouter()->urlForRoute('slideshow.ajax-delete-slide')).';
                
                if ( confirm(' . json_encode(OW::getLanguage()->text('slideshow', 'delete_slide_confirm')) . ') ){
                    $.ajax({
		                type: "post",
		                url: url,
		                data: { slideId: $(this).attr("rel") },
		                dataType: "json",
		                success: function(data){
		                    markup = data.markup;
		                    $("#slides-tbl tbody").html(data.markup);
		                }
		            });
                }
            }
            else if ( $(this).hasClass("action_edit_slide") )
            {
                var slideId = $(this).attr("rel");
                
                document.editSlideFloatbox = OW.ajaxFloatBox(
                    "SLIDESHOW_CMP_EditSlide", 
                    {slideId: slideId}, 
                    {width:422, iconClass: "ow_ic_edit", title: '.json_encode(OW::getLanguage()->text('slideshow', 'edit_slide')).'}
                );
            }
        });';
        
        $script .= '$("#slides-tbl tbody").sortable({
            cursor: "move",
            update: function(event, ui){
                if ( ui.sender ) { return; }

                var set = {};

                $("tr", "#slides-tbl tbody").each(function(i){
                    set["slide-list["+i+"]"] = $(this).attr("rel");
                });

                var url = '.json_encode(OW::getRouter()->urlForRoute('slideshow.ajax-reorder-list')).';
                $.post(url, set);
            }
        }).disableSelection();';
        
        OW::getDocument()->addOnloadScript($script);
    }
}