<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.5
 */
class BASE_CMP_AjaxFileUploadButton extends OW_Component
{

    public function __construct($params = array())
    {
        parent::__construct();
        $plugin = OW::getPluginManager()->getPlugin('base');

        $document = OW::getDocument();
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'file_upload.css');
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $id = FRMSecurityProvider::generateUniqueId('addNewFile');
        $this->assign('id', $id);

        OW::getDocument()->addScriptDeclaration(
        UTIL_JsGenerator::composeJsString(
            ';window[{$addNewFile}] = function()
                    {
                        var ajaxUploadPhotoFB = OW.ajaxFloatBox("BASE_CMP_AjaxFileUpload", [], {
                            $title: {$title},
                            addClass: "ow_admin_ajax_file_upload_form"
                        });
                    };', array(
                'addNewFile' => $id,
                'title' => OW::getLanguage()->text('base', 'upload_files'),
                'close_alert' => OW::getLanguage()->text('base', 'close_alert')
            )
        )
    );

    }
}
