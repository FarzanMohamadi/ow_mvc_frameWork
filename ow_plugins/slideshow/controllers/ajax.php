<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.controllers
 * @since 1.4.0
 */
 
class SLIDESHOW_CTRL_Ajax extends OW_ActionController
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function addSlide()
    {
    	if ( !OW::getRequest()->isAjax() )
    	{
            exit(json_encode(array('result' => false)));
    	}
    	
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
    	
    	if ( !empty($_POST['uniqName']) )
    	{
            $title = !empty($_POST['title']) ? htmlspecialchars($_POST['title']) : null;
            $url = !empty($_POST['url']) ? htmlspecialchars($_POST['url']) : null;
            
            $service = SLIDESHOW_BOL_Service::getInstance();
            $slideId = (int) $_POST['slideId'];
            $service->addSlide($slideId, $title, $url);
            
            exit(json_encode(array('result' => true, 'slideId' => $slideId)));
    	}
        
        exit;
    }
    
    public function editSlide()
    {
        if ( !OW::getRequest()->isAjax() )
        {
            exit(json_encode(array('result' => false)));
        }
        
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
        
        if ( !empty($_POST['slideId']) )
        {
            $title = !empty($_POST['title']) ? htmlspecialchars($_POST['title']) : null;
            $url = !empty($_POST['url']) ? htmlspecialchars($_POST['url']) : null;
            
            $service = SLIDESHOW_BOL_Service::getInstance();
            $slideId = (int) $_POST['slideId'];
            $slide = $service->findSlideById($slideId);
            
            if ( !$slide )
            {
                exit(json_encode(array('result' => false)));
            }
            
            $slide->label = $title;
            $slide->url = $url;
            
            $service->updateSlide($slide);
            
            exit(json_encode(array('result' => true, 'slideId' => $slideId)));
        }
        
        exit;
    }
    
    public function redrawList( $params )
    {
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
        
        $uniqName = $params['uniqName'];
        
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
        
        exit(json_encode(array('markup' => $markup)));
    }
    
    public function deleteSlide( )
    {
    	if ( !OW::getUser()->isAdmin() )
    	{
    		throw new AuthenticationException();
    	}
    	
        $slideId = $_POST['slideId'];
        
        $service = SLIDESHOW_BOL_Service::getInstance();
        
        $slide = $service->findSlideById($slideId);
        $service->deleteSlideById($slideId);
                
        $slides = $service->getSlideList($slide->widgetId);

        $markup = '';
        if ( $slides )
        {
            foreach ( $slides as $slide )
            {
                $cmp = new SLIDESHOW_CMP_Slide($slide);
                $markup .= $cmp->render();
            }
        }
        
        exit(json_encode(array('markup' => $markup)));
    }
    
    public function reorderList( $params )
    {
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
        
        $service = SLIDESHOW_BOL_Service::getInstance();
        
        if ( !empty($_POST['slide-list']) )
        {
            foreach ( $_POST['slide-list'] as $order => $id )
            {
                $slide = $service->findSlideById($id);
                if ( empty($slide) )
                {
                    continue;
                }
                
                $slide->order = $order + 1;
                $service->updateSlide($slide);
            }
        }
        
        exit;
    }
}