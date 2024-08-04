var uploadFileIntoGroupFormComponent;

function showUploadFileIntoGroupForm($groupId){
    uploadFileIntoGroupFormComponent = OW.ajaxFloatBox('FRMGROUPSPLUS_CMP_FileUploadFloatBox', {iconClass: 'ow_ic_add',groupId: $groupId})
}

function closeUploadFileIntoGroupForm(){
    if(uploadFileIntoGroupFormComponent){
        uploadFileIntoGroupFormComponent.close();
    }
}

function searchGroup(url) {
    var searchT = $('#searchTitle')[0].value;
    var categoryS = $('#categoryStatus')[0].value;
    url = url + "?searchTitle="+searchT+"&categoryStatus="+categoryS;
    if($('#status').length) {
        var approveS = $('#status')[0].value;
        url+="&status="+approveS;
    }
    window.location = url;
}

function searchGroupByEnter(url){
    if (window.event.key === "Enter" ) {
        searchGroup(url);
    }
}

function searchGroupFiles() {
    var url = $('#original-search-group-files')[0].href;
    var searchT = $('#search-group-files')[0].value;
    url = url + "?searchTitle="+searchT;
    window.location = url;
}

function SearchGroupFilesByEnter() {
    e = window.event;
    if (e.keyCode == 13) {
        document.getElementById('search-group-files_button').click();
    }
}

function revokeButtonClickListener(userId, groupId, url) {
    $.ajax({
        type: "POST",
        url: url,
        data: {'userId':userId,
            'groupId':groupId
        },
        success:function (e) {
            $('#pending_avatar'+userId+'1').hide();
            $('#pending_avatar'+userId+'0').hide();
            $('#pending_avatar'+userId).hide();
            OW.info(OW.getLanguageText('frmgroupsplus', 'revoke_user_invitation_success_message'));
        },
        error:function (r) {
                OW.error(OW.getLanguageText('frmgroupsplus', 'revoke_user_invitation_failed_message'))
            }
    });
}