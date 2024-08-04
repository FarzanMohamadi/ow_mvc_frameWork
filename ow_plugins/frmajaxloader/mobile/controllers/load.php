<?php
/**
 * frmajaxloader
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmajaxloader
 * @since 1.0
 */

class FRMAJAXLOADER_MCTRL_Load extends FRMAJAXLOADER_CTRL_Load
{
    /**
     *
     * @param array $actionList
     * @param array $data
     * @return NEWSFEED_CMP_FeedList
     */
    protected function createFeedList( $actionList, $data )
    {
        return OW::getClassInstance("NEWSFEED_MCMP_FeedList", $actionList, $data);
    }

}

