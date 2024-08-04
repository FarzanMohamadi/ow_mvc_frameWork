function showAddGroupInvitationLinkForm(confirmText, groupId, addLinkUrl, isLinkHistoryPage = false){
    var answer = $.confirm(confirmText);
    answer.buttons.ok.action = function() {

        $.ajax({
            type: 'POST',
            url: addLinkUrl,
            data: {
                id:groupId
            },
            dataType: 'json',
            success : function(data){
                if(data.result && data.link!==null && data.link!==''){
                    if(isLinkHistoryPage){
                        OW.info(data.message);
                        window.location.reload();
                    } else{
                        appendLink(data.link, data.message);
                    }
                }
                else{
                    OW.error(data.message);
                }
            },
            error : function( XMLHttpRequest, textStatus, errorThrown ){
                OW.error('Ajax Error: '+textStatus+'!');
                throw textStatus;
            }
        });
    };
}

function appendLink(link, resultMessage){
    var linkElement = document.getElementById("frmgroupsinvitationlink_link");
    if(!!linkElement){
        linkElement.innerHTML = linkBriefer(link);
        linkElement.setAttribute('href', link);
    } else{
        var linkContainer = document.getElementById("frmgroupsinvitationlink_active_link_container");
        linkContainer.innerHTML = `<tr class="ow_alt2 ow_tr_first" id="frmgroupsinvitationlink_link_section">
            <td style="max-width: 100px !important;">
                <a id="frmgroupsinvitationlink_link" href="` + link + `" target="_blank">` + linkBriefer(link) + `</a>
            </td>
        </tr>`
    }
    OW.info(resultMessage);
}

function linkBriefer(link){
    var mainSegments = link.split('//');
    var domain = mainSegments[1].split('/')[0];
    return domain + '/...' + link.substr(-5);
}
