$('.version-logs').click(function(){
    header = OW.getLanguageText('frmupdateserver', 'files');
    if(this.attributes['data-header'].value!=""){
        header = this.attributes['data-header'].value;
    }
    openFileManagerFloatBox(this.attributes['data-type'].value, this.attributes['data-key'].value, header);
});

function openFileManagerFloatBox(type, key, header){
    if(header==''){
        header = header = OW.getLanguageText('frmupdateserver', 'files');
    }
    OW.ajaxFloatBox('FRMUPDATESERVER_CMP_FtpFloatBox', {type: type, key: key} , {iconClass: 'ow_ic_add',width:600, title: header});
}

function downloadFile(href, urlPostData, key, version){
    $('#files_data').fadeOut(400, function() {
        hideFilesData();
        text = '<div class="download_waiting_box"><span>'+OW.getLanguageText('frmupdateserver', 'wait_for_download')+'</span><a href="'+href+'" target="_blank">'+OW.getLanguageText('frmupdateserver', 'download_directly')+'</a></div>';
        $(text).appendTo($('#versions-log'));
        $('#versions-log').fadeIn(400);

        $.ajax({
            url: urlPostData,
            type: 'POST',
            data: {key: key, version: version},
            success: function(response) {
                data = JSON.parse(response);
                window.location.href = href;
                returnButton = '<div class="download_waiting_box"><a class="return_from_download" onclick="showFilesData();"><img src="' + data.returnIconUrl + '" />' + data.returnLabel + '</a></div>';
                $(returnButton).prependTo($('#versions-log'));
            },
            'error' : function() {
                returnButton = '<div class="download_waiting_box"><a class="return_from_download" onclick="showFilesData();"><img src="' + data.returnIconUrl + '" />' + data.returnLabel + '</a></div>';
                $(returnButton).prependTo($('#versions-log'));
                window.location.href = href;
            }
        });
    });
}

function hideFilesData(){
    $('#files_data').css('display', 'none');
}

function showFilesData(){
    $('#files_data').css('display', 'block');
    $('.download_waiting_box').css('display', 'none');
}

function loadFolderData(url, type, key, version){
    $('.item_loading').css('display', 'block');
    $('.download_waiting_box').css('display', 'none');
    $.ajax({
        url: url,
        type: 'POST',
        data: {type: type, key: key, version: version},
        success: function(response) {
            data = JSON.parse(response);
            $('#files_data').fadeOut(400, function() {
                $('#files_data').empty();
                $('#files_data').fadeIn(400);

                dirs = data.dirs;
                files = data.files;

                if (files.length == 0 && dirs.length == 0) {
                    $('<p>' + OW.getLanguageText('frmupdateserver', 'no_data_found') + '</p>').appendTo($('#files_data'));
                }else{
                    if(data.returnable) {
                        returnPath = '\'' + data.url + '\',\'\',\'\', \'\'';
                        if (data.version) {
                            returnPath = '\'' + data.url + '\',\'' + type + '\',\'' + key + '\', \'\'';
                        } else if (data.key) {
                            returnPath = '\'' + data.url + '\',\'' + type + '\',\'\', \'\'';
                        }
                        returnButton = '<a class="" onclick="loadFolderData(' + returnPath + ');"><img src="' + data.returnIconUrl + '" />' + data.returnLabel + '</a>';
                        $(returnButton).appendTo($('#files_data'));
                    }

                    for (i = 0; i < files.length; i++) {
                        file = '<a onclick="downloadFile(\''+files[i].href+'\',\''+data.urlOfDownload+'\',\''+files[i].key+'\',\''+files[i].version+'\')" ><img src="'+files[i].icon+'" />'+files[i].name+'</a>';
                        $(file).appendTo($('#files_data'));
                    }

                    for (i = 0; i < dirs.length; i++) {
                        dir = '<a onclick="loadFolderData(\''+data.url+'\',\''+dirs[i].type+'\',\''+dirs[i].key+'\', \''+dirs[i].version+'\');"><img src="'+dirs[i].icon+'" />'+dirs[i].name+' '+dirs[i].time +'</a>';
                        $(dir).appendTo($('#files_data'));
                    }
                    $('.floatbox_title span')[0].innerHTML = data.headerLabel;
                }

                $('.item_loading').css('display', 'none');

            });
        },
        'error' : function() {
            OWM.message('err');
        }
    });
}

function loadDocFolderData(url, path){
    $('.item_loading').css('display', 'block');
    $('.download_waiting_box').css('display', 'none');
    $.ajax({
        url: url,
        type: 'POST',
        data: {path: path, publicFile: true},
        success: function(response) {
            data = JSON.parse(response);
            $('#files_data').fadeOut(400, function() {
                $('#files_data').empty();
                $('#files_data').fadeIn(400);

                dirs = data.dirs;
                files = data.files;

                if (files.length == 0 && dirs.length == 0) {
                    $('<p>' + OW.getLanguageText('frmupdateserver', 'no_data_found') + '</p>').appendTo($('#files_data'));
                }else{
                    if(data.returnable) {
                        returnButton = '<a class="" onclick="loadDocFolderData(\''+url+'\',\'' + data.returnUrl + '\');"><img src="' + data.returnIconUrl + '" />' + data.returnLabel + '</a>';
                        $(returnButton).appendTo($('#files_data'));
                    }

                    for (i = 0; i < files.length; i++) {
                        file = '<a onclick="downloadFile(\''+files[i].href+'\',\''+data.urlOfDownload+'\',\''+files[i].key+'\',\''+files[i].version+'\')" ><img src="'+files[i].icon+'" />'+files[i].name+'</a>';
                        $(file).appendTo($('#files_data'));
                    }

                    for (i = 0; i < dirs.length; i++) {
                        dir = '<a onclick="loadDocFolderData(\''+url+'\',\''+dirs[i].path+'\');"><img src="'+dirs[i].icon+'" />'+dirs[i].name +'</a>';
                        $(dir).appendTo($('#files_data'));
                    }

                    $('.floatbox_title span')[0].innerHTML = data.headerLabel;
                }

                $('.item_loading').css('display', 'none');

            });
        },
        'error' : function() {
            OWM.message('err');
        }
    });
}

function pluginCategoryFilter() {
    var selectedId=$( "#pluginCategorySelectBox option:selected" )[0].id;
    var delayInMilliseconds = 400; //1 second
    if(selectedId==0){
        setTimeout(function() {
            $(".plugin-information").css('opacity', '0');
            setTimeout(function() {
                $(".plugin-information").css('display', 'inline-block');
                setTimeout(function() {
                    $(".plugin-information").css('opacity', '1');
                }, delayInMilliseconds);
            }, delayInMilliseconds);
        }, delayInMilliseconds);
        $(".plugin-information").css('opacity', '1');
    }else{
        $(".plugin-information").css('opacity', '0');
        setTimeout(function() {
            $(".plugin-information").css('display', 'none');
            $(".plugin-information.category_"+ selectedId).css('display', 'inline-block');
            setTimeout(function() {
                $(".plugin-information.category_"+ selectedId).css('opacity', '1');
            }, delayInMilliseconds);
        }, delayInMilliseconds);


    }
}