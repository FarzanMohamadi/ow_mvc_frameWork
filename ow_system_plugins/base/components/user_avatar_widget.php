<?php
/**
 * User avatar widget
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_UserAvatarWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $avatarService = BOL_AvatarService::getInstance();

        $viewerId = OW::getUser()->getId();

        $userId = $paramObj->additionalParamList['entityId'];

        $owner = false;

        $isModerator = (OW::getUser()->isAuthorized('base') || OW::getUser()->isAdmin());

        if ( $viewerId == $userId || $isModerator)
        {
            $owner = true;


            $label = OW::getLanguage()->text('base', 'avatar_change');

            $script =
                '$("#avatar-change-btn").click(function(){
                document.avatarFloatBox = OW.ajaxFloatBox(
                    "BASE_CMP_AvatarChange",
                    { params : { step : 1, userId : '. $userId .' } },
                    { width : 749, title: ' . json_encode($label) . '}
                );
            });

            OW.bind("base.avatar_cropped", function(data){
                if ( data.bigUrl != undefined ) {
                    $("#avatar_console_image").css({ "background-image" : "url(" + data.bigUrl + ")" });
                    window.location = window.location;
                }

                if ( data.modearationStatus )
                {
                    if ( data.modearationStatus != "active" )
                    {
                        $(".ow_avatar_pending_approval").show();
                    }
                    else 
                    {
                        $(".ow_avatar_pending_approval").hide();
                    }
                }
            });
            ';

            OW::getDocument()->addOnloadScript($script);
        }

        $this->assign('owner', $owner);
        $this->assign('isModerator', $isModerator);

        $avatarDto = $avatarService->findByUserId($userId);

        $this->assign('hasAvatar', !empty($avatarDto));
        $moderation = false;

        // approve button
        if ( $isModerator && !empty($avatarDto) && $avatarDto->status == BOL_ContentService::STATUS_APPROVAL )
        {
            $moderation = true;

            $script = ' window.avartar_arrove_request = false;
            $("#avatar-approve").click(function(){
            
                if ( window.avartar_arrove_request == true )
                {
                    return;
                }
                
                window.avartar_arrove_request = true;
                
                $.ajax({
                    "type": "POST",
                    "url": '.json_encode(OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')).',
                    "data": {
                        \'ajaxFunc\' : \'ajaxAvatarApprove\',
                        \'avatarId\' : '.((int)$avatarDto->id).'
                    },
                    "success": function(data){
                        if ( data.result == true )
                        {
                            if ( data.message )
                            {
                                OW.info(data.message);
                            }
                            else
                            {
                                OW.info('.json_encode(OW::getLanguage()->text('base', 'avatar_has_been_approved')).');
                            }
                            
                            $("#avatar-approve").remove();
                            $(".ow_avatar_pending_approval").hide();
                        }
                        else
                        {
                            if ( data.error )
                            {
                                OW.info(data.error);
                            }
                        }
                    },
                    "complete": function(){
                        window.avartar_arrove_request = false;
                    },
                    "dataType": "json"
                });
            }); ';

            OW::getDocument()->addOnloadScript($script);
        }

        //remove avatar
        if ( $owner || $isModerator)
        {
            $label = OW::getLanguage()->text('base', 'remove_avatar_confirm_msg');
            $script = '
            $("#avatar-delete").click(function(){
                let jc = $.confirm('.json_encode($label).');
                jc.buttons.ok.action = function () {
                $.ajax({
                    "type": "POST",
                    "url": '.json_encode(OW::getRouter()->urlFor('BASE_CTRL_Avatar', 'ajaxResponder')).',
                    "data": {
                        \'ajaxFunc\' : \'ajaxRemoveAvatar\',
                        \'userId\' : '.(int)$userId.'
                    },
                    "success": function(data){
                        if ( data.result == true )
                        {
                            if ( data.message )
                            {
                                OW.info(data.message);
                            }
                            else
                            {
                                OW.info('.json_encode(OW::getLanguage()->text('base', 'avatar_has_been_removed')).');
                            }
                            
                            $("#avatar-approve").remove();
                            $(".ow_avatar_pending_approval").hide();
                        }
                        else
                        {
                            if ( data.error )
                            {
                                OW.info(data.error);
                            }
                        }
                    },
                    "complete": function(){
                        location.reload();
                    },
                    "dataType": "json"
                 })
				}
            }); ';

            OW::getDocument()->addOnloadScript($script);
        }

        $avatar = $avatarService->getAvatarUrl($userId, 2, null, false, !($moderation || $owner));
        $avatarImage = $avatar ? $avatar : $avatarService->getDefaultAvatarUrl(2);
        $isDefaultAvatar = $avatar ? false : true;
        $this->assign('isDefaultAvatar', $isDefaultAvatar);
        $this->assign('avatar', $avatarImage);
        $this->assign('avatarImageInfo', BOL_AvatarService::getInstance()->getAvatarInfo($userId, $avatarImage, 'user'));
        $roles = BOL_AuthorizationService::getInstance()->getLastDisplayLabelRoleOfIdList(array($userId));
        $this->assign('role', !empty($roles[$userId]) ? $roles[$userId] : null);

        $userService = BOL_UserService::getInstance();

        $showPresence = true;
        // Check privacy permissions 
        $eventParams = array(
            'action' => 'base_view_my_presence_on_site',
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch ( RedirectException $e )
        {
            $showPresence = false;
        }

        $this->assign('isUserOnline', ($userService->findOnlineUserById($userId) && $showPresence));
        $this->assign('userId', $userId);

        $this->assign('avatarSize', OW::getConfig()->getValue('base', 'avatar_big_size'));

        $this->assign('moderation', $moderation);
        $this->assign('avatarDto', $avatarDto);

        OW::getLanguage()->addKeyForJs('base', 'avatar_has_been_approved');
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'avatar_widget'),
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_PICTURE,
            self::SETTING_FREEZE => true
        );
    }
}