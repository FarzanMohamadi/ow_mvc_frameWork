<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.controllers
 * @since 1.4.0
 */

class SLIDESHOW_CTRL_Slide extends OW_ActionController
{
	public function __construct()
	{
		parent::__construct();
	}

	public function uploadFile( $params )
	{
	    $uniqName = isset($params['uniqName']) ? trim($params['uniqName']) : null;
	    
	    $formElementId = 'file_' . $uniqName;
	    $language = OW::getLanguage();
	    
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
        
	    $result = array('input_id' => $formElementId, 'error' => true, 'message' => '');
	    
	    if ( !OW::getRequest()->isPost() )
	    {
	        $result['message'] = "Not authorized";
	    }
	    else
	    {
            $service = SLIDESHOW_BOL_Service::getInstance();
            
            if ( empty($_FILES['slide']) )
            {
                $result['message'] = "File not selected";
            }
            else 
            {
                $slide = $_FILES['slide'];
    
                if ( is_uploaded_file($slide['tmp_name']) )
                {
                    $iniValue = floatval(ini_get('upload_max_filesize'));
                    $maxSize = 1024 * 1024 * ($iniValue ? $iniValue : 4);
    
                    if ( !UTIL_File::validateImage($slide['name']) )
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_extension_not_allowed');
                    }
                    else if ( $slide['size'] > $maxSize )
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_max_filesize_error');
                    }
                    else if ( $slideId = $service->addTmpSlide($uniqName, $slide) )
                    {
                        $result['slide_id'] = $slideId;
                        $result['error'] = false;
                    }
                    else
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_error'); 
                    }
                }
            }
	    }

        exit("<script>parent.window.OW.trigger('slideshow.upload_file_complete', [" . json_encode($result) . "]);</script>");
    }
    
    public function updateFile( $params )
    {
        if ( !OW::getUser()->isAdmin() )
        {
            throw new AuthenticationException();
        }
        
    	$slideId = isset($params['slideId']) ? trim($params['slideId']) : null;
    	$service = SLIDESHOW_BOL_Service::getInstance();
    	
    	$slide = $service->findSlideById($slideId);
    	
    	$result = array('error' => true, 'message' => '');
    	
    	if ( $slide )
    	{
            $formElementId = 'file_' . $slide->widgetId;
            $language = OW::getLanguage();
        
            if ( empty($_FILES['slide']) )
            {
                $result['message'] = "File not selected";
            }
            else 
            {
                $file = $_FILES['slide'];
    
                if ( is_uploaded_file($file['tmp_name']) )
                {
                    $iniValue = floatval(ini_get('upload_max_filesize'));
                    $maxSize = 1024 * 1024 * ($iniValue ? $iniValue : 4);
    
                    if ( !UTIL_File::validateImage($file['name']) )
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_extension_not_allowed');
                    }
                    else if ( $file['size'] > $maxSize )
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_max_filesize_error');
                    }
                    else if ( $service->updateSlideImage($slide->id, $file) )
                    {
                        $result['slide_id'] = $slideId;
                        $result['error'] = false;
                        $result['input_id'] = $formElementId;
                    }
                    else
                    {
                        $result['message'] = $language->text('slideshow', 'upload_file_error'); 
                    }
                }
            }
        }

        exit("<script>parent.window.OW.trigger('slideshow.upload_file_complete', [" . json_encode($result) . "]);</script>");
    }
}