<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.newsfeed.format
 * @since 1.6.0
 */
class NEWSFEED_MFORMAT_Image extends NEWSFEED_FORMAT_Image
{
    public function getViewDir()
    {
        return $this->plugin->getMobileViewDir();
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        if ( !empty($this->vars['info']['route']) )
        {
            $this->vars['info']['url'] = $this->getUrl($this->vars['info']['route']);
        }

        $this->vars['blankImg'] = OW::getThemeManager()->getCurrentTheme()->getStaticUrl() . 'mobile/images/1px.png';
        $this->assign('vars', $this->vars);
    }
}
