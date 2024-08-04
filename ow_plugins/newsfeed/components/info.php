<?php
/**
 * Likes Widget
 *
 *  Author: Farzan Mohammadi (milad.heshmati@gmail.com)
 * @package ow_plugins.newsfeed.components
 * @since 1.0
 */
class NEWSFEED_CMP_Info extends OW_Component
{
    public function __construct( $followersCount, $followingCount, $postsCount  )
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('newsfeed')->getStaticCssUrl().'newsfeed.css');
        parent::__construct();
        
        if ( $followersCount === null || $followingCount === null || $postsCount === null )
        {
            return;
        }
        $this->assign('followersCount', $followersCount);
        $this->assign('followingCount', $followingCount);
        $this->assign('postsCount', $postsCount);
    }
}