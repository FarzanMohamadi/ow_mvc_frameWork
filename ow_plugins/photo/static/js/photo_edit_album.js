var _vars = $.extend({}, (albumParams || {}), {albumNameList: []});
var photoIdList = new Array();

photoIdList.length = 0;

//select or deselect one photo
var sel = $('.owm_photo_chekbox_area');
sel.click(function () {
    var closest = $(this).closest('.owm_photo_list_item');
    var cover = document.getElementById('set_as_cover');
    if (closest.hasClass('owm_photo_item_checked')) {
        closest.removeClass('owm_photo_item_checked');
        photoIdList.splice(photoIdList.indexOf(+closest.id), 1);
    }
    else {
        closest.addClass('owm_photo_item_checked');
        photoIdList.push(closest.attr('id'));

    }
});

//delete photos
var del = $('.delete');
del.click(function () {
    if (photoIdList.length === 0) {
        $.alert(OW.getLanguageText('photo', 'no_photo_selected'));

        return;
    }

    var jc = $.confirm(OW.getLanguageText('photo', 'confirm_delete_photos'));
    jc.buttons.ok.action = function () {
        $.ajax({
            url: _vars.url,
            type: 'POST',
            dataType: 'json',
            cache: false,
            data: {
                ajaxFunc: 'ajaxDeletePhotos',
                albumId: _vars.album.id,
                photoIdList: photoIdList
            },
            success: function (data) {
                if (data.result) {

                    if (photoIdList.length === 1) {
                        OW.info(OW.getLanguageText('photo', 'photo_deleted'));
                        location.reload();
                    }
                    else {
                        OW.info(OW.getLanguageText('photo', 'photos_deleted'));
                        location.reload();
                    }

                    if (data.url !== undefined) {
                        window.location = data.url;
                    }
                    else {
                        browsePhoto.removePhotoItems(photoIdList);

                        photoIdList.length = 0;
                    }
                }
                else {
                    $.alert(OW.getLanguageText('photo', 'no_photo_selected'));
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                OW.error(textStatus);

                throw textStatus;
            }
        });
    }
});

//select or deselect all photo
var sel_all = $('.select_all');
sel_all.click(function () {
    if (this.checked) {
        var imgs = $('.owm_photo_list_item')
        imgs.addClass('owm_photo_item_checked');

        for (var i = 0; i < imgs.length; i++) {
            photoIdList.push(imgs[i].id);
        }

    }
    else {
        $('.owm_photo_list_item').removeClass('owm_photo_item_checked');
        photoIdList = new Array();
    }
});


function checkPhotoIsSelected() {
    if (photoIdList.length === 0) {
        $.alert(OW.getLanguageText('photo', 'no_photo_selected'));

        return false;
    }

    return true;
}

function createNewAlbumAndMove() {
    var fb = OWM.ajaxFloatBox('PHOTO_MCMP_CreateAlbum', [_vars.album.id, photoIdList.join(',')], {
        title: OW.getLanguageText('photo', 'move_to_new_album'),
        width: '500',
        onLoad: function () {
            owForms['add-album'].bind('success', function (data) {
                fb.close();

                movePhotoSuccess(data);
            });
        }
    });
}

function movePhoto() {

    $.ajax({
        url: _vars.url,
        type: 'POST',
        dataType: 'json',
        cache: false,
        data:
            {
                "ajaxFunc": 'ajaxMoveToAlbum',
                "from-album": _vars.album.id,
                "to-album": $("#choose_album").children(":selected").attr('rel'),
                "photos": photoIdList.join(','),
                "album-name": $("#choose_album").children(":selected").html(),
                "csrf_token": $("form input[name='csrf_token']")[0].value
            },
        success: movePhotoSuccess,
        error: function (jqXHR, textStatus, errorThrown) {
            OW.error(textStatus);

            throw textStatus;
        }
    });
}

function movePhotoSuccess(data) {
    if (data.result) {
        OW.info(OW.getLanguageText('photo', 'photo_success_moved'));
        photoIdList.length = 0;
    }
    else {
        if (data.msg) {
            OW.error(data.msg);
        }
        else {
            $.alert(OW.getLanguageText('photo', 'no_photo_selected'));
        }
    }
}

//create and move album
$('#choose_album').change(function () {
    if (!checkPhotoIsSelected()) {
        event.stopImmediatePropagation();

        return false;
    }
    else {
        var id = $("#choose_album").children(":selected")[0].id
        if (id === "create_new_photo_album") {
            createNewAlbumAndMove();
        }
        else
            movePhoto();
    }
});

