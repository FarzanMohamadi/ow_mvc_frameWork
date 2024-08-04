function searchUserList(url) {
    var searchValue = $('#searchValue')[0].value;
    var filter = "?searchValue="+searchValue;
    url = url + filter;
    window.location = url;
}

function GROUPS_InitInviteButton(options)
{
    var floatBox;
    $('#GROUPS_InviteLink,#GROUP_Users_Invite').click(
        function()
        {
            if(options.defaultSearch)
            {
                userIdList = options.userList;
                floatBox = OW.ajaxFloatBox('BASE_CMP_AvatarUserListSelect', [options.userList],
                    {
                        width:600,
                        height:385,
                        iconClass: 'ow_ic_user',
                        title: options.floatBoxTitle
                    });
            }else {
                userIdList = new Array();
                floatBox = OW.ajaxFloatBox('BASE_CMP_SearchUserListSelect', [userIdList, 'groups', options.groupId],
                    {
                        width: 600,
                        height: 385,
                        iconClass: 'ow_ic_user'
                    });
                OW.trigger('base.search_user_list_select');
            }
        }
    );
    OW.bind('base.avatar_user_list_select',
        function(list)
        {
            if($("#SearchUserForm").length && $("#SearchUserForm input[name=entityType]").val()!='groups')
            {
                return;
            }
            floatBox.close();
            $.ajax({
                type: 'POST',
                url: options.inviteResponder,
                data: {"groupId": options.groupId, "userIdList": JSON.stringify(list), "allIdList": JSON.stringify(userIdList)},
                dataType: 'json',
                success : function(data)
                {
                    if( data.messageType == 'error' )
                    {
                        OW.error(data.message);
                        document.location.reload();
                    }
                    else if( data.directAdd ){
                        document.location = data.url;
                    }
                    else
                    {
                        OW.info(data.message);
                        document.location.reload();
                        userIdList = data.allIdList;
                    }
                }
            });
        }
    );
}