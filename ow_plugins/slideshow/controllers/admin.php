<?php
/**
 * Slideshow administration
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.slideshow.controllers
 * @since 1.4.0
 */
class SLIDESHOW_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    public function uninstall()
    {
        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
        	$service = SLIDESHOW_BOL_Service::getInstance();
        	$list = $service->getAllSlideList();
        	
            if ( $list )
            {
                foreach ( $list as $slide )
                {
                	$service->addSlideToDeleteQueue($slide->id);
                }
            }
        	
            OW::getConfig()->saveConfig('slideshow', 'uninstall_inprogress', 1);
            OW::getEventManager()->trigger(new OW_Event(SLIDESHOW_BOL_Service::EVENT_UNINSTALL_IN_PROGRESS));
            BOL_ComponentAdminService::getInstance()->deleteWidget('SLIDESHOW_CMP_SlideshowWidget');
            
            OW::getFeedback()->info(OW::getLanguage()->text('slideshow', 'plugin_set_for_uninstall'));
            $this->redirect();
        }

        $this->setPageHeading(OW::getLanguage()->text('slideshow', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');
        
        $this->assign('inprogress', (bool) OW::getConfig()->getValue('slideshow', 'uninstall_inprogress'));
        
        $js = new UTIL_JsGenerator();
        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("'.OW::getLanguage()->text('slideshow', 'confirm_delete_plugin').'") ) return false;');
        
        OW::getDocument()->addOnloadScript($js);
    }
}