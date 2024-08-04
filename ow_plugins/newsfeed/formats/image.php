<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.newsfeed.format
 * @since 1.6.0
 */
class NEWSFEED_FORMAT_Image extends NEWSFEED_CLASS_Format
{
    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "image" => null,
            "status" => null,
            "url" => null,
            "info" => null,
            "title" => null,
        );

        $this->vars = array_merge($defaults, $this->vars);
        $this->vars['url'] = $this->getUrl($this->vars['url']);
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $this->vars['image'])));
        if(isset($stringRenderer->getData()['string'])){
            $this->vars['image'] = $stringRenderer->getData()['string'];
        }

        $this->assign('vars', $this->vars);
    }
}