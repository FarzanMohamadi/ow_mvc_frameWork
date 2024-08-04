<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.5
 */
class BASE_CMP_AjaxFileUpload extends OW_Component
{
    public function __construct( $url = null )
    {
        $userId = OW::getUser()->getId();

        $document = OW::getDocument();
        $plugin = OW::getPluginManager()->getPlugin('base');
        $document->addScript($plugin->getStaticJsUrl() . 'jQueryRotate.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'codemirror.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'upload.js');
        $document->addScriptDeclarationBeforeIncludes(
            UTIL_JsGenerator::composeJsString(';window.ajaxFileUploadParams = {};
                Object.defineProperties(ajaxFileUploadParams, {
                    actionUrl: {
                        value: {$url},
                        writable: false,
                        enumerable: true
                    },
                    maxFileSize: {
                        value: {$size},
                        writable: false,
                        enumerable: true
                    },
                    deleteAction: {
                        value: {$deleteAction},
                        writable: false,
                        enumerable: true
                    }
                });',
                array(
                    'url' => OW::getRouter()->urlForRoute('admin.ajax_upload'),
                    'size' => BOL_FileService::getInstance()->getUploadMaxFilesizeBytes(),
                    'deleteAction' => OW::getRouter()->urlForRoute('admin.ajax_upload_delete')
                )
            )
        );
        $document->addOnloadScript(';window.ajaxFileUploader.init();');

        BOL_FileTemporaryService::getInstance()->deleteUserTemporaryFiles($userId);

        $form = new BASE_CLASS_AjaxUploadForm('user', $userId, $url);
        $this->addForm($form);

        $language = OW::getLanguage();
        $language->addKeyForJs('admin', 'not_all_photos_uploaded');
        $language->addKeyForJs('admin', 'size_limit');
        $language->addKeyForJs('admin', 'type_error');
        $language->addKeyForJs('admin', 'dnd_support');
        $language->addKeyForJs('admin', 'dnd_not_support');
        $language->addKeyForJs('admin', 'drop_here');
        $language->addKeyForJs('admin', 'please_wait');
        $language->addKeyForJs('admin', 'describe_photo');
        $language->addKeyForJs('admin', 'photo_upload_error');
        $language->addKeyForJs('base', 'upload_bad_request_error');
    }
}
