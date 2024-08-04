<?php
/**
 * @package ow_core
 * @since 1.0
 */
class BASE_CLASS_Attachment extends OW_Component
{
    private $uid;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $pluginKey, $uid, $buttonId )
    {
        parent::__construct();
        $language = OW::getLanguage();
        $this->uid = $uid;
        $previewContId = 'attch_preview_' . $this->uid;

        $params = array(
            'uid' => $uid,
            'previewId' => $previewContId,
            'buttonId' => $buttonId,
            'pluginKey' => $pluginKey,
            'addPhotoUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addPhoto'),
            'langs' => array(
                'attchLabel' => $language->text('base', 'attch_attachment_label')
            )
        );

        $this->assign('previewId', $previewContId);
        OW::getLanguage()->addKeyForJs('base', 'upload_analyze_massage');
        OW::getLanguage()->addKeyForJs('base', 'delete');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
        OW::getDocument()->addOnloadScript("window.owPhotoAttachment['" . $uid . "'] =  new OWPhotoAttachment(" . json_encode($params) . ");");
    }
}
