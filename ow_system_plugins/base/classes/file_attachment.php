<?php
/**
 * @package ow_core
 * @since 1.0
 */
class BASE_CLASS_FileAttachment extends OW_Component
{
    private $uid;
    private $inputSelector;
    private $dropAreasSelector;
    private $showPreview;
    private $pluginKey;
    private $multiple;

    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $pluginKey, $uid )
    {
        parent::__construct();
        $this->uid = $uid;
        $this->showPreview = true;
        $this->pluginKey = $pluginKey;
        $this->multiple = true;
    }

    public function getMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple( $multiple )
    {
        $this->multiple = (bool) $multiple;
    }

    public function getInputSelector()
    {
        return $this->inputSelector;
    }

    public function setInputSelector( $inputSelector )
    {
        $this->inputSelector = trim($inputSelector);
    }

    public function getDropAreasSelector()
    {
        return $this->dropAreasSelector;
    }

    public function setDropAreasSelector( $selector )
    {
        $this->dropAreasSelector = trim($selector);
    }

    public function getShowPreview()
    {
        return $this->showPreview;
    }

    public function setShowPreview( $showPreview )
    {
        $this->showPreview = (bool) $showPreview;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = BOL_AttachmentService::getInstance()->getFilesByBundleName($this->pluginKey, $this->uid);
        $itemsArr = array();

        foreach ( $items as $item )
        {
            $itemsArr[] = array('name' => $item['dto']->getOrigFileName(), 'size' => $item['dto']->getSize(), 'dbId' => $item['dto']->getId());
        }

        $params = array(
            'uid' => $this->uid,
            'submitUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'addFile'),
            'deleteUrl' => OW::getRouter()->urlFor('BASE_CTRL_Attachment', 'deleteFile'),
            'showPreview' => $this->showPreview,
            'selector' => $this->inputSelector,
            'dropAreasSelector' => $this->dropAreasSelector,
            'pluginKey' => $this->pluginKey,
            'multiple' => $this->multiple,
            'lItems' => $itemsArr
        );
        $attachmentEvents = OW::getEventManager()->trigger(new OW_Event('attachment.add.parameters',array('pluginKey' => $this->pluginKey,'oldParams'=>$params)));
        if(isset($attachmentEvents->getData()['newParams']))
        {
            if(FRMSecurityProvider::checkPluginActive('frmnewsfeedplus', true)) {
                OW::getLanguage()->addKeyForJs('frmnewsfeedplus', 'file_show');
                OW::getLanguage()->addKeyForJs('frmnewsfeedplus', 'preview_show');
            }
            $params=$attachmentEvents->getData()['newParams'];
        }
        if(FRMSecurityProvider::checkPluginActive('mailbox', true)) {
            OW::getLanguage()->addKeyForJs('mailbox', 'attache_file_delete_button');
        }
        OW::getLanguage()->addKeyForJs('base', 'attachment_is_inprogress');
        OW::getLanguage()->addKeyForJs('base', 'upload_analyze_massage');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('base')->getStaticJsUrl() . 'attachments.js');
        OW::getDocument()->addOnloadScript("owFileAttachments['" . $this->uid . "'] = new OWFileAttachment(" . json_encode($params) . ");");



        $this->assign('data', array('uid' => $this->uid, 'showPreview' => $this->showPreview, 'selector' => $this->inputSelector));
    }
}
