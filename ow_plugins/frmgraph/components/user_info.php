<?php
/**
 * FRM Graph
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmgraph
 * @since 1.0
 */
class FRMGRAPH_CMP_UserInfo extends OW_Component
{
    public function __construct( $username )
    {
        parent::__construct();

        $userService = BOL_UserService::getInstance();
        $user = $userService->findByUsername($username);
        $userData = array(
            'username'=>$username,
            'fullname'=>$userService->getDisplayName($user->id),
            'email'=>$user->email,
            'url'=>$userService->getUserUrl($user->id),
            'avatarUrl'=>BOL_AvatarService::getInstance()->getAvatarUrl($user->id)
        );

        $this->assign('userData', $userData);
    }
}