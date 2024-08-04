<?php
/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.7.5
 */
class BASE_CLASS_AjaxUploadForm extends Form
{
    public function __construct( $entityType, $entityId, $albumId = null, $albumName = null, $albumDescription = null, $url = null )
    {
        parent::__construct('ajax-upload');
        
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(OW::getRouter()->urlForRoute('admin.ajax_upload_submit'));
        $this->bindJsFunction('success', 
            UTIL_JsGenerator::composeJsString('function( data )
            {
                if ( data )
                {
                    if ( !data.result )
                    {
                        if ( data.msg )
                        {
                            OW.error(data.msg);
                        }
                        else
                        {
                            OW.getLanguageText("admin", "photo_upload_error");
                        }
                    }
                    else
                    {
                        var url = {$url};
                        
                        if ( url )
                        {
                            window.location.href = url;
                        }
                        else if ( data.url )
                        {
                            window.location.href = data.url;
                        }
                    }
                }
                else
                {
                    OW.error("Server error");
                }
            }', array(
                'url' => $url
            ))
        );

        $submit = new Submit('submit');
        $submit->addAttribute('class', 'ow_ic_submit ow_positive');
        $this->addElement($submit);
    }
}
