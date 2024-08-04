<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.2
 */
class BASE_CMP_AvatarLibrarySection extends OW_Component
{
    public function __construct( $list, $offset, $count )
    {
        parent::__construct();

        $this->assign('list', $list);
        $this->assign('count', $count);
        $this->assign('loadMore', $count - $offset > BOL_AvatarService::AVATAR_CHANGE_GALLERY_LIMIT);
    }
}