<?php
/**
 * Smarty user avatar function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_function_user_avatar( $params, $smarty )
{
    if( empty( $params['userId'] ) )
    {
        return '_EMPTY_USER_ID_';
    }

    $decoratorParams = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($params['userId']));

    if( empty( $decoratorParams ) )
    {
        return '_USER_NOT_FOUND_';
    }

    return OW::getThemeManager()->processDecorator('avatar_item', $decoratorParams[$params['userId']]);
}