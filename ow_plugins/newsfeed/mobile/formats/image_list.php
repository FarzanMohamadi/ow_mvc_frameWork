<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.newsfeed.mobile.format
 * @since 1.6.0
 */
class NEWSFEED_MFORMAT_ImageList extends NEWSFEED_FORMAT_ImageList
{
    const LIST_LIMIT = 8;

    public function getViewDir()
    {
        return $this->plugin->getMobileViewDir();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->vars['blankImg'] = OW::getThemeManager()->getCurrentTheme()->getStaticUrl() . 'mobile/images/1px.png';

        if ( !empty($this->vars['info']['route']) )
        {
            $this->vars['info']['url'] = $this->getUrl($this->vars['info']['route']);
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

        $this->list = array_slice($this->list, 0, $limit);
        $this->assign('list', $this->list);
        $this->assign('vars', $this->vars);

        $count = count($this->list);
        $this->assign('totalCount', $count);
        $count = $count > 4 ? 4 : $count;
        $this->assign('count', $count);
    }
}
