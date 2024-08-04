var attachmentInProgress=0;
var attachmentxhrArray=[];
var OWFileAttachment = function(params) {
    $.extend(this, params);
    this.$context = $('#' + this.uid);
    this.$previewCont = $('.ow_file_attachment_preview', this.$context);
    this.inputCont = this.selector ? $(this.selector) : $('a.attach', this.$context);
    this.captionInput = this.captionInput ? this.captionInput : false;
    this.dropAreasSelector = this.dropAreasSelector ? this.dropAreasSelector : '';
    this.caption = '';

    var self = this;
    var items = {};
    var itemId = 1;
    var refreshClasses = function() {
        var itemIndex = 1;
        $.each(items,
                function(index, data) {
                    data['html'].removeClass('ow_file_attachment_block1').removeClass('ow_file_attachment_block2').addClass('ow_file_attachment_block' + itemIndex);
                    itemIndex = itemIndex == 1 ? 2 : 1;
                }
        );
    };
    function getAttachmentExtension(filename){
        var ext = '';
        if(filename.lastIndexOf('.')>0){
            ext = filename.substr(filename.lastIndexOf('.')+1);
        }
        if(ext===''){
            var match = document.cookie.match(new RegExp('(^| )UsingMobileApp=([^;]+)'));
            if (match && match[2]==='android')
            {
                ext = 'mp3';
            }
        }
        return ext;
    }
    this.addItem = function(data, loader, customDeleteUrl, customConfirmation) {
        var ext = getAttachmentExtension(data["name"]);
        var defaulthtml = $('<div><div class="ow_file_attachment_info '+ ext.toLowerCase() +' ">' +
            '<div class="ow_file_attachment_name"><span class="ow_file_attachment_string">' + data.name + ' </span><span class="ow_file_attachment_size" style="display: inline-block;">(' + data.size + 'KB)</span></div>' +
            '<div class="ow_file_attachment_preload" style="display:' + (loader ? 'block' : 'none') + ';"></div>' +
            '<a href="javascript://" class="ow_file_attachment_close"></a>' +
            '</div></div>');
        if(self.photoPreviewFeature!=undefined && self.photoPreviewFeature==true)
        {
            var previewExtensions=['jpg','jpeg','png','gif','bmp','mp4', "mp3","aac","mov","ogg"];
            if(self.previewExtensions!=undefined)
            {
                previewExtensions = self.previewExtensions;
            }
            if(previewExtensions.includes(ext.toLowerCase()))
            {
                data['html'] = $('<div><div class="ow_file_attachment_info '+ ext.toLowerCase() +'">' +
                    '<div class="ow_file_attachment_name"><span class="ow_file_attachment_string">' + data.name + ' </span><span class="ow_file_attachment_size" style="display: inline-block;">(' + data.size + 'KB)</span></div>' +
                    '<div class="ow_file_attachment_preload" style="display:' + (loader ? 'block' : 'none') + ';"></div>' +
                    '<a href="javascript://" class="ow_file_attachment_close"></a>' +
                    '<a href="javascript://" class="ow_file_attachment_photo_preview"></a>' +
                    '<span id="span_preview_'+data['id']+'" class="ow_file_attachment_photo_not_preview_label">'+OW.getLanguageText('frmnewsfeedplus', 'preview_show')+'</span>'+
                    '</div></div>');
            }
            else{
                data['html'] = defaulthtml;
            }
        }
        else {
            data['html'] = defaulthtml
        }

        if (typeof customDeleteUrl != "undefined") 
        {
            data['customDeleteUrl'] = customDeleteUrl;
        }

        self.$previewCont.append(data['html']);
        OW.trigger('base.attachment_rendered', {'data' : data}, this);
 
        $('.ow_file_attachment_close', data['html']).bind('click', function() {
            var confirmed = true;
 
            if (typeof customConfirmation != "undefined") {
                confirmed = confirm(customConfirmation);
            }
 
            if (confirmed) {
                self.deleteItem(data['id'], customDeleteUrl);
            }
        });

        if(self.photoPreviewFeature!=undefined && self.photoPreviewFeature==true)
        {
            $('.ow_file_attachment_photo_preview', data['html']).bind('click', function() {
                if(this.classList.contains('ow_file_attachment_photo_not_preview')) {
                    this.classList.remove('ow_file_attachment_photo_not_preview');
                    OW.trigger('frmnewsfeedplus.add.to.previewlist', {'data': data}, this);
                    document.getElementById("span_preview_"+data['id']).innerHTML=OW.getLanguageText('frmnewsfeedplus', 'preview_show');
                }
                else{
                    this.classList.add('ow_file_attachment_photo_not_preview');
                    OW.trigger('frmnewsfeedplus.remove.from.previewlist', {'data': data}, this);
                    document.getElementById("span_preview_"+data['id']).innerHTML=OW.getLanguageText('frmnewsfeedplus', 'file_show');
                }
            });
        }
    };

    /**
     * Render uploaded items
     * 
     * @param object uploadedItems
     * @param string customDeleteUrl
     * @param string customConfirmation
     */
    this.renderUploaded = function(uploadedItems, customDeleteUrl, customConfirmation) {
        $.each(uploadedItems, function(index, data) {
            self.addItem(data, true, customDeleteUrl, customConfirmation);
            itemId++;
        });

        $.extend(items, uploadedItems);
        refreshClasses();
    }

    this.initInput = function() {
        var $input = $('<input accept="*/*" class="mlt_file_input" id="chat-files-attachment" type="file"' + (this.multiple ? ' multiple=""' : '') + ' name="ow_file_attachment[]" />');
        this.inputCont.append($input);

        $input.change(
                function(e) {
                    var self2 = this;
                    function processInput(){
                        var inItems = self.prepareFilesToSubmit(self2.files);
                        self.submitFiles(inItems);
                    }

                    self.caption = '';
                    if (self.captionInput === false) {
                        processInput();
                    }else{
                        $.confirm({
                            backgroundDismiss: false, closeIcon: false,
                            content: '' +
                            '<div class="form-group" id="form-group-chat" style="text-align: center;">' +
                            '<input id="pic-caption" type="text" placeholder="' + OW.getLanguageText('mailbox', 'text') + '" class="name form-control" value="'+$('#dialogTextarea').val()+'" required />' +
                            '</div>',
                            buttons: {
                                sayMyName: {
                                    text: OW.getLanguageText('mailbox', 'send'),
                                    btnClass: 'btn-orange',
                                    action: function () {
                                        var input = this.$content.find('input#pic-caption');
                                        self.caption = input.val().trim();
                                        $('#dialogTextarea').val('');
                                        self.thumbnail = document.getElementsByName('thumbnail[]');
                                        processInput();
                                        $('input', self.inputCont).val('');
                                    }
                                },
                                cancel: {
                                    text: OW.getLanguageText('base', 'cancel'),
                                    action: function () {
                                        $('input', self.inputCont).val('');
                                    }
                                }
                            }
                        });

                        if ( window.FileReader ) {
                            var i=0;
                            fileAttIndex=[];
                            fileExtArr=[];
                            fileNamesArr=[];
                            for(i;i<self2.files.length;i++) {
                                var ext = '';
                                var name = '';
                                if (self2.files[i].name.lastIndexOf('.') > 0) {
                                    ext = self2.files[i].name.substr(self2.files[i].name.lastIndexOf('.') + 1);
                                    name = self2.files[i].name
                                }
                                fileAttIndex.push(i);
                                fileExtArr.push(ext);
                                fileNamesArr.push(name);
                                var reader = new FileReader();
                                reader.onload = function (e) {

                                    /**
                                     * create a unique id
                                     */

                                    var dt = new Date().getTime();
                                    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                                        var r = (dt + Math.random()*16)%16 | 0;
                                        dt = Math.floor(dt/16);
                                        return (c=='x' ? r :(r&0x3|0x8)).toString(16);
                                    });

                                    /**
                                     * get current file ext & name
                                     */
                                    var currentFileExt = fileExtArr[0];
                                    var currentFileName = fileNamesArr[0];

                                    /**
                                     *
                                     * create an img tag
                                     */
                                    var imgTag = document.createElement("img");
                                    imgTag.setAttribute("height", "100");
                                    imgTag.setAttribute("width", "100");
                                    imgTag.setAttribute("class", "attach-img");
                                    imgTag.setAttribute('id', 'img'+uuid);

                                    /**
                                     *
                                     * create a video tag
                                     */
                                    var videoTag = document.createElement("video");
                                    videoTag.setAttribute("height", "100");
                                    videoTag.setAttribute("width", "100");
                                    videoTag.setAttribute("class", "attach-img");
                                    videoTag.setAttribute('id', 'img'+uuid);

                                    /**
                                     *
                                     * create a child div to add the img tag to it
                                     */
                                    var child = document.createElement("div");

                                    child.setAttribute("class", "attach_images_item");

                                    /**
                                     *
                                     * create a link to delete attachment
                                     */
                                    var ahref = document.createElement('a');
                                    ahref.title = OW.getLanguageText('base', 'delete');
                                    ahref.setAttribute('class', 'ow_photo_preview_x  remove-chat-file-attachment');
                                    ahref.setAttribute('prent-div-id', 'div-'+uuid);
                                    ahref.setAttribute('file-index', fileAttIndex[0]);
                                    ahref.href = "#";
                                    /**
                                     *
                                     * create a parent div to add child div to it
                                     */
                                    var elem = document.createElement("div")
                                    elem.appendChild(child);
                                    elem.setAttribute("class", "attach_images_container");
                                    elem.setAttribute('id', 'div-'+uuid);
                                    fileAttIndex.shift();
                                    fileExtArr.shift();
                                    fileNamesArr.shift();
                                    $( ".form-group" ).prepend( elem);
                                    elem.prepend(ahref);
                                    if(e.target.result.match('data:image*'))
                                    {
                                        child.appendChild(imgTag);
                                        imgTag.src = e.target.result;
                                        // $('#attach-img').attr('src', e.target.result).attr('style', 'width: auto; height: auto; background: none;');
                                    } else if (e.target.result.match('data:video*')) {
                                        child.appendChild(videoTag);
                                        videoTag.src = e.target.result;

                                        setTimeout(function() {
                                            var canvas =  document.createElement('canvas');
                                            canvas.width = videoTag.width;
                                            canvas.height = videoTag.height;
                                            canvas.getContext('2d').drawImage(videoTag, 0, 0, canvas.width, canvas.height);
                                            var canvasData = canvas.toDataURL("image/png");
                                            var inputTag = document.createElement("input");
                                            inputTag.setAttribute('id', 'thumbnail-' + uuid);
                                            inputTag.setAttribute('name', 'thumbnail[]');
                                            inputTag.setAttribute('type', 'hidden');
                                            inputTag.setAttribute('value', canvasData);
                                            document.getElementById('form-group-chat').append(inputTag);
                                        }, 1000);
                                    } else {
                                        child.appendChild(imgTag);
                                        imgTag.style.display = 'none';
                                        elem.classList.add('ow_file_attachment_chat');
                                        elem.classList.add(currentFileExt);
                                        var extension = document.createElement("span");
                                        extension.setAttribute("class", "ow_file_attachment_chat_information");
                                        extension.classList.add('extention');
                                        extension.textContent = currentFileExt;
                                        elem.prepend(extension);
                                        var filename = document.createElement("span");
                                        filename.setAttribute("class", "ow_file_attachment_chat_information");
                                        filename.classList.add('filename');
                                        filename.textContent = currentFileName;
                                        elem.appendChild(filename);
                                    }
                                };
                                reader.readAsDataURL(self2.files[i]);
                            }
                        }
                    }
                }
        );
    };


    $('body').on('click', 'a.remove-chat-file-attachment', function() {
        parentDivId = this.attributes['prent-div-id'].value;
        fileIndex = this.attributes['file-index'].value;
        $('#'+parentDivId).remove();

        var currentFileList = Array.from($('#dialogAttachmentsBtn input[id=chat-files-attachment]')[0].files);
        currentFileList.splice(fileIndex, 1);
        let list = new DataTransfer();
        for (var k = 0; k < currentFileList.length; k++) {
            list.items.add(currentFileList[k]);
        }
        let updatedFileList = list.files;
        $('#dialogAttachmentsBtn input[id=chat-files-attachment]')[0].files = updatedFileList;
        if($('#dialogAttachmentsBtn input[id=chat-files-attachment]')[0].files.length<=1){
            $( ".remove-chat-file-attachment" ).remove();
        }
    });

    /***
     * Drag and Drop
     *
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    this.initDragAndDtop = function() {
        if(self.dropAreasSelector === ''){
            return;
        }
        var $dropAreas = $(self.dropAreasSelector);
        $dropAreas.addClass('drag_drop_area');
        $dropAreas.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
        })
        .on('dragover dragenter', function() {
            $(this).addClass('is_dragover');
            var nowTime = (new Date()).getTime();
            $(this).attr('lastDragEnterTime', nowTime);
        })
        .on('dragleave dragend drop', function() {
            var formObj = $(this);
            setTimeout(function(){
                var nowTime = (new Date()).getTime();
                var lastEnterTime = eval(formObj.attr('lastDragEnterTime'));
                if (lastEnterTime + 1000 < nowTime) {
                    formObj.removeClass('is_dragover');
                }
            }, 1000);
        })
        .on('drop', function(e) {
            $('body').removeClass('drag_drop_over');
            $(this).removeClass('is_dragover');

            var fileInput = $( 'input.mlt_file_input' ,self.inputCont);
            if (fileInput.length > 0) {
                fileInput[0].files = e.originalEvent.dataTransfer.files;
            }

            $(fileInput).trigger('change');
        });
    };


    this.initCopyPaste = function () {
        if(self.dropAreasSelector === ''){
            return;
        }
        $(self.dropAreasSelector).on("paste", function (e) {

            retrieveImageFromClipboardAsBlob(e, function (imageBlob) {
                if (imageBlob) {
                    var fileInput = $( 'input.mlt_file_input' ,self.inputCont);

                    var list = new DataTransfer();
                    var file = new File([imageBlob], "img.png", {type:"image/png", lastModified:new Date()});
                    list.items.add(file);

                    let myFileList = list.files;

                    if (fileInput.length > 0) {
                        fileInput[0].files = myFileList;
                    }

                    $(fileInput).trigger('change');

                }
            });
        });

    };

    this.prepareFilesToSubmit = function(files){
        var inItems = {};

        // check if files array is available
        if (files != undefined) {
            var elData;
            for (var i = 0; i < files.length; i++) {
                elData = files[i];
                inItems[itemId] = {id: itemId++, name: elData.name, size: (Math.round(elData.size / 1024))};
            }
        }
        else {

        }

        if (self.showPreview) {
            $.each(inItems,
                function(index, data) {
                    self.addItem(data, true);
                }
            );
        }

        OW.trigger('base.add_attachment_to_queue', {'pluginKey': self.pluginKey, 'uid': self.uid, 'items': items});

        $.extend(items, inItems);

        if (self.showPreview) {
            refreshClasses();
        }
        return inItems;
    };

    this.submitFilesSuccess = function (attachmentData) {
        if(attachmentData['dbIds']!=undefined) {
            var attachmentDataDbId = {'dbId': attachmentData['dbIds']};
            OW.trigger('frmnewsfeedplus.add.to.previewlist', {'data': attachmentDataDbId}, this);
        }
        OW.trigger('frmclamav.verified.file.view', {'data': attachmentDatavirusNames}, this);
        if(attachmentData['virusNames']!=undefined) {
            var attachmentDatavirusNames = {'virusNames': attachmentData['virusNames']};
            OW.trigger('frmclamav.virus.file.view', {'data': attachmentDatavirusNames}, this);
        }
        $('body').append(attachmentData ['script']);
    };

    this.submitFiles = function(data) {
        var idList = [], idListIndex = 0;
        $.each(data, function() {
            idList[idListIndex++] = this.id;
        }
        );

        var index = idList.join('_');
        var nameObj = {};

        $.each(items, function(index, item) {
            nameObj[item.name] = item.id;
        });

        $form = '<form method="post" action="' + self.submitUrl + '?flUid=' + self.uid + '" enctype="multipart/form-data" target="form_' + index + '"><input type="hidden" name="flData" value="' +
                encodeURIComponent(JSON.stringify(nameObj)) + '" /><input type="hidden" name="flUid" value="' + self.uid + '"><input type="hidden" name="pluginKey" value="' + self.pluginKey + '">';

        if (typeof self.thumbnail !== 'undefined') {
            for ( var i = 0; i < self.thumbnail.length; i++) {
                $form = $form + '<input type="hidden"  id="' + self.thumbnail[i].id + '" name="thumbnail[]" value="' + self.thumbnail[i].value + '">';
            }
        }

        if (self.captionInput === true){
            $form = $form +'<input type="hidden" name="caption" value="' + self.caption + '" />';
        }
        $form = $($form + '</form>').append($('input[type=file]', self.inputCont));
//        $form.appendTo($('body'));
        $('<div style="display:none" id="hd_' + index + '"><div>').appendTo($('body'))
                .append($('<iframe name="form_' + index + '"></iframe>'))
                .append($form);
        //$form.submit();
        var xhrForm=$form.ajaxSubmit({
            beforeSubmit: function() {
                attachmentInProgress=attachmentInProgress+1;
                OW.trigger('base.progress_bar_actions', { 'action': 'initiate', 'percentComplete': null });
            },
            uploadProgress: function (event, position, total, percentComplete){
                OW.trigger('base.progress_bar_actions', { 'action': 'progress', 'percentComplete': percentComplete });
            },
            success:function (xhr){
                OW.trigger('base.progress_bar_actions', { 'action': 'terminate', 'percentComplete': null });
                var attachmentData = jQuery.parseJSON(xhr);
                self.submitFilesSuccess(attachmentData);
            },
            error: function(e){
                OW.trigger('base.progress_bar_actions', { 'action': 'terminate', 'percentComplete': null });
            },
            complete:function(xhr) {
                OW.trigger('base.progress_bar_actions', { 'action': 'terminate', 'percentComplete': null });
                attachmentInProgress=attachmentInProgress-1;
                $("#conversationChatFormBlock").find("a.attach.uploading").removeClass("uploading");
                $("#dialogsContainer #dialogMessageFormBlock").find("a#dialogAttachmentsBtn").removeClass("uploading");
            },
        });
        attachmentxhrArray.push(xhrForm.data('jqxhr'));
        self.initInput();
    };

    this.updateItems = function(data) {
        if (!data.result && data.noData) {
            OW.error(data.message);
            return;
        }
        var indexList = [];

        if (data.items) {
            $.each(data.items, function(index, item) {
                indexList.push(index);

                if (item.result) {
                    items[index]['dbId'] = item['dbId'];
                    if( items[index]['cancelled'] ){
                        self.deleteItem(index);
                        return;
                    }
                    if (self.showPreview) {
                        $('.ow_file_attachment_preload', items[index]['html']).hide();
                    }
                } else {
                    self.deleteItem(index);
                    if (self.showPreview) {
                        OW.error(item.message);
                    }
                }
            });

            OW.trigger('base.update_attachment', {'pluginKey': self.pluginKey, 'uid': self.uid, 'items': data.items});
        }

        $('#hd_' + indexList.join('_')).remove();
    };

    this.deleteItem = function(id, customDeleteUrl) {
        OW.trigger('base.attachment_deleted', {'id' : id}, this);

        if (self.showPreview) {
            items[id]['html'].remove();
        }

        if( !items[id]['dbId'] ){
            items[id]['cancelled'] = true;
            return;
        }

        $.ajax({
            url: (typeof customDeleteUrl == "undefined" ? self.deleteUrl : customDeleteUrl), 
            data: {id: items[id]['dbId']},
            method: "POST"
        });

        delete items[id];
        if (self.showPreview) {
            refreshClasses();
        }
    };

    this.reset = function(id, callback) {
        self.uid = id;

        if (typeof callback != "undefined") {
            callback.call({}, items);
        }

        if (self.showPreview) {
            $.each(items,
                    function(index, data) {
                        data['html'].remove();
                    }
            );

            refreshClasses();
        }

        items = {};
        itemId = 1;

    };

    $.each(self.lItems, function(index, lItem) {
        items[itemId] = {id: itemId++, name: lItem.name, size: lItem.size, dbId: lItem.dbId};
    });

    if (self.showPreview) {
        $.each(items,
                function(index, data) {
                    self.addItem(data, false);
                }
        );

        refreshClasses();
    }

    this.initInput();
    this.initDragAndDtop();
    this.initCopyPaste();
};

OW.bind('base.file_attachment', function(data) {
    if (owFileAttachments[data.uid]) {
        owFileAttachments[data.newUid] = owFileAttachments[data.uid];
        delete owFileAttachments[data.uid];
        owFileAttachments[data.newUid].reset(data.newUid);
    }
});

OW.bind('base.progress_bar_actions', function(data) {
        var action = data.action;
        if (data.percentComplete != null){
            var percentComplete = data.percentComplete;
        }
        var formerProgressDivElement = $('#progress-div').length;
        var progressDivElement = $('#progress-div');
        var progressDivStatusElement = $('#progress-status');
        var text = OW.getLanguageText('base', 'upload_analyze_massage');
        switch (action) {
            case 'initiate':
                if(formerProgressDivElement==0)
                    $('body').append('<div id="progress-div" class="c100 small"><span id="progress-status">0%</span><span id="progress-status-analyzing">'+text+'</span><div class="slice"><div class="bar"></div><div class="fill"></div></div></div>\n');
                progressDivElement.css({'display': 'block'});
                break;
            case 'progress':
                progressDivElement.addClass('p'+percentComplete)
                progressDivStatusElement.text( percentComplete +'%');
                if (percentComplete == 100){
                    $('#progress-status-analyzing').css({'display': 'inline-block'});
                }else{
                    $('#progress-status-analyzing').css({'display': 'none'});
                }
                break;
            case 'terminate':
                progressDivElement.css({'opacity':'0'});
                setTimeout(function() {
                    progressDivElement.css({'display':'none'});
                    progressDivElement.removeClass();
                    progressDivElement.addClass('c100 small');
                    progressDivElement.css({'opacity':'1'});
                }, 300);
                break;
            default:
            // code block
        }
    }
);

window.owFileAttachments = {};

var OWPhotoAttachment = function(params) {
    $.extend(this, params);
    var self = this, $previewCont = $('#' + this.previewId),
            $buttonCont = $('#' + this.buttonId),
            $form,
            $iframe = null,
            $item = $('.ow_photo_attachment_pic', $previewCont),
            canceled = false;

    this.eventParams = {uid: self.uid, pluginKey: self.pluginKey};

    this.initInput = function() {
        var $input = $('<input accept="*/*" class="mlt_file_input" type="file" name="attachment" />'), self = this;
        $buttonCont.empty().append($input).show();
        OW.trigger('base.attachment_show_button_cont', self.eventParams);
        $item.css({backgroundImage: ''}).unbind('click').addClass('loading');
        canceled = false;
        $previewCont.hide();
        $('div', $item).unbind('click').click(function() {
            canceled = true;
            $previewCont.hide();
            self.initInput();
            OW.trigger('base.attachment_deleted', self.eventParams);
        });

        if ($iframe != null) {
            $iframe.remove();
        }

        if ($form != null) {
            $form.remove();
        }

        $input.change(
                function(e) {
                    $buttonCont.hide();
                    OW.trigger('base.attachment_hide_button_cont', self.eventParams);
                    $previewCont.show();
                    $form = $('<form method="post" action="' + self.addPhotoUrl + '?flUid=' + self.uid + '" enctype="multipart/form-data" target="form_' + self.uid + '">' +
                            '<input type="hidden" name="flUid" value="' + self.uid + '"><input type="hidden" name="pluginKey" value="' + self.pluginKey + '"></form>')
                            .append($('input[type=file]', $buttonCont));
                    $iframe = $('<div style="display:none" id="hd_' + self.uid + '"><div>').appendTo($('body'))
                            .append($('<iframe name="form_' + self.uid + '"></iframe>'))
                            .append($form);
                    //$form.on('submit',  function(event) {
                    //    if (typeof event.target[CSRFP.CSRFP_TOKEN] === 'undefined') {
                    //        event.target.appendChild(CSRFP._getInputElt());
                    //    } else {
                    //        //modify token to latest value
                    //        event.target[CSRFP.CSRFP_TOKEN].value = CSRFP._getAuthKey();
                    //    }
                    //});
                    $form.submit();
                    OW.trigger('base.add_photo_attachment_submit', self.eventParams);
                }
        );
    };

    this.updateItem = function(data) {
        if (canceled) {
            canceled = false;
            return;
        }

        var self = this, eventParams = {uid: self.uid, pluginKey: self.pluginKey, url: data.url};
        if (data.result) {
            var previewImg = new Image();
            previewImg.onload = function() {
                $item.removeClass('loading').css({backgroundImage: 'url(' + data.url + ')'}).click(function() {
                    OW.showImageInFloatBox(data.url)
                });
                $('div', $item).unbind('click').click(function(e) {
                    e.stopPropagation();
                    self.initInput();
                    OW.trigger('base.attachment_deleted', eventParams);
                });
                OW.trigger('base.attachment_added', eventParams);
            };
            previewImg.src = data.url;
        }
        else {
            if (data.message) {
                OW.error(data.message);
            }

            self.initInput();
        }
    };

    this.resetUid = function(data) {
        this.uid = data;
        this.eventParams.uid = this.uid;
        self.initInput();
    };

    OW.bind('base.photo_attachment_reset', function(data) {
        if (data.uid == self.uid && data.pluginKey == self.pluginKey)
            self.initInput();
    });

    this.initInput();
};

window.owPhotoAttachment = {};

OW.bind('base.photo_attachment_uid_update', function(data) {
    if (owPhotoAttachment[data.uid]) {
        owPhotoAttachment[data.newUid] = owPhotoAttachment[data.uid];
        delete owPhotoAttachment[data.uid];
        owPhotoAttachment[data.newUid].resetUid(data.newUid);
    }
});


OW.bind('check.attachment.upload.status', function(data) {
         if(attachmentInProgress>0)
             return false;
         else
             return true;
});

OW.bind('clear.attachment.inProgress', function(data) {
    for(var i=0;i<attachmentxhrArray.length;i++){
        attachmentxhrArray[i].abort();
    }
});

/***
 * Drag and Drop
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
lastDragEnterTime = 0;
$('body').on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
    e.preventDefault();
    e.stopPropagation();
})
.on('dragover dragenter', function() {
    $('body').addClass('drag_drop_over');
    lastDragEnterTime = (new Date()).getTime();

    //show newsfeed buttons
    $('.ow_submit_auto_click').show();
    $(this).unbind('focus.auto_click')
})
.on('dragleave dragend drop', function() {
    setTimeout(function(){
        var nowTime = (new Date()).getTime();
        if (lastDragEnterTime + 1000 < nowTime) {
            $('body').removeClass('drag_drop_over');
        }
    }, 1000);
})
.on('drop', function(e) {
    $('body').removeClass('drag_drop_over');
});


/**
 * This handler retrieves the images from the clipboard as a blob and returns it in a callback.
 *
 * @param pasteEvent
 * @param callback
 * @link https://ourcodeworld.com/articles/read/491/how-to-retrieve-images-from-the-clipboard-with-javascript-in-the-browser
 */
function retrieveImageFromClipboardAsBlob(pasteEvent, callback) {

    pasteEvent = pasteEvent.originalEvent;

    if(pasteEvent.clipboardData == false){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    var items = pasteEvent.clipboardData.items;

    if(items == undefined){
        if(typeof(callback) == "function"){
            callback(undefined);
        }
    };

    for (var i = 0; i < items.length; i++) {
        // Skip content if not image
        if (items[i].type.indexOf("image") == -1) continue;
        // Retrieve image on clipboard as blob
        var blob = items[i].getAsFile();

        if(typeof(callback) == "function"){
            callback(blob);
        }
    }
}
