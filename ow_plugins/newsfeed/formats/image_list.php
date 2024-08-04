<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.newsfeed.format
 * @since 1.6.0
 */
class NEWSFEED_FORMAT_ImageList extends NEWSFEED_CLASS_Format
{
    const LIST_LIMIT = 4;

    protected $list = array();

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $defaults = array(
            "iconClass" => null,
            "title" => '',
            "description" => '',
            "status" => null,
            "list" => null,
            "info" => null,
            "more" => null
        );

        $this->vars = array_merge($defaults, $this->vars);

        if ( empty($this->vars['list']) )
        {
            $this->setVisible(false);
            return;
        }

        // prepare image list
        foreach ( $this->vars['list'] as $id => $image )
        {
            if(!empty($image['image'])) {
                $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('string' => $image['image'])));
                if(isset($stringRenderer->getData()['string'])){
                    $image['image'] = $stringRenderer->getData()['string'];
                }
            }

            $image['title'] = !empty($image['title']) ? $image['title'] : null;
            if(!empty($image['url'])) {
                $image['url'] = $this->getUrl($image['url']);
            }

            $this->list[$id] = $image;
        }

        $limit = self::LIST_LIMIT;

        // prepare view more url
        if ( !empty($this->vars['more']) )
        {
            $this->vars['more']['url'] = $this->getUrl($this->vars['more']);
            if ( !empty($this->vars['more']['limit']) )
            {
                $limit = $this->vars['more']['limit'];
            }
        }

        $this->assign('list', array_slice($this->list, 0, $limit));
        $this->assign('vars', $this->vars);
    }
}
