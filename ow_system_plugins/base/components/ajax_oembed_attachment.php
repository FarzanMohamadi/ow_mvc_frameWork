<?php
/**
 * Oembed attachment
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_AjaxOembedAttachment extends OW_Component
{
    protected $oembed = array(), $uniqId;

    public function __construct( $oembed )
    {
        parent::__construct();

        $this->uniqId = FRMSecurityProvider::generateUniqueId('eqattachment');
        $this->assign('uniqId', $this->uniqId);
        $frmEventSecurity = new OW_Event('frmsecurityessentials.on.check.url.embed', array('oembed'=>$oembed));
        OW::getEventManager()->trigger($frmEventSecurity);
        if(isset($frmEventSecurity->getData()['noContent'])){
            $this->assign('noContent',$frmEventSecurity->getData()['noContent']);
        }
        $this->oembed = $oembed;
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        $js->newObject(array('OW_AttachmentItemColletction', $this->uniqId), 'OW_Attachment', array($this->uniqId, $this->oembed));

        OW::getDocument()->addOnloadScript($js);

        return $this->uniqId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('data', $this->oembed);
    }
}
