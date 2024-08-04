<?php
/**
 * Forum group class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_plugins.forum.mobile.components
 * @since 1.0
 */
class FORUM_MCMP_ForumGroup extends OW_MobileComponent
{
    /**
     * Class constructor
     * 
     * @param array $params
     *      array topics
     */
    public function __construct(array $params = array())
    {
        parent::__construct();

        $topics = !empty($params['topics']) 
            ? $params['topics'] 
            : array();

        // assign view variables
        $this->assign('topics', $topics);

        $enableAttachments = OW::getConfig()->getValue('forum', 'enable_attachments');
        $this->assign('enableAttachments', $enableAttachments);

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('forum')->getStaticCssUrl().'forum.css');
    }
}