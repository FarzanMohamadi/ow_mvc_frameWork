<?php 



$service = SLIDESHOW_BOL_Service::getInstance();
$list = $service->getAllSlideList();
            
if ( $list )
{
    foreach ( $list as $slide )
    {
        $service->addSlideToDeleteQueue($slide->id);
    }
}

BOL_ComponentAdminService::getInstance()->deleteWidget('SLIDESHOW_CMP_SlideshowWidget');