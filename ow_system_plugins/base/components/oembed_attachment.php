<?php
/**
 * Oembed attachment
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_OembedAttachment extends OW_Component
{
    protected $uniqId, $oembed;
    
    public function __construct( array $oembed, $delete = false )
    {
        parent::__construct();

        $this->oembed = $oembed;
        if(isset($this->oembed['thumbnail_url'])){
            $checkImg = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_URL_IMAGE_ADD_ON_CHECK_LINK, array('img' => $this->oembed['thumbnail_url'])));
            if(isset($checkImg->getData()['wrong']) && $checkImg->getData()['wrong']){
                $this->oembed['thumbnail_url'] = null;
            }
        }
        //deleting image inside comment without deleting the comment disabled
        $this->assign('delete',$delete = false);
        $this->uniqId = FRMSecurityProvider::generateUniqueId("oe-");
        $this->assign("uniqId",$this->uniqId);
    }

    public function setDeleteBtnClass( $class )
    {
        $this->assign('deleteClass', $class);
    }

    public function setContainerClass( $class )
    {
        $this->assign('containerClass', $class);
    }

    public function initJs()
    {
        $js = UTIL_JsGenerator::newInstance();
        
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($this->oembed["html"], "autoplay", 1);
        $code = BOL_TextFormatService::getInstance()->addVideoCodeParam($code, "play", 1);
        
        $js->addScript('$(".ow_oembed_video_cover", "#" + {$uniqId}).click(function() { '
                . '$(".two_column", "#" + {$uniqId}).addClass("ow_video_playing"); '
                . '$(".attachment_left", "#" + {$uniqId}).html({$embed});'
                . 'OW.trigger("base.comment_video_play", {});'
                . 'return false; });', array(
            "uniqId" => $this->uniqId,
            "embed" => $code
        ));
        
        OW::getDocument()->addOnloadScript($js);
    }
    
    public function render()
    {
        if(isset($this->oembed['url'])) {
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $this->oembed['url'])));
            if (isset($stringRenderer->getData()['string'])) {
                $this->oembed['url'] = $stringRenderer->getData()['string'];
            }
        }
        if(isset($this->oembed['href'])) {
            $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array('string' => $this->oembed['href'])));
            if (isset($stringRenderer->getData()['string'])) {
                $this->oembed['href'] = $stringRenderer->getData()['string'];
            }
        }
        if ( isset($this->oembed["type"]) && $this->oembed["type"] == "video" && !empty($this->oembed["html"]) )
        {
            $this->initJs();
        }
        
        $this->assign('data', $this->oembed);

        return parent::render();
    }

    public function getOembed()
    {
        return $this->oembed;
    }
}